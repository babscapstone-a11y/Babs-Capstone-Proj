<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpPasswordResetController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    /**
     * Display the "enter the code" view.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_otp_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('password_reset_otp_email'),
        ]);
    }

    /**
     * Verify the submitted code.
     *
     * @throws ValidationException
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('password_reset_otp_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $record = PasswordResetOtp::where('email', $email)->first();

        if (! $record || $record->isExpired()) {
            $record?->delete();

            return back()->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            $record->increment('attempts');

            if ($record->attempts >= self::MAX_ATTEMPTS) {
                $record->delete();
                $request->session()->forget('password_reset_otp_email');

                return redirect()->route('password.request')
                    ->withErrors(['email' => 'Too many incorrect attempts. Please request a new code.']);
            }

            return back()->withErrors(['otp' => 'That code is incorrect.']);
        }

        $record->delete();
        $request->session()->forget('password_reset_otp_email');
        $request->session()->put('password_reset_otp_verified_email', $email);

        return redirect()->route('password.otp.reset');
    }

    /**
     * Display the "set a new password" view.
     */
    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_otp_verified_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password-otp');
    }

    /**
     * Apply the new password.
     *
     * @throws ValidationException
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('password_reset_otp_verified_email');
        $user = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            return redirect()->route('password.request');
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget('password_reset_otp_verified_email');

        return redirect()->route('login')->with('status', 'Your password has been reset.');
    }
}
