<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Staff on a default password (new account, or reset by an admin) set their own one here
 * before they can use the system — see EnsurePasswordIsChanged.
 */
class FirstPasswordController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! $user->must_change_password) {
            return redirect($user->homeRoute());
        }

        return view('auth.first-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $request->validate([
            // Same strong-password rule as everywhere else (AppServiceProvider: Password::defaults)
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // The default (last name + birthday) is easy to guess, so it can't be kept
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Your new password must be different from your default password.']);
        }

        $user->forceFill([
            'password'             => $request->password,
            'must_change_password' => false,
        ])->save();

        $request->session()->regenerate();

        return redirect($user->homeRoute())
            ->with('success', 'Your new password has been saved.');
    }
}
