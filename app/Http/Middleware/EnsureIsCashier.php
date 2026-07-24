<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsCashier
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isCashier()) {
            abort(403, 'Access denied. Cashier privileges required.');
        }

        return $next($request);
    }
}
