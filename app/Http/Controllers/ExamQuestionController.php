<?php
namespace App\Http\Controllers;

use App\Models\ExamQuestion;
use App\Models\Exam;
use Illuminate\Http\Request;
use App\Exports\QuestionTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;


class ExamQuestionController extends Controller
{
    // Browse the pool of questions
    public function index(Request $request) {
        $user = auth()->user();
        $query = ExamQuestion::query();

        // Filters
        if ($request->search) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }
        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        // Visibility Logic
        $query->where(function($q) use ($user) {
            $q->where('is_public', true)
            ->orWhere('user_id', $user->id);
        });

        $questions = $query->latest()->paginate(10);
        
        // IMPORTANT: Fetch these for the filters
        $courses = \App\Models\Course::all();
        $subjects = \App\Models\Subject::all();

        return view('exam_questions.index', compact('questions', 'courses', 'subjects'));
    }

    // Clone a public question into personal library
    public function clone($id) {
        $original = ExamQuestion::findOrFail($id);
        
        $clone = $original->replicate();
        $clone->user_id = auth()->id();
        $clone->is_public = false; // Clones are always private
        $clone->cloned_from_id = $original->id;
        $clone->save();

        return back()->with('success', 'Question added to your personal library.');
    }

    public function store(Request $request) {
        $request->validate([
            'question_text' => 'required',
            'course_id' => 'required',
            'subject_id' => 'required'
        ]);

        \App\Models\ExamQuestion::create([
            'user_id' => auth()->id(),
            'course_id' => $request->course_id,
            'subject_id' => $request->subject_id,
            'question_text' => $request->question_text,
            'correct_answer_guide' => $request->correct_answer_guide,
            'default_points' => $request->points ?? 5,
            'is_public' => $request->is_public ?? false,
        ]);

        return back()->with('success', 'Question added successfully.');
    }

    

    public function create() {
        $courses = \App\Models\Course::all();
        $subjects = \App\Models\Subject::all();
        return view('exam_questions.create', compact('courses', 'subjects'));
    }

    public function storeBulk(Request $request) 
    {
        // 1. Validate the basic fields and the file
        $request->validate([
            'exam_name' => 'required|string|max:255',
            'course_id' => 'required',
            'subject_id' => 'required',
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // 2. Create the Exam "Container" first
        $exam = Exam::create([
            'user_id' => auth()->id(),
            'name' => $request->exam_name,
            'type' => $request->type,
            'assessment_type_id' => $request->assessment_type_id == 'mock' ? 1 : 2, // Map your tags to IDs
            'course_id' => $request->course_id,
            'subject_id' => $request->subject_id,
            'timer_type' => $request->timer_type,
            'total_time_minutes' => $request->total_time_minutes,
            'allow_pause' => $request->has('allow_pause'),
            'pause_limit' => $request->pause_limit ?? 0,
            'collaborators' => $request->collaborator_emails ? explode(',', $request->collaborator_emails) : [],
        ]);

        // 3. Process the File into an array
        $rows = Excel::toArray([], $request->file('file'))[0];
        
        // Skip the header row (index 0)
        $dataRows = array_slice($rows, 1); 

        $questionIds = [];

        foreach ($dataRows as $row) {
            // Skip empty rows (assuming index 2 is the Question text in your template)
            if (empty($row[2])) continue;

            $question = ExamQuestion::create([
                'user_id' => auth()->id(),
                'course_id' => $request->course_id,
                'subject_id' => $request->subject_id,
                'question_text' => $row[2],
                'correct_answer_guide' => $row[3] ?? null,
                'default_points' => $row[4] ?? 5,
                'is_public' => false,
            ]);

            $questionIds[] = $question->id;
        }

        // 4. Link the new questions to the new exam via the pivot table
        $exam->questions()->sync($questionIds);

        return redirect()->route('exams.index')->with('success', 'Exam and Questions imported successfully!');
    }
 
    public function showUploadForm() {
        $courses = \App\Models\Course::all();
        $subjects = \App\Models\Subject::all();
        return view('exams.questions.bulk_upload', compact('courses', 'subjects'));
    }
 

    public function downloadTemplate(Request $request) {
        $course = $request->query('course', 'Juris Doctor');
        $subject = $request->query('subject', 'Political Law');
        $rowsCount = (int) $request->query('rows', 10);

        return Excel::download(
            new QuestionTemplateExport($course, $subject, $rowsCount), 
            "exam_template_{$subject}.xlsx"
        );
    }
 

    public function updateAjax(Request $request, $id) {
        $request->validate([
            'question_text' => 'required',
            'correct_answer_guide' => 'nullable',
            'default_points' => 'required|integer'
        ]);

        $question = ExamQuestion::findOrFail($id);
        
        // Authorization: Only owner or admin
        if ($question->user_id !== auth()->id() && auth()->user()->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $question->update([
            'question_text' => $request->question_text,
            'correct_answer_guide' => $request->correct_answer_guide,
            'default_points' => $request->default_points,
        ]);

        return response()->json(['success' => true, 'updated_text' => Str::limit(strip_tags($request->question_text), 100)]);
    }
}