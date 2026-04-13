<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index(){
        $user = auth()->user();

        // Check if they already onboarded
        $hasOnboarded = \DB::table('user_subscriptions')
            ->where('user_id', $user->id)
            ->exists();

        // CRITICAL: If they have already onboarded, DO NOT let them stay here.
        // This breaks the loop.
        if ($hasOnboarded) {
            return redirect()->route('flashcards.index');
        }

        $plans = \DB::table('subscription_plans')->get();
        return view('auth.onboarding', compact('plans'));
    }

    public function store(Request $request)
    {
        // 1. Validation - We allow 'trial' or a real numeric ID
        $request->validate([
            'role' => 'required|in:student,teacher',
            'plan_id' => 'required' 
        ], [
            'role.required' => 'Please tell us if you are a Student or a Teacher.',
            'plan_id.required' => 'Please select a subscription plan to continue.',
        ]);

        $user = Auth::user();
        $user->role = $request->role;
        $user->save();

        // 2. Handle the "1 Day Free Trial" case
        if ($request->plan_id === 'trial') {
            $trialPlan = DB::table('subscription_plans')->where('promo_price', 0)->first();
            // Add this check
            if (!$trialPlan) {
                return back()->with('error', 'Trial plan not found in database. Please contact support.');
            }

            DB::table('user_subscriptions')->insert([
                'user_id' => $user->id,
                'plan_id' => $trialPlan->id,
                'starts_at' => now(),
                'expires_at' => now()->addDay(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('flashcards.index')
                ->with('success', 'Welcome to AralDeck! Your 1-day trial is now active.');
        }

        // 3. Handle Paid Plans
        $plan = DB::table('subscription_plans')->where('id', $request->plan_id)->first();

        if ($plan && $plan->promo_price > 0) {
            return redirect()->route('subscription.show_checkout', $plan->id);
        }

        return redirect()->route('flashcards.index');
    }
}