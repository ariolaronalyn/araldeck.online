<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FlashcardExport;
use Illuminate\Support\Facades\DB; // Required for database queries

class PDFExtractionController extends Controller
{
    /**
     * Show the extraction form with database values.
     */
    public function showForm()
    {
        $courses = DB::table('courses')->get(); // Adjust table name if different
        $subjects = DB::table('subjects')->get(); // Adjust table name if different

        return view('flashcards.extract_pdf', compact('courses', 'subjects'));
    }

    /**
     * Process the PDF and export XLSX.
     */
    public function extract(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '600');

        $request->validate([
            'pdf_file' => 'required|mimes:pdf',
            'pdf_type' => 'required|in:compendious',
            'row_limit' => 'required|integer|min:1',
            'course' => 'required|string',
            'subject' => 'required|string',
        ]);

        // Determine actual Course and Subject (Handling "Other" input)
        $course = $request->course === 'other' ? $request->new_course : $request->course;
        $subject = $request->subject === 'other' ? $request->new_subject : $request->subject;

        if (empty($course) || empty($subject)) {
            return back()->with('error', 'Please provide both Course and Subject names.');
        }

        $uploadedFilePath = $request->file('pdf_file')->getPathname();

        // Pass Course and Subject to the parsing logic
        $rows = $this->parseCompendious($uploadedFilePath, $request->row_limit, $course, $subject);

        return Excel::download(
            new FlashcardExport($rows, $course, $subject), 
            'Extracted_QA.xlsx'
        );
    }

private function parseCompendious($filePath, $limit, $course, $subject)
{
    $leftPath = storage_path('app/left.txt');
    $rightPath = storage_path('app/right.txt');
    $binary = "/opt/homebrew/bin/pdftotext";

    // 1. CROP COORDINATES (Adjusted for Book Spreads)
    // -y 50: Skips the top 50 units (Headers like "CIVIL LAW")
    // -H 800: Only captures the middle 800 units, cutting off the Footer area
    // -W 380: Narrower width to ensure we don't bleed into the other page
    
    // LEFT PASS
    shell_exec("$binary -layout -x 20 -y 50 -W 380 -H 820 " . escapeshellarg($filePath) . " " . escapeshellarg($leftPath));
    
    // RIGHT PASS (Starts at x=420 to skip the "gutter/spine" of the book)
    shell_exec("$binary -layout -x 420 -y 50 -W 380 -H 820 " . escapeshellarg($filePath) . " " . escapeshellarg($rightPath));

    if (!file_exists($leftPath) || !file_exists($rightPath)) return [];

    $fullText = file_get_contents($leftPath) . "\n" . file_get_contents($rightPath);
    unlink($leftPath);
    unlink($rightPath);

    $lines = explode("\n", $fullText);
    $data = [];
    $currentTopic = "General Knowledge";
    $currentQuestion = null;
    $currentAnswer = "";

    foreach ($lines as $line) {
        if (count($data) >= $limit) break;

        $line = trim(preg_replace('/\s+/', ' ', $line));

        // 2. AGGRESSIVE FOOTER FILTERING
        if (empty($line) || is_numeric($line)) continue;
        
        // Skip specific recurring text seen in your screenshots
        if (preg_match('/UNIVERSITY OF THE CORDILLERAS|BAR REVIEW CENTER|PERSONS|CIVIL LAW|COMPENDIOUS|Page \d+/i', $line)) {
            continue;
        }

        // 3. DETECT TOPIC
        if (preg_match('/^\d+\.\s+(.*)/', $line, $matches)) {
            $currentTopic = trim($matches[1]);
            continue;
        }

        // 4. DETECT QUESTION
        if (preg_match('/^Q[\s\.]*[:\.]\s*(.*)/i', $line, $matches)) {
            if ($currentQuestion && $currentAnswer) {
                $data[] = $this->formatRow($currentQuestion, $currentAnswer, $currentTopic, $course, $subject);
            }
            if (count($data) >= $limit) break;
            $currentQuestion = trim($matches[1]);
            $currentAnswer = ""; 
            continue;
        }

        // 5. DETECT ANSWER
        if (preg_match('/^A[\s\.]*[:\.]\s*(.*)/i', $line, $matches)) {
            $currentAnswer = trim($matches[1]);
            continue;
        }

        // 6. APPEND TEXT
        if ($currentQuestion !== null && $currentAnswer === "") {
            $currentQuestion .= " " . $line;
        } elseif ($currentAnswer !== "") {
            $currentAnswer .= " " . $line;
        }
    }

    if (count($data) < $limit && $currentQuestion && $currentAnswer) {
        $data[] = $this->formatRow($currentQuestion, $currentAnswer, $currentTopic, $course, $subject);
    }

    return $data;
}
    private function formatRow($q, $a, $topic, $course, $subject)
    {
        return [
            'Course' => $course,
            'Subject' => $subject, 
            'Question' => trim($q),
            'Answer' => trim($a),
            'Reference' => 'Compendious', // Hardcoded as requested
            'Topic' => $topic
        ];
    }
}