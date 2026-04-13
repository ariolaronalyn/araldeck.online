<?php
namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\AssessmentType;
use App\Models\ExamQuestion; // Ensure this is imported
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     */
    public function index()
    {
        $userId = auth()->id();

        $exams = Exam::with(['assessmentType'])
            ->withCount('questions')
            // Load the current user's submission for each exam
            ->with(['submissions' => function($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereJsonContains('collaborators', $userId)
                    ->orWhereJsonContains('collaborators', (string)$userId);
            })
            ->latest()
            ->get();

        return view('exams.index', compact('exams'));
    }

    public function create() {
        $user = auth()->user();
        $assessmentTypes = AssessmentType::all();
        $courses = \App\Models\Course::all();
        $subjects = \App\Models\Subject::all();

        // Power to the Super Admin: See everything
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $questionPool = ExamQuestion::latest()->get();
        } else {
            $questionPool = ExamQuestion::where('user_id', $user->id)
                            ->orWhere('is_public', true)->latest()->get();
        }
        
        // Get users for collaborator selection (excluding self)
        $users = \App\Models\User::where('id', '!=', $user->id)->get();
                            
        return view('exams.create', compact('assessmentTypes', 'questionPool', 'courses', 'subjects', 'users'));
    }

    public function store(Request $request) {
        // Add custom messages to debug easily
        $request->validate([
            'exam_name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'question_ids' => 'required|array|min:1', // Ensure at least one question is picked
        ], [
            'question_ids.required' => 'You must select at least one question from the pool below.'
        ]);

        $collaboratorIds = [];
        if ($request->collaborator_emails) {
            $emails = explode(',', $request->collaborator_emails);
            $collaboratorIds = \App\Models\User::whereIn('email', $emails)->pluck('id')->toArray();
        }

        $exam = Exam::create([
            'user_id' => auth()->id(),
            'name' => $request->exam_name,
            'course_id' => $request->course_id,
            'subject_id' => $request->subject_id,
            'type' => $request->type,
            'assessment_type_id' => $request->assessment_type_id,
            'timer_type' => $request->timer_type,
            'total_time_minutes' => $request->total_time_minutes,
            'allow_pause' => $request->has('allow_pause'), // Use has() for checkboxes
            'pause_limit' => $request->pause_limit ?? 0,
            'collaborators' => $collaboratorIds, 
        ]);

        if ($request->has('question_ids')) {
            $exam->questions()->sync($request->question_ids);
        }

        return redirect()->route('exams.index')->with('success', 'Exam created successfully!');
    }
    // app/Http/Controllers/ExamController.php

    public function results($id)
    {
        $exam = Exam::with(['questions', 'assessmentType'])->findOrFail($id);
        
        // Fetch submissions for this exam
        // If it's 'self' type, probably just show the user's attempt
        // If 'class', show all students to the teacher
        $submissions = \App\Models\ExamSubmission::where('exam_id', $id)
            ->with('user')
            ->get();

        return view('exams.results', compact('exam', 'submissions'));
    }

    public function edit($id) {
        $exam = Exam::with('questions')->findOrFail($id);
        $user = auth()->user();
        
        // Check permission (Creator or Admin)
        if ($exam->user_id !== $user->id && $user->role !== 'super_admin') {
            abort(403);
        }

        $assessmentTypes = AssessmentType::all();
        $courses = \App\Models\Course::all();
        $subjects = \App\Models\Subject::all();

        if ($user->role === 'super_admin' || $user->role === 'admin') {
            $questionPool = ExamQuestion::latest()->get();
        } else {
            $questionPool = ExamQuestion::where('user_id', $user->id)
                            ->orWhere('is_public', true)->latest()->get();
        }
        
        // Convert collaborator IDs back to emails for Tom Select
        $collaboratorEmails = \App\Models\User::whereIn('id', $exam->collaborators ?? [])
                                ->pluck('email')->implode(',');

        return view('exams.edit', compact('exam', 'assessmentTypes', 'questionPool', 'courses', 'subjects', 'collaboratorEmails'));
    }

    public function update(Request $request, $id) {
        $exam = Exam::findOrFail($id);
        
        $request->validate([
            'exam_name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'subject_id' => 'required|exists:subjects,id',
            'question_ids' => 'required|array|min:1',
        ]);

        $collaboratorIds = [];
        if ($request->collaborator_emails) {
            $emails = explode(',', $request->collaborator_emails);
            $collaboratorIds = \App\Models\User::whereIn('email', $emails)->pluck('id')->toArray();
        }

        $exam->update([
            'name' => $request->exam_name,
            'course_id' => $request->course_id,
            'subject_id' => $request->subject_id,
            'type' => $request->type,
            'assessment_type_id' => $request->assessment_type_id,
            'timer_type' => $request->timer_type,
            'total_time_minutes' => $request->total_time_minutes,
            'allow_pause' => $request->has('allow_pause'),
            'pause_limit' => $request->pause_limit ?? 0,
            'collaborators' => $collaboratorIds, 
        ]);

        $exam->questions()->sync($request->question_ids);

        return redirect()->route('exams.index')->with('success', 'Exam updated successfully!');
    }
}