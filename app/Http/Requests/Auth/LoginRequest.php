<?php

namespace App\Http\Requests\Auth;

use App\Models\EmailVerificationOtp;
use App\Notifications\EmailVerificationOtpNotification;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        if (Auth::guard('staff')->attempt($credentials, $remember)) {
            $guard = 'staff';
        } elseif (Auth::guard('customer')->attempt($credentials, $remember)) {
            $guard = 'customer';
        } else {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // REQ012: Reject inactive accounts immediately on login
        if (! Auth::guard($guard)->user()->isActive()) {
            Auth::guard($guard)->logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ]);
        }

        // Customers must verify their email before they can log in.
        if ($guard === 'customer' && ! Auth::guard($guard)->user()->isEmailVerified()) {
            $customer = Auth::guard($guard)->user();
            Auth::guard($guard)->logout();
            RateLimiter::hit($this->throttleKey());

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

            $this->session()->put('registration_otp_email', $customer->email);

            throw ValidationException::withMessages([
                'email' => 'Please verify your email before logging in. We\'ve sent a new verification code to your email address.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
