<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(Customer::class),
                Rule::unique(User::class),
            ],
            'phone'      => ['nullable', 'digits:11'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.digits' => 'Phone number must be exactly 11 digits.',
        ]);

        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'contact_no' => $request->phone,
            'status'     => 'active',
        ]);

        $otp = (string) random_int(100000, 999999);

        EmailVerificationOtp::updateOrCreate(
            ['email' => $customer->email],
            [
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ]
        );

        $customer->notify(new EmailVerificationOtpNotification($otp));

        $request->session()->put('registration_otp_email', $customer->email);

        // Do NOT auto-login. The customer must verify their email before they can log in.
        return redirect()->route('register.otp.verify')
            ->with('status', "We've sent a 6-digit verification code to your email address.");
    }
}
