<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordResetOtp;
use App\Models\StaffPasswordResetRequest;
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
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** Shown at every reset-password step while the account is locked out. */
    public static function lockedResetMessage(string $email): string
    {
        return 'This account is locked after ' . LoginLockout::MAX_ATTEMPTS
            . ' failed login attempts, so its password can\'t be reset right now. ' . LoginLockout::retryText($email);
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

        // Admins approve everyone else's requests, so they don't wait for approval themselves:
        // they get the same emailed code an approved request produces, straight away.
        if ($staffUser && $staffUser->isAdmin()) {
            if (! $staffUser->isActive()) {
                return back()->withInput()
                    ->withErrors(['email' => 'This account has been deactivated, so its password can\'t be reset.']);
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
                ->with('status', 'A 6-digit code has been sent to your email.');
        }

        // Other staff (cashier / kitchen / table server) go through admin approval
        // instead of an instant self-service reset.
        if ($staffUser) {
            // Already approved: the code was emailed then, so take them straight to entering it.
            $approvedCode = PasswordResetOtp::where('email', $staffUser->email)->first();
            if ($approvedCode && ! $approvedCode->isExpired()) {
                $request->session()->put('password_reset_otp_email', $staffUser->email);

                return redirect()->route('password.otp.verify')
                    ->with('status', 'Your request was approved. Enter the 6-digit code we emailed you.');
            }

            if (! $staffUser->passwordResetRequests()->pending()->exists()) {
                StaffPasswordResetRequest::create([
                    'user_id'      => $staffUser->id,
                    'requested_by' => $staffUser->id,
                    'status'       => 'pending',
                ]);
            }

            return redirect()->route('password.request')
                ->with('status', 'Your password reset request has been submitted. Once an admin approves it, log in with your default password (your last name + birthday, e.g. delacruz01152000) and you will be asked to set a new one.');
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
