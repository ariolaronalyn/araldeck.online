<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if the user is logged in
        if (!auth()->check()) {
            return redirect('/login');
        }

        // 2. Check if the user's role is in the allowed $roles array
        if (in_array(auth()->user()->role, $roles)) {
            return $next($request);
        }

        // 3. Redirect if they don't have the right role
        return redirect('/home')->with('error', 'You do not have access to this page.');
    }
}
