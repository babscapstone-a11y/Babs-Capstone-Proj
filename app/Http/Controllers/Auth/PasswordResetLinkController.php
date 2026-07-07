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

        // Staff (admin/cashier/kitchen/table server) go through admin approval
        // instead of an instant self-service reset.
        $staffUser = User::where('email', $email)->first();

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

        if ($customer) {
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
        }

        $request->session()->put('password_reset_otp_email', $email);

        return redirect()->route('password.otp.verify')
            ->with('status', 'If an account exists for that email, a 6-digit code has been sent.');
    }
}
