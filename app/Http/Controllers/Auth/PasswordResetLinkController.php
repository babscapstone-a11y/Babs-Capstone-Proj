<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PasswordResetOtp;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
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

        $staffUser = User::where('email', $email)->first();

        // Admins approve everyone else's requests, so they don't wait for approval themselves:
        // they get the same emailed reset link an approved request produces, straight away.
        if ($staffUser && $staffUser->isAdmin()) {
            if (! $staffUser->isActive()) {
                return back()->withInput()
                    ->withErrors(['email' => 'This account has been deactivated, so its password can\'t be reset.']);
            }

            $status = Password::sendResetLink(['email' => $staffUser->email]);

            if ($status === Password::RESET_THROTTLED) {
                return back()->withInput()
                    ->withErrors(['email' => 'A reset link was just sent. Please check your email, or wait a minute before trying again.']);
            }
            if ($status !== Password::RESET_LINK_SENT) {
                return back()->withInput()
                    ->withErrors(['email' => 'We couldn\'t send the reset email. Please try again in a moment.']);
            }

            return redirect()->route('password.request')
                ->with('status', 'A password reset link has been sent to your email.');
        }

        // Other staff (cashier / kitchen / table server) go through admin approval
        // instead of an instant self-service reset.
        if ($staffUser) {
            if (! $staffUser->passwordResetRequests()->pending()->exists()) {
                StaffPasswordResetRequest::create([
                    'user_id'      => $staffUser->id,
                    'requested_by' => $staffUser->id,
                    'status'       => 'pending',
                ]);
            }

            return redirect()->route('password.request')
                ->with('status', 'Your password reset request has been submitted. An admin will review it and email you a reset link once approved.');
        }

        $customer = Customer::where('email', $email)->first();

        // Tell the user when the email isn't registered, so a typo can be fixed right away.
        // (This route is rate-limited — throttle:6,1 — which limits using it to probe for accounts.)
        if (! $customer) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'We couldn\'t find an account with that email address. Please check it and try again.']);
        }

        $otp = (string) random_int(100000, 999999);

        PasswordResetOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ]
        );

        $customer->notify(new PasswordResetOtpNotification($otp));

        $request->session()->put('password_reset_otp_email', $email);

        return redirect()->route('password.otp.verify')
            ->with('status', 'A 6-digit code has been sent to your email.');
    }
}
