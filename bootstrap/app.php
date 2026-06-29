<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'          => \App\Http\Middleware\EnsureIsAdmin::class,
            'customer'       => \App\Http\Middleware\EnsureIsCustomer::class,
            'account.active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);

        // When an already-authenticated user hits a guest-only route (/login, /register),
        // redirect them based on their role instead of blindly sending everyone to /dashboard.
        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();
            if ($user && $user->isCustomer()) {
                return route('catalog.index');
            }
            return route('dashboard');
        });

        // Enforce active-account check on every authenticated web request
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureAccountIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
