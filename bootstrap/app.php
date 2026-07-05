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
            'kitchen_staff'  => \App\Http\Middleware\EnsureIsKitchenStaff::class,
            'table_server'   => \App\Http\Middleware\EnsureIsTableServer::class,
            'account.active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);

        // When an already-authenticated user hits a guest-only route (/login, /register),
        // redirect them based on their role instead of blindly sending everyone to /dashboard
        // (which is admin-only and would 403 for non-admin staff).
        $middleware->redirectUsersTo(function (Request $request) {
            if (Auth::guard('customer')->check()) {
                return route('catalog.index');
            }
            if (Auth::guard('staff')->user()?->isAdmin()) {
                return route('dashboard');
            }
            if (Auth::guard('staff')->user()?->isKitchenStaff()) {
                return route('kitchen.index');
            }
            if (Auth::guard('staff')->user()?->isTableServer()) {
                return route('table-server.index');
            }
            return route('profile.edit');
        });

        // Enforce active-account check on every authenticated web request
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureAccountIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
