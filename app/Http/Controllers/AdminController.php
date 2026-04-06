<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Get all users to display in the management table
        $users = User::with('subscriptions')->get();
        return view('admin.users', compact('users'));
    }

    public function impersonate($id)
    {
        $userToImpersonate = User::findOrFail($id);

        // Store the original Admin ID in the session so we can switch back later
        session(['impersonator_id' => Auth::id()]);

        // Log in as the selected user
        Auth::login($userToImpersonate);

        // Redirect to flashcards or home after login
        return redirect()->route('flashcards.index')->with('success', "Now logged in as {$userToImpersonate->name}");
    }

    public function stopImpersonating()
    {
        $adminId = session('impersonator_id');

        if ($adminId) {
            // Log back in as the Admin using the ID saved in session
            Auth::loginUsingId($adminId);
            
            // Remove the impersonation flag
            session()->forget('impersonator_id');
            
            return redirect()->route('admin.users')->with('success', 'Back to Admin account.');
        }

        return redirect('/');
    }
    public function updateUser(Request $request)
    {
        try {
            $request->validate([
                'user_id'  => 'required|exists:users,id',
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email,' . $request->user_id,
                'role'     => 'required|in:student,teacher,encoder,admin,super_admin',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            $user = User::findOrFail($request->user_id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;

            if ($request->filled('password')) {
                $user->password = \Hash::make($request->password);
            }

            $user->save();

            if ($request->plan_id) {
                $plan = \DB::table('subscription_plans')->find($request->plan_id);
                \DB::table('user_subscriptions')->updateOrInsert(
                    ['user_id' => $user->id, 'status' => 'active'],
                    [
                        'plan_id'    => $plan->id,
                        'starts_at'  => now(),
                        'expires_at' => now()->addDays($plan->duration_days),
                        'updated_at' => now()
                    ]
                );
            }

            // Send success message back to the UI
            return back()->with('success', "User '{$user->name}' has been updated successfully.");

        } catch (\Exception $e) {
            // Send error message back if something fails (e.g. validation or DB error)
            return back()->with('error', "Failed to update user: " . $e->getMessage());
        }
    }
}