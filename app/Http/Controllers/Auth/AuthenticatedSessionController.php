<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Role-based hard redirect — clear stored intended URL so login never
        // bounces a customer to /dashboard (admin-only) or vice versa.
        $request->session()->forget('url.intended');

        if (Auth::guard('customer')->check()) {
            return redirect()->route('catalog.index');
        }

        if (Auth::guard('staff')->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        if (Auth::guard('staff')->user()->isKitchenStaff()) {
            return redirect()->route('kitchen.index');
        }

        if (Auth::guard('staff')->user()->isTableServer()) {
            return redirect()->route('table-server.index');
        }

        return redirect()->route('profile.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        } else {
            Auth::guard('staff')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
