<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Support\LoginLockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the forgot-password (request a reset code) view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // "Send again" asks for a fresh code, so forget the one this browser is waiting on
        if ($request->boolean('new')) {
            $request->session()->forget('password_reset_otp_email');
        }

        // A code was already sent and is still valid (e.g. the page was refreshed while the
        // email was sending): go straight to where it's entered instead of asking again
        $pendingEmail = $request->session()->get('password_reset_otp_email');

        if ($pendingEmail && ! $request->session()->has('errors')
            && PasswordResetOtp::where('email', $pendingEmail)->where('expires_at', '>', now())->exists()) {
            $request->session()->reflash();

            return redirect()->route('password.otp.verify');
        }

        return view('auth.forgot-password');
    }

    /** Shown at every reset-password step while the account is locked out. */
    public static function lockedResetMessage(string $email): string
    {
        return 'This account is locked after ' . LoginLockout::MAX_ATTEMPTS
            . ' failed login attempts, so its password can\'t be reset right now. ' . LoginLockout::retryText($email);
    }

    /** Shown to non-admin staff once their (code-verified) request is waiting for an admin. */
    public static function awaitingApprovalMessage(): string
    {
        return 'Your password reset request has been submitted. Once an admin approves it, log in with your default password (your last name + birthday, e.g. delacruz01152000) and you will be asked to set a new one.';
    }

    /**
     * Handle an incoming password reset OTP request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;

        // A locked account (too many wrong passwords) can't get around the lock by resetting
        if (LoginLockout::isLocked($email)) {
            return back()->withInput()->withErrors(['email' => self::lockedResetMessage($email)]);
        }

        $staffUser = User::staffAccounts()->where('email', $email)->first();

        // Every staff member first proves the email is theirs with an emailed code. Admins then set
        // a new password themselves; other staff (cashier / kitchen / table server) have their
        // request sent to an admin, who resets it to the default (see OtpPasswordResetController).
        if ($staffUser) {
            if (! $staffUser->isActive()) {
                return back()->withInput()
                    ->withErrors(['email' => 'This account has been deactivated, so its password can\'t be reset.']);
            }

            if (! $staffUser->isAdmin() && $staffUser->passwordResetRequests()->pending()->exists()) {
                return redirect()->route('password.request')
                    ->with('status', self::awaitingApprovalMessage())
                    ->with('reset_request_submitted', true);
            }

            try {
                $staffUser->notify(new PasswordResetOtpNotification(PasswordResetOtp::issue($staffUser->email)));
            } catch (\Throwable $e) {
                report($e);
                PasswordResetOtp::where('email', $staffUser->email)->delete();

                return back()->withInput()
                    ->withErrors(['email' => 'We couldn\'t send the reset email. Please try again in a moment.']);
            }

            $request->session()->put('password_reset_otp_email', $staffUser->email);

            return redirect()->route('password.otp.verify')
                ->with('status', $staffUser->isAdmin()
                    ? 'A 6-digit code has been sent to your email.'
                    : 'A 6-digit code has been sent to your email. Enter it to send your password reset request to an admin.');
        }

        $customer = Customer::withEmail($email)->first();

        // Tell the user when the email isn't registered, so a typo can be fixed right away.
        // (This route is rate-limited — throttle:6,1 — which limits using it to probe for accounts.)
        if (! $customer) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'We couldn\'t find an account with that email address. Please check it and try again.']);
        }

        $customer->notify(new PasswordResetOtpNotification(PasswordResetOtp::issue($email)));

        $request->session()->put('password_reset_otp_email', $email);

        return redirect()->route('password.otp.verify')
            ->with('status', 'A 6-digit code has been sent to your email.');
    }
}
