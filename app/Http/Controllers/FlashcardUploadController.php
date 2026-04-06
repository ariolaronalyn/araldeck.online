<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flashcard;
use App\Models\Subject;
use App\Models\Course;
use App\Models\User;
use App\Models\Collaboration;   
use App\Models\Deck;
use App\Exports\FlashcardTemplateExport; 
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class FlashcardUploadController extends Controller
{
    public function index() {
        $user = Auth::user();
        $courses = Course::orderBy('title')->get();
        $subjects = Subject::orderBy('title')->get();
        

        // FIX: Fetch actual Deck models instead of just names from the flashcards table
        $userDecks = Deck::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        // --- START SUBSCRIPTION CHECK ---
            $canUpload = true;
            $uploadMessage = "";

            $hasActiveSubscription = \DB::table('user_subscriptions')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->exists();

            if (!$hasActiveSubscription) {
                $uploadedToday = Deck::where('user_id', $user->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
                    
                if ($uploadedToday) {
                    $canUpload = false;
                    $uploadMessage = "Free limit reached: 1 upload per day. Come back tomorrow or upgrade to Pro!";
                }
            }
            // --- END SUBSCRIPTION CHECK ---

        return view('upload', compact('courses', 'subjects', 'userDecks', 'canUpload', 'uploadMessage'));
    }

     
    public function upload(Request $request) {
        $request->validate([
            'csv_file' => 'required|file|extensions:xlsx,xls,csv',
            'existing_deck' => 'required',
            'new_deck_name' => 'nullable|string|max:255',
            'is_public' => 'required',
            'deck_type' => 'required|in:study,quiz',
        ]);

        try {
            $deckName = ($request->existing_deck === 'others') ? trim($request->new_deck_name) : trim($request->existing_deck);
            $rows = Excel::toArray([], $request->file('csv_file'))[0];
            $dataRows = array_slice($rows, 1); 

            $courseTitle = trim($dataRows[0][0] ?? 'General Course');
            $subjectTitle = trim($dataRows[0][1] ?? 'General Subject');
            // Capture Topic from the first data row for the Deck itself
            $topicTitle = trim($dataRows[0][5] ?? null); 

            $course = Course::firstOrCreate(['title' => $courseTitle]);
            $subject = Subject::firstOrCreate(['course_id' => $course->id, 'title' => $subjectTitle]);

            $deck = Deck::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => $deckName],
                [
                    'subject_id' => $subject->id,
                    'course_id' => $course->id,
                    'topic' => $topicTitle, // Save topic to deck
                    'type' => $request->deck_type,
                    'is_public' => filter_var($request->is_public, FILTER_VALIDATE_BOOLEAN)
                ]
            );
// var_dump($dataRows);exit();
            foreach ($dataRows as $row) {
                if (!empty($row[2]) && $row[2] !== 'Question') { // Question column
                    Flashcard::create([
                        'deck_id' => $deck->id,
                        'user_id' => Auth::id(),
                        'subject_id' => $subject->id,
                        'question' => trim($row[2]),
                        'answer' => trim($row[3]),
                        'reference' => trim($row[4] ?? null),
                        'topic' => trim($row[5] ?? null), // Save topic to card
                    ]);
                    }
                }

            return redirect()->route('flashcards.index')->with('success', "Deck uploaded successfully with topics.");
        } catch (\Exception $e) {
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    private function getTimerByDifficulty($difficulty) {
        return match($difficulty) {
            'easy'    => 30,
            'average' => 20,
            'hard'    => 10,
            default   => 20
        };
    }

    public function downloadTemplate(Request $request) {
        $course = $request->query('course', 'Course');
        $subject = $request->query('subject', 'Subject');
        $rowsCount = (int) $request->query('rows', 10);

        return Excel::download(
            new FlashcardTemplateExport($course, $subject, $rowsCount), 
            "araldeck_{$subject}_template.xlsx"
        );
    }
}