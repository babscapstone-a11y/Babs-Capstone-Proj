<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isCustomer()) {
            if ($user && $user->isAdmin()) {
                return redirect()->route('dashboard');
            }
            abort(403, 'Access denied. This area is for customers only.');
        }

        return $next($request);
    }
}
