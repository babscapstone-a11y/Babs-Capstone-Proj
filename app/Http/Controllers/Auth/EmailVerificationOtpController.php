<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\EmailVerificationOtp;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailVerificationOtpController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    /**
     * Display the "enter the code" view.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('registration_otp_email')) {
            return redirect()->route('login');
        }

        return view('auth.verify-registration-otp', [
            'email' => $request->session()->get('registration_otp_email'),
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

        $email = $request->session()->get('registration_otp_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $record = EmailVerificationOtp::where('email', $email)->first();

        if (! $record || $record->isExpired()) {
            $record?->delete();

            return back()->withErrors(['otp' => 'This code has expired. Please request a new one.']);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            $record->increment('attempts');

            if ($record->attempts >= self::MAX_ATTEMPTS) {
                $record->delete();
                $request->session()->forget('registration_otp_email');

                return redirect()->route('login')
                    ->withErrors(['email' => 'Too many incorrect attempts. Please register again or request a new code.']);
            }

            return back()->withErrors(['otp' => 'That code is incorrect.']);
        }

        $customer = Customer::where('email', $email)->first();

        if ($customer) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        }

        $record->delete();
        $request->session()->forget('registration_otp_email');

        return redirect()->route('login')->with('status', 'Your email has been verified. You can now log in.');
    }

    /**
     * Resend a fresh verification code.
     */
    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('registration_otp_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $customer = Customer::where('email', $email)->first();

        if ($customer && ! $customer->isEmailVerified()) {
            $otp = (string) random_int(100000, 999999);

            EmailVerificationOtp::updateOrCreate(
                ['email' => $email],
                [
                    'otp' => Hash::make($otp),
                    'attempts' => 0,
                    'expires_at' => now()->addMinutes(10),
                    'created_at' => now(),
                ]
            );

            $customer->notify(new EmailVerificationOtpNotification($otp));
        }

        return redirect()->route('register.otp.verify')
            ->with('status', 'A new verification code has been sent to your email.');
    }
}
