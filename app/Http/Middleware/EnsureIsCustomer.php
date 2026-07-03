<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('customer')->check()) {
            if (Auth::guard('staff')->check()) {
                return redirect()->route('dashboard');
            }
            abort(403, 'Access denied. This area is for customers only.');
        }

        return $next($request);
    }
}
