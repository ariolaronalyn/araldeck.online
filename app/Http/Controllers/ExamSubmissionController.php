<?php
namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use App\Models\ExamComment;
use Illuminate\Http\Request;

class ExamSubmissionController extends Controller
{
    public function start($examId) {
        $exam = Exam::with('questions')->findOrFail($examId);
         
        $submission = ExamSubmission::firstOrCreate([
            'exam_id' => $examId,
            'user_id' => auth()->id(),
            'status' => 'in_progress'
        ], [
            'started_at' => now(),
            'remaining_time_seconds' => $exam->total_time_minutes * 60
        ]);

        return view('exams.take', compact('exam', 'submission'));
    }

 

    // Handle Pause Logic
    public function pause(Request $request) {
        $submission = ExamSubmission::findOrFail($request->submission_id);
        $exam = $submission->exam;

        if ($submission->pause_count >= $exam->pause_limit) {
            return response()->json(['allowed' => false, 'message' => 'Pause limit reached!']);
        }

        $submission->increment('pause_count');
        return response()->json(['allowed' => true, 'count' => $submission->pause_count]);
    }

    // TEACHER ONLY: Grade the Exam
    public function updateGrade(Request $request) {
        if (auth()->user()->role !== 'teacher') abort(403);

        $answer = ExamAnswer::findOrFail($request->answer_id);
        $answer->update(['points_given' => $request->points]);

        // Recalculate total score
        $submission = $answer->submission;
        $submission->update([
            'total_score' => $submission->answers()->sum('points_given'),
            'status' => 'graded'
        ]);

        return response()->json(['status' => 'Graded', 'total' => $submission->total_score]);
    }

    // Group Study: Nested Comments
    public function addComment(Request $request) {
        ExamComment::create([
            'user_id' => auth()->id(),
            'exam_submission_id' => $request->submission_id,
            'exam_answer_id' => $request->answer_id,
            'comment_body' => $request->comment,
            'parent_id' => $request->parent_id // For nested replies
        ]);
        return back();
    } 

    public function finishSubmission(Request $request) {
        $submission = ExamSubmission::findOrFail($request->submission_id);
        $submission->update([
            'status' => 'completed',
            'submitted_at' => now()
        ]);
        return response()->json(['status' => 'success']);
    }

    // ExamSubmissionController.php
    public function logIncident(Request $request) {
        $sub = ExamSubmission::findOrFail($request->submission_id);
        $logs = $sub->proctoring_logs ?? [];
        $logs[] = ['time' => $request->timestamp, 'event' => $request->incident];
        $sub->update(['proctoring_logs' => $logs]);
        return response()->json(['status' => 'logged']);
    }
 // Save Answer via AJAX (Autosave)
    public function saveAnswer(Request $request) {
        // Validation to prevent 500 error if JS sends nulls
        if (!$request->submission_id || !$request->question_id) {
            return response()->json(['error' => 'Invalid IDs'], 422);
        }

        ExamAnswer::updateOrInsert(
            [
                'exam_submission_id' => $request->submission_id,
                'exam_question_id' => $request->question_id
            ],
            [
                'answer_text' => $request->answer_text ?? '',
                'updated_at' => now()
            ]
        );
        return response()->json(['status' => 'Saved']);
    }

    // Save Time Logs
    public function saveTime(Request $request) {
        if (!$request->submission_id || !is_array($request->time_logs)) {
            return response()->json(['error' => 'Invalid logs'], 422);
        }

        $sub = ExamSubmission::findOrFail($request->submission_id);
        
        // Ensure the time_logs are saved as a JSON string if the model doesn't cast it
        $sub->update([
            'time_per_question' => $request->time_logs
        ]);

        return response()->json(['status' => 'time saved']);
    }
}