<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A staff member still on a default password (last name + birthday) can't use the system
 * until they set their own: every page sends them to "Set Your Password" (signing out still works).
 */
class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('staff')->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.first', 'password.first.update', 'logout')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please set a new password before continuing.'], 403);
            }

            return redirect()->route('password.first');
        }

        return $next($request);
    }
}
