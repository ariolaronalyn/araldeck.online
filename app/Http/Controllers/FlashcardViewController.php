<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Flashcard;
use App\Models\Deck;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FlashcardViewController extends Controller
{

    // public function index(Request $request) {
    //     $user = auth()->user();
    //     $now = now();
    //     $view = $request->get('view', 'personal');

    //     $query = Deck::with(['subject.course'])->withCount('flashcards as card_count');

    //     if ($view === 'public') {
    //         // Exclude decks already in user library by name to prevent duplicates
    //         $myDeckNames = Deck::where('user_id', $user->id)->pluck('name');
    //         $query->where('is_public', true)
    //             ->where('user_id', '!=', $user->id)
    //             ->whereNotIn('name', $myDeckNames);
    //     } else {
    //         $query->where(function($q) use ($user, $now) {
    //             $q->where('user_id', $user->id) // Created/Cloned
    //             ->orWhereExists(function ($sub) use ($user) { // Collaborated
    //                 $sub->select(DB::raw(1))->from('collaborations')
    //                     ->whereColumn('collaborations.deck_id', 'decks.id')
    //                     ->where('collaborations.invited_user_id', $user->id)
    //                     ->where('collaborations.status', 'accepted');
    //             })
    //             ->orWhere(function($sub) use ($user, $now) { // Assigned but NOT finished
    //                 $sub->whereExists(function ($inner) use ($user) {
    //                     $inner->select(DB::raw(1))->from('class_decks')
    //                         ->join('class_students', 'class_decks.school_class_id', '=', 'class_students.school_class_id')
    //                         ->whereColumn('class_decks.deck_id', 'decks.id')
    //                         ->where('class_students.student_id', $user->id);
    //                 })
    //                 ->whereNotExists(function ($finish) use ($user) {
    //                     $finish->select(DB::raw(1))->from('quiz_attempts')
    //                         ->whereColumn('quiz_attempts.deck_id', 'decks.id')
    //                         ->where('quiz_attempts.user_id', $user->id);
    //                 });
    //             });
    //         });
    //     }

    //     $decks = $query->paginate(12);
    //     $courses = Course::all(); $subjects = Subject::all();
    //     $userClasses = ($user->role === 'teacher') ? SchoolClass::where('teacher_id', $user->id)->get() : $user->classes;

    //     return view('flashcards.index', compact('decks', 'courses', 'subjects', 'userClasses', 'request'));
    // }
    public function index(Request $request) {
        $user = auth()->user();
        // FORCED ONBOARDING: Redirect if they don't have a role yet
        $hasSubscription = \DB::table('user_subscriptions')
        ->where('user_id', $user->id)
        ->exists();

        if (!$hasSubscription && $user->role !== 'admin' && $user->role !== 'super_admin') {
            return redirect()->route('onboarding.index');
        }
        
        $now = now();
        $view = $request->get('view', 'personal');

        $hasActiveSubscription = \DB::table('user_subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();

        // Start query - Topic is automatically included as it's a column in the 'decks' table
        // Update this line in FlashcardViewController@index
        $query = Deck::with(['subject.course', 'user', 'collaborators.invitedUser']) 
            ->withCount('flashcards as card_count');

        if ($view === 'public') {
            $myDeckNames = Deck::where('user_id', $user->id)->pluck('name');
            $query->where('is_public', true)
                ->where('user_id', '!=', $user->id)
                ->whereNotIn('name', $myDeckNames)
                ->whereDoesntHave('collaborators');
        } else {
            $query->where(function($q) use ($user, $now) {
                $q->where('user_id', $user->id)
                ->orWhereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))->from('collaborations')
                        ->whereColumn('collaborations.deck_id', 'decks.id')
                        ->where('collaborations.invited_user_id', $user->id)
                        ->where('collaborations.status', 'accepted');
                })
                ->orWhere(function($sub) use ($user, $now) {
                    $sub->whereExists(function ($inner) use ($user) {
                        $inner->select(DB::raw(1))->from('class_decks')
                            ->join('class_students', 'class_decks.school_class_id', '=', 'class_students.school_class_id')
                            ->whereColumn('class_decks.deck_id', 'decks.id')
                            ->where('class_students.student_id', $user->id);
                    })
                    ->whereNotExists(function ($finish) use ($user) {
                        $finish->select(DB::raw(1))->from('quiz_attempts')
                            ->whereColumn('quiz_attempts.deck_id', 'decks.id')
                            ->where('quiz_attempts.user_id', $user->id);
                    });
                });
            });
        }

        // --- NEW: TOPIC FILTERING ---
        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }

        // Apply standard filters
        if ($request->search) $query->where('name', 'like', '%' . $request->search . '%');
        if ($request->course_id) $query->where('course_id', $request->course_id);
        if ($request->subject_id) $query->where('subject_id', $request->subject_id);

        $decks = $query->paginate(12);

        // Get Data for the Filter Dropdowns
        $courses = Course::orderBy('title')->get();
        $subjects = Subject::orderBy('title')->get();
        
        // Get unique topics available to this user to populate a filter dropdown
        $topics = Deck::where('user_id', $user->id)
                    ->whereNotNull('topic')
                    ->distinct()
                    ->pluck('topic');

        $userClasses = ($user->role === 'teacher') 
            ? SchoolClass::where('teacher_id', $user->id)->get() 
            : $user->classes;

        return view('flashcards.index', compact('decks', 'courses', 'subjects', 'topics', 'userClasses', 'request', 'hasActiveSubscription'));
    }
    private function checkUploadLimit($user, $requestedCardCount) {
        $hasActiveSubscription = \DB::table('user_subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasActiveSubscription) {
            // 1. Check if they already created a deck today
            $alreadyUploadedToday = Deck::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyUploadedToday) {
                return ['allowed' => false, 'message' => 'Free tier limit: You can only create one deck per day. Upgrade to Pro for unlimited uploads!'];
            }

            // 2. Check card count limit
            if ($requestedCardCount > 10) {
                return ['allowed' => false, 'message' => 'Free tier limit: Maximum of 10 cards per deck allowed. Upgrade to Pro for unlimited cards!'];
            }
        }
        return ['allowed' => true];
    }

    public function fetchDeck(Request $request) {
        // SECURITY: Use deck_id
        $finished = DB::table('quiz_attempts')->where('user_id', auth()->id())->where('deck_id', $request->deck_id)->exists();
        if (auth()->user()->role === 'student' && $finished) {
            return response()->json(['error' => 'already_answered', 'message' => 'You already finished this assignment.'], 403);
        }

        $deck = Deck::with('flashcards')->findOrFail($request->deck_id);
        $progress = DB::table('deck_progress')->where('user_id', auth()->id())->where('deck_id', $request->deck_id)->first();

        return response()->json(['cards' => $deck->flashcards, 'progress' => $progress]);
    }
    
    public function saveProgress(Request $request) {
        // 1. Validation - Ensure deck_id is present
        $request->validate([
            'deck_id' => 'required'
        ]);

        // 2. Handle Reset/Retake
        if (empty($request->deck_order) || $request->current_index == 0) {
            \DB::table('deck_progress')
                ->where('user_id', auth()->id())
                ->where('deck_id', $request->deck_id) // Match by ID
                ->delete();
                
            return response()->json(['status' => 'cleared_for_retake']);
        }

        // 3. Save Progress
        \DB::table('deck_progress')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'deck_id' => $request->deck_id, // Match by ID
            ],
            [
                'current_index' => $request->current_index,
                'remaining_seconds' => $request->remaining_seconds,
                'deck_order' => is_array($request->deck_order) ? json_encode($request->deck_order) : $request->deck_order,
                'score' => $request->score,
                'updated_at' => now()
            ]
        );
        
        return response()->json(['status' => 'saved']);
    }

    public function cloneDeck(Request $request)
    {
        $request->validate(['deck_id' => 'required|exists:decks,id']);
        
        $currentUser = Auth::user();
        
        // 1. Find the Source Deck
        $sourceDeck = Deck::with('flashcards')->findOrFail($request->deck_id);

        // Security check: only clone public decks unless super_admin
        if (!$sourceDeck->is_public && $currentUser->role !== 'super_admin') {
            return back()->with('error', 'You do not have permission to clone this deck.');
        }

        // 2. Prevent Duplicate Cloning (Check by source deck reference)
        $alreadyCloned = Deck::where('user_id', $currentUser->id)
                            ->where('cloned_from_id', $sourceDeck->id)
                            ->exists();

        if ($alreadyCloned) {
            return back()->with('error', 'You have already added this deck to your library.');
        }

        // 3. Create the New Deck Header
        $newDeck = Deck::create([
            'user_id'        => $currentUser->id,
            'cloned_from_id' => $sourceDeck->id, // Store source ID
            'subject_id'     => $sourceDeck->subject_id,
            'course_id'      => $sourceDeck->course_id,
            'name'           => $sourceDeck->name . " (Added)",
            'topic'          => $sourceDeck->topic,
            'type'           => $sourceDeck->type,
            'is_public'      => false, // Personal library decks are private
        ]);

        // 4. Create the Flashcards linked to the NEW deck ID
        foreach ($sourceDeck->flashcards as $card) {
            Flashcard::create([
                'deck_id'    => $newDeck->id,
                'user_id'    => $currentUser->id,
                'subject_id' => $card->subject_id,
                'question'   => $card->question,
                'answer'     => $card->answer,
                'reference'  => $card->reference,
                'topic'      => $card->topic,
            ]);
        }

        return redirect()->route('flashcards.index')->with('success', "Deck successfully added to your library.");
    }

    public function invites()
    {
        // Fetch pending collaborations for the logged-in user
        $invites = \App\Models\Collaboration::with(['invitedUser'])
            ->join('decks', 'collaborations.deck_id', '=', 'decks.id') // Join to 'decks' table
            ->where('collaborations.invited_user_id', auth()->id())
            ->where('collaborations.status', 'pending')
            ->select('collaborations.*', 'decks.name as deck_name') // Use decks.name
            ->get();

        return view('flashcards.invites', compact('invites'));
    }

    public function respondToInvite(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:accepted,rejected']);
        
        $invite = \App\Models\Collaboration::where('id', $id)
            ->where('invited_user_id', auth()->id())
            ->firstOrFail();

        $invite->update(['status' => $request->status]);

        $message = $request->status === 'accepted' ? 'Invite accepted!' : 'Invite declined.';
        return back()->with('success', $message);
    }

    // Delete an entire deck
    public function deleteDeck($id)
    {
        // Ensure the deck belongs to the user trying to delete it
        $deck = Deck::where('id', $id)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();

        $deck->delete();

        return back()->with('success', 'Deck deleted successfully.');
    }

    public function addCollaborator(Request $request)
    {
        $request->validate([
            'emails' => 'required|string',
            'deck_id' => 'required|exists:decks,id', // Changed from subject_id/name
        ]);

        // 1. Get Plan Limits
        $subscription = \DB::table('user_subscriptions')
            ->join('subscription_plans', 'user_subscriptions.plan_id', '=', 'subscription_plans.id')
            ->where('user_id', auth()->id())
            ->where('expires_at', '>', now()) 
            ->select('subscription_plans.collaborator_limit')
            ->first();

        $limit = $subscription ? $subscription->collaborator_limit : 0;

        // 2. Count existing collaborators for this DECK
        $existingCount = \App\Models\Collaboration::where('deck_id', $request->deck_id)->count();

        $emails = explode(',', $request->emails);
        $newEmailsCount = count($emails);

        if (($existingCount + $newEmailsCount) > $limit) {
            return back()->with('error', "Limit exceeded. You have $existingCount collaborators and are trying to add $newEmailsCount. Max allowed: $limit.");
        }

        $invitedCount = 0;
        foreach ($emails as $email) {
            $user = \App\Models\User::where('email', trim($email))->first();
            
            if ($user && $user->id !== auth()->id()) {
                \App\Models\Collaboration::firstOrCreate([
                    'deck_id' => $request->deck_id, // Use deck_id
                    'invited_user_id' => $user->id,
                ], ['status' => 'pending']);
                $invitedCount++;
            }
        }

        return back()->with('success', "Invites sent to $invitedCount user(s).");
    }
    public function manageDeck($id)
    {
        $deck = Deck::with('subject.course')->where('id', $id)->firstOrFail();
        $cards = Flashcard::where('deck_id', $id)->get();

        return view('flashcards.manage', compact('cards', 'deck')); // 'deck' here must match the variable in Blade
    }

    public function updateCard(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:flashcards,id',
            'question' => 'required',
            'answer' => 'required',
            'difficulty' => 'required|in:easy,average,hard',
            'topic' => 'nullable|string|max:255'
        ]);

        $card = Flashcard::where('id', $request->id)->where('user_id', auth()->id())->firstOrFail();
        $card->update($request->only(['question', 'answer', 'reference', 'difficulty', 'topic'  ]));

        return back()->with('success', 'Card updated successfully!');
    }

    // Timer Settings Logic
    public function settingsIndex() {
        $user = auth()->user();
        $plans = \DB::table('subscription_plans')->get();
        
        $timers = [
            'easy' => \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'easy')->latest('updated_at')->value('timer_seconds') ?? 30,
            'average' => \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'average')->latest('updated_at')->value('timer_seconds') ?? 20,
            'hard' => \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'hard')->latest('updated_at')->value('timer_seconds') ?? 10,
        ];

        // ADD THIS LINE TO FIX THE ERROR:
        $logs = \DB::table('quiz_attempts')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ADD 'logs' to compact:
        return view('settings.index', compact('plans', 'timers', 'logs'));
    }

    // Admin-only method to update plans
    public function updateSubscriptionPlan(Request $request) {
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) abort(403);

        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'original_price' => 'required|numeric', // Add this
            'promo_price' => 'required|numeric',
            'details' => 'required',
            'collaborator_limit' => 'required|integer'
        ]);

        \DB::table('subscription_plans')->where('id', $request->plan_id)->update([
            'original_price' => $request->original_price, // Add this
            'promo_price' => $request->promo_price,
            'details' => $request->details,
            'collaborator_limit' => $request->collaborator_limit,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Subscription plan updated successfully!');
    }
    public function updateTimerSettings(Request $request) {
        $request->validate([
            'easy' => 'required|integer',
            'average' => 'required|integer',
            'hard' => 'required|integer',
            'card_color' => 'required|string',
            'custom_labels' => 'nullable|string' // Add this to validation
        ]);

        $user = auth()->user();
        $user->card_color = $request->card_color;

        // Convert "Label 1, Label 2" string into ['Label 1', 'Label 2'] array
        if ($request->has('custom_labels')) {
            $labelsArray = array_map('trim', explode(',', $request->custom_labels));
            $user->custom_labels = array_filter($labelsArray); // remove empty values
        }

        $user->save();
        
        // 1. Save color to User Table
        $user->card_color = $request->card_color;
        $user->save();

        // 2. Update all user's flashcards with new timer values
        \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'easy')->update(['timer_seconds' => $request->easy]);
        \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'average')->update(['timer_seconds' => $request->average]);
        \App\Models\Flashcard::where('user_id', $user->id)->where('difficulty', 'hard')->update(['timer_seconds' => $request->hard]);

        // 3. Explicitly redirect to the index route with success message
        return redirect()->route('settings.index')->with('success', 'General settings updated successfully!');
    }
    public function subscribeUser(Request $request) {
        $plan = \DB::table('subscription_plans')->where('id', $request->plan_id)->first();
        if (!$plan) return back()->with('error', 'Plan not found.');

        // 1. Look for the LATEST expiry date for this user
        // We check for the max(expires_at) to ensure we stack on top of the furthest future date
        $latestExpiry = \DB::table('user_subscriptions')
            ->where('user_id', auth()->id())
            ->where('expires_at', '>', now())
            ->max('expires_at');

        // 2. Determine the Start Date
        // If they have a plan, start the new one the moment the old one ends.
        // If they don't, start it now.
        $startDate = $latestExpiry ? \Carbon\Carbon::parse($latestExpiry) : now();
        $expiryDate = $startDate->copy()->addDays($plan->duration_days);

        // 3. Insert the new subscription
        \DB::table('user_subscriptions')->insert([
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'starts_at' => $startDate,
            'expires_at' => $expiryDate,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $message = $latestExpiry 
            ? "Plan added! Your new subscription will start on " . $startDate->format('M d, Y') . " and run until " . $expiryDate->format('M d, Y')
            : "Subscription successful! Your plan is active until " . $expiryDate->format('M d, Y');

        return redirect()->route('settings.index')->with('success', $message);
    }
    public function cancelSubscription(Request $request) {
        $subscription = \DB::table('user_subscriptions')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No active subscription found.');
        }

        \DB::table('user_subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'updated_at' => now()
            ]);

        return back()->with('success', 'Your subscription has been cancelled. You will still have access until ' . \Carbon\Carbon::parse($subscription->expires_at)->format('M d, Y'));
    }
    public function resumeSubscription(Request $request) {
        $subscription = \DB::table('user_subscriptions')
            ->where('user_id', auth()->id())
            ->where('status', 'cancelled')
            ->where('expires_at', '>', now())
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No cancellable subscription found to resume.');
        }

        \DB::table('user_subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => 'active',
                'cancelled_at' => null, // Clear the cancellation timestamp
                'updated_at' => now()
            ]);

        return back()->with('success', 'Your subscription has been successfully restored to Active status!');
    }
    public function showCheckout($id) {
        $plan = \DB::table('subscription_plans')->where('id', $id)->firstOrFail();
        return view('subscription.checkout', compact('plan'));
    }

    public function processPaymongoPayment(Request $request) {
        $plan = \DB::table('subscription_plans')->where('id', $request->plan_id)->firstOrFail();
        
        // PayMongo MUST receive an integer in centavos (e.g., 199.00 becomes 19900)
        $amountInCentavos = (int) (round($plan->promo_price * 100));

        $secretKey = config('services.paymongo.secret_key');

        // Use withoutVerifying() to prevent local Mac SSL errors
        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
            ])->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [
                            [
                                'currency' => 'PHP',
                                'amount' => $amountInCentavos,
                                'description' => $plan->name,
                                'name' => $plan->name,
                                'quantity' => 1,
                            ]
                        ],
                        'payment_method_types' => ['card', 'gcash', 'paymaya'],
                        'success_url' => route('subscription.payment_success', ['plan_id' => $plan->id]),
                        'cancel_url' => route('settings.index'),
                    ]
                ]
            ]);

        // This is the CRITICAL part. If PayMongo returns an error, we need to see it.
        if ($response->failed()) {
            // For debugging: if it still doesn't work, change this to dd($response->json());
            return back()->with('error', 'PayMongo Error: ' . ($response->json()['errors'][0]['detail'] ?? 'Check API Keys'));
        }

        $checkoutUrl = $response->json()['data']['attributes']['checkout_url'];

        // Use away() for external redirects
        return redirect()->away($checkoutUrl);
    }
    public function createBulk() {
        $subjects = \App\Models\Subject::orderBy('title')->get();
        $canUpload = true;
        $uploadMessage = "";

        $hasActiveSubscription = \DB::table('user_subscriptions')->where('user_id', auth()->id())->where('status', 'active')->where('expires_at', '>', now())->exists();

        if (!$hasActiveSubscription) {
            $uploadedToday = Deck::where('user_id', auth()->id())->whereDate('created_at', now()->toDateString())->exists();
            if ($uploadedToday) {
                $canUpload = false;
                $uploadMessage = "Free limit reached: 1 upload per day. Come back tomorrow or upgrade to Pro!";
            }
        }
        return view('flashcards.create_bulk', compact('canUpload', 'uploadMessage', 'subjects'));
    }

    public function storeBulk(Request $request) {
        $user = auth()->user();

        // 1. Subscription Check
        $check = $this->checkUploadLimit($user, count($request->cards ?? []));
        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }


        $request->validate([
            'subject_id' => 'required',
            'deck_name' => 'required',
            'cards' => 'required|array|min:1',
        ]);

        foreach ($request->cards as $cardData) {
            // Skip if both fields are empty
            if (empty($cardData['question']) && empty($cardData['answer'])) continue;
            if (strtolower($cardData['question']) === 'question' && strtolower($cardData['answer']) === 'answer') continue;

            \App\Models\Flashcard::create([
                'user_id' => auth()->id(),
                'subject_id' => $request->subject_id,
                'deck_name' => $request->deck_name,
                'deck_type' => $request->deck_type,
                'question' => $cardData['question'],
                'answer' => $cardData['answer'],
                'is_public' => false,
                'difficulty' => 'average',
                'timer_seconds' => 20
            ]);
        }

        return redirect()->route('flashcards.index')->with('success', 'Bulk cards added successfully!');
    }

    public function handlePaymentSuccess($plan_id)
    {
        $plan = \DB::table('subscription_plans')->where('id', $plan_id)->firstOrFail();
        $user = auth()->user();

        // 1. Logic to handle stacking (same as your subscribeUser logic)
        $latestExpiry = \DB::table('user_subscriptions')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->max('expires_at');

        $startDate = $latestExpiry ? \Carbon\Carbon::parse($latestExpiry) : now();
        $expiryDate = $startDate->copy()->addDays($plan->duration_days);

        // 2. SAVE TO DATABASE
        \DB::table('user_subscriptions')->insert([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'starts_at'  => $startDate,
            'expires_at' => $expiryDate,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('settings.index')->with('success', "Payment Successful! Your plan is active until " . $expiryDate->format('M d, Y'));
    }
    public function updateDeckSettings(Request $request)
    {
        $request->validate([
            'deck_id' => 'required|exists:decks,id',
            'name' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255',
            'is_public' => 'required|boolean',
            'deck_type' => 'required|in:study,quiz',
            'difficulty' => 'required|in:easy,average,hard'
        ]);

        $deck = Deck::findOrFail($request->deck_id);
        
        // 1. Update the Deck Parent
        $deck->update([
            'name' => $request->name,
            'topic' => $request->topic,
            'type' => $request->deck_type,
            'is_public' => $request->is_public
        ]);

        // 2. Bulk Update all Flashcards in this deck
        \App\Models\Flashcard::where('deck_id', $deck->id)->update([
            'topic' => $request->topic,
            'difficulty' => $request->difficulty,
            'timer_seconds' => match($request->difficulty) {
                'easy' => 30,
                'average' => 20,
                'hard' => 10,
                default => 20
            }
        ]);

        return back()->with('success', 'Deck and all associated cards updated successfully!');
    }
    
    public function saveScore(Request $request) {
        $request->validate([
            'deck_name' => 'required|string',
            'score' => 'required|integer',
            'total' => 'required|integer',
            'details' => 'nullable'
        ]);

        // Insert into the quiz_attempts table
        \DB::table('quiz_attempts')->insert([
            'user_id' => auth()->id(),
            'deck_name' => $request->deck_name,
            'score' => $request->score, // This is now strictly what JS sends
            'total_questions' => $request->total,
            'details' => is_array($request->details) ? json_encode($request->details) : $request->details,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'saved']);
    }
 
    public function createManual()
    {
        $courses = Course::orderBy('title')->get();
        $subjects = Subject::orderBy('title')->get();
        $canUpload = true;
        $uploadMessage = "";

        $hasActiveSubscription = \DB::table('user_subscriptions')->where('user_id', auth()->id())->where('status', 'active')->where('expires_at', '>', now())->exists();

        if (!$hasActiveSubscription) {
            $uploadedToday = Deck::where('user_id', auth()->id())->whereDate('created_at', now()->toDateString())->exists();
            if ($uploadedToday) {
                $canUpload = false;
                $uploadMessage = "Free limit reached: 1 upload per day. Come back tomorrow or upgrade to Pro!";
            }
        }
        return view('flashcards.create_manual', compact('canUpload', 'uploadMessage', 'courses', 'subjects'));
    }

    public function storeManual(Request $request)
    {
        $user = auth()->user();
        
        // 1. Subscription Check
        $check = $this->checkUploadLimit($user, count($request->cards ?? []));
        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }
        // 1. Updated Validation to include 'topic' and 'deck_id' logic
        $request->validate([
            'course_selector' => 'required',
            'new_course_name' => 'nullable|string|max:255',
            'subject_selector' => 'required',
            'new_subject_name' => 'nullable|string|max:255',
            'deck_name' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255', // Global Deck Topic
            'is_public' => 'required|boolean',
            'deck_type' => 'required|in:study,quiz',
            'difficulty' => 'required|in:easy,average,hard',
            'cards' => 'required|array|min:1',
            'cards.*.question' => 'required|string',
            'cards.*.answer' => 'required|string',
            'cards.*.topic' => 'nullable|string|max:255', // Individual Card Topic
            'cards.*.reference' => 'nullable|string|max:255',
        ]);

        // 2. Resolve Course
        $courseTitle = ($request->course_selector === 'others') 
            ? trim($request->new_course_name) 
            : trim($request->course_selector);
        $course = Course::firstOrCreate(['title' => $courseTitle]);

        // 3. Resolve Subject
        $subjectTitle = ($request->subject_selector === 'others') 
            ? trim($request->new_subject_name) 
            : trim($request->subject_selector);
        $subject = Subject::firstOrCreate([
            'course_id' => $course->id, 
            'title' => $subjectTitle
        ]);

        // 4. Create the Parent Deck first
        // This is the step that ensures the 'decks' table is populated
        $deck = Deck::create([
            'user_id'    => auth()->id(),
            'subject_id' => $subject->id,
            'course_id'  => $course->id,
            'name'       => $request->deck_name,
            'topic'      => $request->topic, // Global topic from Step 1
            'type'       => $request->deck_type,
            'is_public'  => $request->is_public,
        ]);

        // 5. Create the Flashcards linked to the Deck ID
        foreach ($request->cards as $cardData) {
            Flashcard::create([
                'deck_id'    => $deck->id, // Link to the new deck
                'user_id'    => auth()->id(),
                'subject_id' => $subject->id,
                'question'   => $cardData['question'],
                'answer'     => $cardData['answer'],
                'topic'      => $cardData['topic'] ?? $request->topic, // Use card topic or fallback to deck topic
                'reference'  => $cardData['reference'] ?? null,
                'difficulty' => $request->difficulty,
                'timer_seconds' => match($request->difficulty) {
                    'easy'    => 30,
                    'average' => 20,
                    'hard'    => 10,
                    default   => 20
                }
            ]);
        }
        $hasActiveSubscription = \DB::table('user_subscriptions')
        ->where('user_id', $user->id)->where('status', 'active')->where('expires_at', '>', now())->exists();

    if (!$hasActiveSubscription) {
        // 1. Check if they already uploaded today
        $alreadyUploadedToday = Flashcard::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyUploadedToday) {
            return back()->with('error', 'Free tier limit: You can only create one deck per day. Upgrade to Pro for unlimited uploads!');
        }

        // 2. Check card count
        if (count($request->cards) > 10) {
            return back()->with('error', 'Free tier limit: Maximum of 10 cards per deck allowed. Upgrade to Pro for unlimited cards!');
        }
    }

        return redirect()->route('flashcards.index')->with('success', 'Deck and ' . count($request->cards) . ' cards saved successfully!');
    }

    public function checkCollaborator(Request $request){
        $email = trim($request->email);
        $deckId = $request->deck_id;

        // 1. Check if user exists
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'User does not exist.']);
        }

        // 2. Prevent inviting yourself
        if ($user->id === auth()->id()) {
            return response()->json(['valid' => false, 'message' => 'You cannot invite yourself.']);
        }

        // 3. Check if already invited to this deck
        $exists = \App\Models\Collaboration::where('deck_id', $deckId)
            ->where('invited_user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['valid' => false, 'message' => 'User is already a collaborator.']);
        }

        return response()->json(['valid' => true, 'name' => $user->name]);
    }
    public function toggleLabel(Request $request){
        $request->validate([
            'card_id' => 'required|exists:flashcards,id',
            'label' => 'required|string'
        ]);

        $card = Flashcard::findOrFail($request->card_id);
        
        // Initialize labels as an empty array if null
        $labels = $card->labels ?? [];

        if (in_array($request->label, $labels)) {
            // If it exists, remove it
            $labels = array_values(array_diff($labels, [$request->label]));
        } else {
            // If it doesn't exist, add it
            $labels[] = $request->label;
        }

        $card->labels = $labels;
        $card->save();

        return response()->json(['success' => true, 'current_labels' => $labels]);
    }
    
}