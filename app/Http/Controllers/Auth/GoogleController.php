<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User exists, log them in
                $user->update(['google_id' => $googleUser->id]);
                Auth::login($user);
            } else {
                // New user registration
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => null, 
                    'role' => null, // Ensure role is null for onboarding trigger
                ]);
                Auth::login($user);
            }

            // REDIRECT LOGIC
            // If the user has no role, they haven't finished onboarding
            if (is_null($user->role)) {
                return redirect()->route('onboarding.index');
            }

            return redirect()->route('home');

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed.');
        }
    }
}