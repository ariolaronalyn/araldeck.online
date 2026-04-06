<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all available plans
        $plans = DB::table('subscription_plans')->get();
        
        // Get current timer settings for the user
        $timers = [
            'easy' => \App\Models\Flashcard::where('user_id', $user->id)
                ->where('difficulty', 'easy')
                ->latest('updated_at')
                ->value('timer_seconds') ?? 30,
            'average' => \App\Models\Flashcard::where('user_id', $user->id)
                ->where('difficulty', 'average')
                ->latest('updated_at')
                ->value('timer_seconds') ?? 20,
            'hard' => \App\Models\Flashcard::where('user_id', $user->id)
                ->where('difficulty', 'hard')
                ->latest('updated_at')
                ->value('timer_seconds') ?? 10,
        ];

        // Fetch Quiz Logs from your quiz_attempts table
        $logs = DB::table('quiz_attempts')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('settings.index', compact('plans', 'timers', 'logs'));
    }

    public function getLogSummary($id)
    {
        $log = DB::table('quiz_attempts')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        return response()->json($log);
    }
    
    public function showLogDetails($id) {
        $attempt = DB::table('quiz_attempts')->where('id', $id)->first();
        if (!$attempt) abort(404);
        
        // Decode JSON, fallback to an empty array if null
        $details = json_decode($attempt->details, true) ?: [];
        
        return view('settings.quiz_summary', compact('attempt', 'details'));
    }

    public function overrideScore(Request $request, $id) {
        if (auth()->user()->role !== 'teacher') abort(403);

        $attempt = DB::table('quiz_attempts')->where('id', $id)->first();
        $details = json_decode($attempt->details, true);
        $logs = json_decode($attempt->override_logs, true) ?? [];

        // Find the specific question index and toggle correctness
        $index = $request->index;
        $oldStatus = $details[$index]['is_correct'];
        $details[$index]['is_correct'] = !$oldStatus;
        $details[$index]['overridden'] = true;

        // Calculate New Score
        $newScore = collect($details)->where('is_correct', true)->count();

        // Log the action
        $logs[] = [
            'teacher_id' => auth()->id(),
            'teacher_name' => auth()->user()->name,
            'date' => now()->toDateTimeString(),
            'action' => "Changed question index $index from " . ($oldStatus ? 'Correct' : 'Wrong') . " to " . (!$oldStatus ? 'Correct' : 'Wrong')
        ];

        DB::table('quiz_attempts')->where('id', $id)->update([
            'score' => $newScore,
            'details' => json_encode($details),
            'override_logs' => json_encode($logs)
        ]);

        return back()->with('success', 'Score updated and logged.');
    }
}