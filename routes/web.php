<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FlashcardUploadController;
use App\Http\Controllers\FlashcardViewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ClassroomController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PDFExtractionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\ExamSubmissionController;

// Public Routes
Route::get('/', function () {
    $plans = DB::table('subscription_plans')->get();
    return view('welcome', compact('plans') );
});

Auth::routes();

// Auth-Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');
    
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // --- Flashcard Viewing & Study/Quiz logic ---
    Route::get('/flashcards', [FlashcardViewController::class, 'index'])->name('flashcards.index');
    Route::get('/flashcards/fetch-deck', [FlashcardViewController::class, 'fetchDeck'])->name('flashcards.fetch_deck');
    Route::post('/flashcards/save-progress', [FlashcardViewController::class, 'saveProgress'])->name('decks.save_progress');
    Route::post('/quiz/save-score', [FlashcardViewController::class, 'saveScore'])->name('quiz.save_score');
    Route::post('/decks/clone', [FlashcardViewController::class, 'cloneDeck'])->name('decks.clone');
    Route::get('/decks/check-collaborator', [FlashcardViewController::class, 'checkCollaborator'])->name('decks.check_collab');
    Route::post('/flashcards/toggle-label', [FlashcardViewController::class, 'toggleLabel'])->name('flashcards.toggle_label');

    //PDF extraction
    Route::get('/extract-pdf', function () {
        return view('flashcards.extract_pdf');
    })->name('pdf.form');
    // Route::post('/extract-pdf', [PDFExtractionController::class, 'extract'])->name('pdf.extract');
    
    // FOR pdf to excel conversion
    Route::get('/extract-pdf', [PDFExtractionController::class, 'showForm'])->name('pdf.form');
    Route::post('/extract-pdf', [PDFExtractionController::class, 'extract'])->name('pdf.extract');
    
    // Creation
    Route::get('/flashcards/create-bulk', [FlashcardViewController::class, 'createBulk'])->name('flashcards.create_bulk');
    Route::post('/flashcards/store-bulk', [FlashcardViewController::class, 'storeBulk'])->name('flashcards.store_bulk');
    Route::get('/flashcards/create-manual', [FlashcardViewController::class, 'createManual'])->name('flashcards.create_manual');
    Route::post('/flashcards/store-manual', [FlashcardViewController::class, 'storeManual'])->name('flashcards.store_manual');
    
    // --- UPDATED: Deck Management (Now using unique Deck ID) ---
    Route::get('/flashcards/{id}/manage', [FlashcardViewController::class, 'manageDeck'])->name('decks.manage');
    Route::post('/flashcards/{id}/update-settings', [FlashcardViewController::class, 'updateDeckSettings'])->name('decks.update_settings');
    Route::post('/flashcards/update-card', [FlashcardViewController::class, 'updateCard'])->name('flashcards.update');
    Route::delete('/decks/{id}', [FlashcardViewController::class, 'deleteDeck'])->name('decks.delete');
    
    // --- Settings & Logs ---
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/timer', [FlashcardViewController::class, 'updateTimerSettings'])->name('settings.update_timer');
    Route::post('/settings/admin/update-plan', [FlashcardViewController::class, 'updateSubscriptionPlan'])->name('admin.update_plan');
    Route::get('/settings/logs/{id}', [SettingsController::class, 'getLogSummary'])->name('settings.logs.summary');
    Route::get('/settings/logs/{id}/details', [SettingsController::class, 'showLogDetails'])->name('settings.logs.show');
    Route::post('/settings/logs/{id}/override', [SettingsController::class, 'overrideScore'])->name('settings.logs.override');  
    
    // --- Collaborations ---
    Route::get('/invites', [FlashcardViewController::class, 'invites'])->name('invites.index');
    Route::post('/invites/{id}/respond', [FlashcardViewController::class, 'respondToInvite'])->name('invites.respond');
    Route::post('/decks/invite', [FlashcardViewController::class, 'addCollaborator'])->name('decks.invite');
    
    // --- Subscriptions ---
    Route::get('/checkout/{id}', [FlashcardViewController::class, 'showCheckout'])->name('subscription.show_checkout');
    Route::post('/subscribe/paymongo', [FlashcardViewController::class, 'processPaymongoPayment'])->name('subscription.paymongo');
    Route::post('/subscribe/checkout', [FlashcardViewController::class, 'subscribeUser'])->name('subscription.checkout');
    Route::post('/subscribe/cancel', [FlashcardViewController::class, 'cancelSubscription'])->name('subscription.cancel');
    Route::post('/subscribe/resume', [FlashcardViewController::class, 'resumeSubscription'])->name('subscription.resume');
    Route::get('/subscribe/success/{plan_id}', [FlashcardViewController::class, 'handlePaymentSuccess'])->name('subscription.payment_success');


    // Question Bank
    Route::get('/question-bank', [ExamQuestionController::class, 'index'])->name('questions.index');
    Route::post('/questions/clone/{id}', [ExamQuestionController::class, 'clone'])->name('questions.clone');
    Route::post('/question-bank/store', [ExamQuestionController::class, 'store'])->name('questions.store');
    Route::post('/questions/clone/{id}', [ExamQuestionController::class, 'clone'])->name('questions.clone');
    Route::get('/users/check-email', function (Illuminate\Http\Request $request) {
        $exists = \App\Models\User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    })->name('users.check_email');

    Route::get('/question-bank/create', [ExamQuestionController::class, 'create'])->name('questions.create');
    Route::post('/question-bank/store-bulk', [ExamQuestionController::class, 'storeBulk'])->name('questions.store_bulk');


    // Exams
    Route::resource('exams', ExamController::class);


    // Taking the Exam
    Route::get('/exams/{id}/start', [ExamSubmissionController::class, 'start'])->name('exams.start');
    // Route::post('/exams/save-answer', [ExamSubmissionController::class, 'saveAnswer'])->name('exams.save_answer');
    Route::post('/exams/pause', [ExamSubmissionController::class, 'pause'])->name('exams.pause');
    Route::get('/exams/{id}/results', [App\Http\Controllers\ExamController::class, 'results'])->name('exams.results');
    Route::get('/exams/{id}/start', [App\Http\Controllers\ExamSubmissionController::class, 'start'])->name('exams.start');
    Route::get('/exams/{id}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{id}/update', [ExamController::class, 'update'])->name('exams.update');

    Route::post('/exams/log-incident', [ExamSubmissionController::class, 'logIncident'])->name('exams.log_incident');

    Route::post('/exams/save-answer', [ExamSubmissionController::class, 'saveAnswer'])->name('exams.save_answer');
    Route::post('/exams/save-time', [ExamSubmissionController::class, 'saveTime'])->name('exams.save_time_log');

    // Pause Exam
    Route::post('/exams/pause', [ExamSubmissionController::class, 'pause'])->name('exams.pause');


    // Grading & Comments
    Route::post('/exams/grade', [ExamSubmissionController::class, 'updateGrade'])->name('exams.grade');
    Route::post('/exams/comment', [ExamSubmissionController::class, 'addComment'])->name('exams.comment');

    Route::post('/exams/submit', [ExamSubmissionController::class, 'finishSubmission'])->name('exams.submit');


    // --- Uploads (Restricted Roles) ---
    Route::middleware(['role:student,teacher,encoder,admin,super_admin'])->group(function () {
        Route::get('/upload', [FlashcardUploadController::class, 'index'])->name('csv.form');
        Route::post('/upload', [FlashcardUploadController::class, 'upload'])->name('csv.upload');
        Route::get('/download-template', [FlashcardUploadController::class, 'downloadTemplate'])->name('csv.download_template');
    });
});

// --- Admin & Super Admin Management ---
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users');
    Route::post('/admin/users/update', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::post('/admin/impersonate/{id}', [AdminController::class, 'impersonate'])->name('admin.impersonate');

    // Super Admin Settings : Exam
    Route::post('/settings/assessment-types', function(Request $request) {
        if(auth()->user()->role !== 'super_admin') abort(403);
        \App\Models\AssessmentType::create(['name' => $request->name]);
        return back();
    })->name('settings.add_assessment_type');   
});

// --- Classroom (Shared: Student & Teacher) ---
Route::middleware(['auth', 'role:teacher,student'])->group(function () {
    Route::get('/classroom', [ClassroomController::class, 'index'])->name('classroom.index');
    Route::get('/classroom/{id}', [ClassroomController::class, 'show'])->name('classroom.show');
});

// --- Classroom (Teacher Actions Only) ---
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::post('/classroom', [ClassroomController::class, 'store'])->name('classroom.store');
    Route::post('/classroom/{id}/add-student', [ClassroomController::class, 'addStudent'])->name('classroom.add_student');
    Route::post('/classroom/{id}/schedule', [ClassroomController::class, 'scheduleDeck'])->name('classroom.schedule_deck');
    
    // UPDATED: Report now uses deckId (Integer)
    Route::get('/classroom/{id}/report/{deckId}', [ClassroomController::class, 'report'])->name('classroom.report');
    
    Route::post('/classroom/{id}/schedule/{scheduleId}/update', [ClassroomController::class, 'updateDeckSchedule'])->name('classroom.update_schedule');
    Route::post('/classroom/{id}/schedule/{scheduleId}/close', [ClassroomController::class, 'closeDeckSchedule'])->name('classroom.close_schedule');
    Route::post('/classroom/{id}/schedule/{scheduleId}/reopen', [ClassroomController::class, 'reopenDeckSchedule'])->name('classroom.reopen_schedule');
});

// Impersonation Utility
Route::post('/admin/stop-impersonating', [AdminController::class, 'stopImpersonating'])->name('admin.stop');

// Webhooks
Route::post('/webhooks/paymongo', [WebhookController::class, 'handlePaymongo']);

//Google Auth
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/check-email', [App\Http\Controllers\Auth\EmailCheckController::class, 'check'])->name('email.check');
