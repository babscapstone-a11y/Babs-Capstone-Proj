<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsTableServer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isTableServer()) {
            abort(403, 'Access denied. Table server privileges required.');
        }

        return $next($request);
    }
}
