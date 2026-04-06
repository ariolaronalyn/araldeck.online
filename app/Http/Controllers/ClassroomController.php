<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Deck;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    // List all classes for the teacher
    public function index() {
        $user = auth()->user();

        if ($user->role === 'teacher') {
            // Teachers see classes they created
            $classes = SchoolClass::where('teacher_id', $user->id)->get();
        } else {
            // Students see classes where they are enrolled
            // We use 'with' to ensure the teacher info is loaded too
            $classes = $user->classes()->with('teacher')->get();
        }

        return view('classroom.index', compact('classes'));
    }

    // Save a new class
    public function store(Request $request) {
        // 1. Custom Validation for Duplicate Class
        $exists = SchoolClass::where('teacher_id', auth()->id())
            ->where('name', $request->name)
            ->where('school_year', $request->school_year)
            ->exists();

        if ($exists) {
            return back()->with('error', "The class '{$request->name}' already exists for the school year {$request->school_year}.");
        }

        $request->validate([
            'name' => 'required|string',
            'school_year' => 'required',
            'section' => 'required'
        ]);

        SchoolClass::create([
            'teacher_id' => auth()->id(),
            'name' => $request->name,
            'school_year' => $request->school_year,
            'section' => $request->section
        ]);

        return back()->with('success', 'Class created successfully!');
    }

    // public function show($id) {
    //     $user = auth()->user();
        
    //     // Security: Teachers see their own, Students see where they are enrolled
    //     if ($user->role === 'teacher') {
    //         $class = SchoolClass::where('teacher_id', $user->id)->findOrFail($id);
    //     } else {
    //         $class = $user->classes()->findOrFail($id);
    //     }

    //     $students = $class->students;

    //     // Get Decks scheduled for this specific class
    //     $scheduledDecks = DB::table('class_decks')
    //         ->where('school_class_id', $id)
    //         ->orderBy('start_at', 'asc')
    //         ->get();

    //     // Teacher-only data for the modal
    //     $myDecks = [];
    //     if ($user->role === 'teacher') {
    //         $myDecks = DB::table('flashcards')
    //             ->join('subjects', 'flashcards.subject_id', '=', 'subjects.id')
    //             ->join('courses', 'subjects.course_id', '=', 'courses.id')
    //             ->where('flashcards.user_id', $user->id)
    //             ->select('flashcards.deck_name', 'flashcards.subject_id', 'subjects.title as subject_title', 'courses.title as course_title')
    //             ->distinct()
    //             ->get();
    //     }

    //     return view('classroom.show', compact('class', 'students', 'scheduledDecks', 'myDecks'));
    // }

    public function show($id) {
        $user = auth()->user();
        $class = ($user->role === 'teacher') ? SchoolClass::where('teacher_id', $user->id)->findOrFail($id) : $user->classes()->findOrFail($id);
        
        $students = $class->students;
        
        // Join decks table to get names for scheduled decks
        $scheduledDecks = DB::table('class_decks')
            ->join('decks', 'class_decks.deck_id', '=', 'decks.id')
            ->where('class_decks.school_class_id', $id)
            ->select('class_decks.*', 'decks.name as deck_name')
            ->get();

        $myDecks = [];
        if ($user->role === 'teacher') {
            $myDecks = Deck::with('subject.course')->where('user_id', $user->id)->get();
        }

        return view('classroom.show', compact('class', 'students', 'scheduledDecks', 'myDecks'));
    }

    public function scheduleDeck(Request $request, $id) {
        $deckId = $request->deck_info; // This should now be just the ID from the dropdown

        $alreadyScheduled = DB::table('class_decks')
            ->where('school_class_id', $id)
            ->where('deck_id', $deckId)
            ->exists();

        if ($alreadyScheduled) return back()->with('error', "This deck is already scheduled.");

        DB::table('class_decks')->insert([
            'school_class_id' => $id,
            'deck_id' => $deckId,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Deck scheduled successfully!');
    }

    public function report($classId, $deckId) {
        $class = SchoolClass::findOrFail($classId);
        $deck = Deck::findOrFail($deckId);

        $report = DB::table('class_students as cs')
            ->join('users as u', 'cs.student_id', '=', 'u.id')
            ->leftJoin('quiz_attempts as qa', function($join) use ($deckId) {
                $join->on('u.id', '=', 'qa.user_id')
                    ->where('qa.deck_id', '=', $deckId);
            })
            ->where('cs.school_class_id', $classId)
            ->select('u.name', 'u.email', 'qa.score', 'qa.total_questions', 'qa.created_at as completed_at')
            ->get();

        return view('classroom.report', compact('report', 'class', 'deck'));
    }

    // Add a student to the class via email
    public function addStudent(Request $request, $id) {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->with('error', 'User with that email not found. They must register first.');
        }

        DB::table('class_students')->updateOrInsert([
            'school_class_id' => $id,
            'student_id' => $user->id
        ], ['created_at' => now()]);

        return back()->with('success', 'Student added to class!');
    }

    // public function scheduleDeck(Request $request, $id) {
    //     // deck_info should now pass the deck ID
    //     $deckId = $request->deck_info; 

    //     $alreadyScheduled = DB::table('class_decks')
    //         ->where('school_class_id', $id)
    //         ->where('deck_id', $deckId)
    //         ->exists();

    //     if ($alreadyScheduled) {
    //         return back()->with('error', "This deck is already scheduled.");
    //     }

    //     DB::table('class_decks')->insert([
    //         'school_class_id' => $id,
    //         'deck_id' => $deckId, // Use the Foreign Key
    //         'start_at' => $request->start_at,
    //         'end_at' => $request->end_at,
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     return back()->with('success', 'Deck scheduled successfully!');
    // }

    // View the grade/completion report
    // public function report($classId, $deckName) {
    //     $class = SchoolClass::findOrFail($classId);
        
    //     // Report logic: Left join ensures we see students even if they haven't answered yet
    //     $report = DB::table('class_students as cs')
    //         ->join('users as u', 'cs.student_id', '=', 'u.id')
    //         ->leftJoin('quiz_attempts as qa', function($join) use ($deckName) {
    //             $join->on('u.id', '=', 'qa.user_id')
    //                  ->where('qa.deck_name', '=', $deckName);
    //         })
    //         ->where('cs.school_class_id', $classId)
    //         ->select('u.name', 'u.email', 'qa.score', 'qa.total_questions', 'qa.created_at as completed_at')
    //         ->get();

    //     return view('classroom.report', compact('report', 'class', 'deckName'));
    // }
 
    public function updateDeckSchedule(Request $request, $classId, $scheduleId) {
        $request->validate([
            'start_at' => 'required',
            'end_at' => 'required',
        ]);

        DB::table('class_decks')
            ->where('id', $scheduleId)
            ->update([
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'updated_at' => now()
            ]);

        return back()->with('success', 'Schedule updated successfully!');
    }

    public function closeDeckSchedule($classId, $scheduleId) {
        // We update the end_at to 'now' so the student's 'between' check fails
        DB::table('class_decks')
            ->where('id', $scheduleId)
            ->where('school_class_id', $classId)
            ->update([
                'end_at' => now(),
                'updated_at' => now()
            ]);

        return back()->with('success', 'The scheduled deck has been closed and is no longer accessible to students.');
    }
    public function reopenDeckSchedule($classId, $scheduleId) {
        // Set the end date to 24 hours from now to give students time
        DB::table('class_decks')
            ->where('id', $scheduleId)
            ->where('school_class_id', $classId)
            ->update([
                'end_at' => now()->addDay(), 
                'updated_at' => now()
            ]);

        return back()->with('success', 'The deck has been re-opened for 24 hours. You can edit the specific time using the Edit button.');
    }
}