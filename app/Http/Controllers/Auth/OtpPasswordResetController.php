<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use App\Support\LoginLockout;
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
     * Signed "Enter Code" link from the email sent when an admin approves a staff
     * reset request: remembers whose code it is and opens the "enter the code" view.
     */
    public function enterFromEmail(Request $request): RedirectResponse
    {
        $request->session()->put('password_reset_otp_email', (string) $request->query('email'));

        return redirect()->route('password.otp.verify');
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

        // Locked out since the code was requested: a code can't be used to get around the lock
        if (LoginLockout::isLocked($email)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => PasswordResetLinkController::lockedResetMessage($email)]);
        }

        $record = PasswordResetOtp::where('email', $email)->first();

        if (! $record || $record->isExpired()) {
            $record?->delete();

            return back()->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            // expires_at is re-sent on purpose: MySQL/MariaDB auto-updates that timestamp column to
            // "now" on any other change, which would expire the code after one wrong attempt.
            $record->increment('attempts', 1, ['expires_at' => $record->expires_at]);

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

        // Cashier / kitchen / table server: the code only proves the email is theirs. The request
        // now goes to an admin, who resets the password to the default (last name + birthday).
        $staffUser = User::staffAccounts()->where('email', $email)->first();
        if ($staffUser && ! $staffUser->isAdmin()) {
            if (! $staffUser->passwordResetRequests()->pending()->exists()) {
                StaffPasswordResetRequest::create([
                    'user_id'      => $staffUser->id,
                    'requested_by' => $staffUser->id,
                    'status'       => 'pending',
                ]);
            }

            return redirect()->route('password.request')
                ->with('status', PasswordResetLinkController::awaitingApprovalMessage());
        }

        // Admins and customers set a new password themselves
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
        // Staff and customer passwords are both stored in users
        $user = $email ? User::where('email', $email)->first() : null;

        // Only admins and customers set their own password; other staff go through admin approval
        if (! $user || ! ($user->isAdmin() || $user->isCustomer())) {
            $request->session()->forget('password_reset_otp_verified_email');

            return redirect()->route('password.request');
        }

        if (LoginLockout::isLocked($email)) {
            $request->session()->forget('password_reset_otp_verified_email');

            return redirect()->route('password.request')
                ->withErrors(['email' => PasswordResetLinkController::lockedResetMessage($email)]);
        }

        // Resetting to the password they already have changes nothing — tell them
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'This is the same as your current password. Please choose a different one.',
            ]);
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
