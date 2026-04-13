<?php
namespace App\Http\Controllers;

use App\Models\ExamQuestion;
use Illuminate\Http\Request;

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

    public function storeBulk(Request $request) {
        $request->validate([
            'course_id' => 'required',
            'subject_id' => 'required',
            'questions.*.text' => 'required',
        ]);

        foreach ($request->questions as $qData) {
            ExamQuestion::create([
                'user_id' => auth()->id(),
                'course_id' => $request->course_id,
                'subject_id' => $request->subject_id,
                'question_text' => $qData['text'],
                'correct_answer_guide' => $qData['answer'], // Optional grading guide
                'default_points' => $qData['points'] ?? 5,
                'is_public' => $request->is_public ?? false,
            ]);
        }

        return redirect()->route('questions.index')->with('success', 'Questions added successfully!');
    }
}