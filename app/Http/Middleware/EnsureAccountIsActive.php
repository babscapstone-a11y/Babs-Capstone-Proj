<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = match (true) {
            Auth::guard('staff')->check() => 'staff',
            Auth::guard('customer')->check() => 'customer',
            default => null,
        };

        if ($guard && ! Auth::guard($guard)->user()->isActive()) {
            // A customer deactivated while logged in is told why, same as at login
            $message = $guard === 'customer'
                ? Auth::guard($guard)->user()->deactivationNotice()
                : 'Your account has been deactivated. Please contact the administrator.';

            Auth::guard($guard)->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['login' => $message]);
        }

        return $next($request);
    }
}
