<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsKitchenStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isKitchenStaff()) {
            abort(403, 'Access denied. Kitchen staff privileges required.');
        }

        return $next($request);
    }
}
