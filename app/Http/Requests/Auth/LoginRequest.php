<?php

namespace App\Http\Requests\Auth;

use App\Models\EmailVerificationOtp;
use App\Notifications\EmailVerificationOtpNotification;
use App\Support\LoginLockout;
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
            'login' => ['required', 'string'],
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

        $login = $this->string('login')->toString();
        $isEmail = str_contains($login, '@');

        // Locked after too many wrong passwords: refuse even a correct password until it expires
        if (LoginLockout::isLocked($login)) {
            throw ValidationException::withMessages(['login' => LoginLockout::lockedMessage($login)]);
        }

        // Staff/admin accounts can sign in with either their email or username;
        // customers only ever have an email, so a bare username never matches them.
        $staffCredentials = [$isEmail ? 'email' : 'username' => $login, 'password' => $this->password];

        if (Auth::guard('staff')->attempt($staffCredentials, $this->boolean('remember'))) {
            $guard = 'staff';
        } elseif ($isEmail && Auth::guard('customer')->attempt(['email' => $login, 'password' => $this->password], $this->boolean('remember'))) {
            $guard = 'customer';
        } else {
            RateLimiter::hit($this->throttleKey());

            // Count the wrong password; the third one locks the account (15 minutes, then 1 hour if it happens again)
            $left = LoginLockout::recordFailure($login);

            throw ValidationException::withMessages([
                'login' => $left === 0
                    ? LoginLockout::lockedMessage($login)
                    : 'Invalid Username or Password. ' . $left . ' ' . Str::plural('attempt', $left)
                        . ' left before this account is locked for ' . LoginLockout::nextLockText($login) . '.',
            ]);
        }

        // REQ012: Reject inactive accounts immediately on login
        if (! Auth::guard($guard)->user()->isActive()) {
            // Customers are told why (the reason the admin entered). This only runs after the
            // password was accepted, so the reason isn't shown to anyone else.
            $message = $guard === 'customer'
                ? Auth::guard($guard)->user()->deactivationNotice()
                : 'Your account has been deactivated. Please contact the administrator.';

            Auth::guard($guard)->logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages(['login' => $message]);
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
                'login' => 'Please verify your email before logging in. We\'ve sent a new verification code to your email address.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        LoginLockout::clear($login);
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
            'login' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
