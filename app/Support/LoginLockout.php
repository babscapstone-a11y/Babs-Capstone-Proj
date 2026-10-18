<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Account lockout: after MAX_ATTEMPTS wrong passwords in a row, the account can't log in or
 * reset its password for LOCK_MINUTES (counted from the last failed attempt).
 *
 * The lock belongs to the account, not to what was typed: a staff member's username and email
 * share one counter, so switching between them doesn't give extra tries. Logins that match no
 * account are counted too, so the messages don't reveal which accounts exist.
 *
 * `php artisan login:unlock {login}` lifts a lock early.
 */
class LoginLockout
{
    const MAX_ATTEMPTS = 3;

    const LOCK_MINUTES = 60;

    public static function isLocked(string $login): bool
    {
        return self::secondsLeft($login) > 0;
    }

    public static function secondsLeft(string $login): int
    {
        $until = (int) Cache::get(self::key($login) . ':until', 0);

        return max(0, $until - now()->timestamp);
    }

    /** Count one wrong password. Returns how many attempts are left (0 = now locked). */
    public static function recordFailure(string $login): int
    {
        $key = self::key($login);
        $attempts = (int) Cache::get($key . ':attempts', 0) + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::put($key . ':until', now()->addMinutes(self::LOCK_MINUTES)->timestamp, now()->addMinutes(self::LOCK_MINUTES));
            Cache::forget($key . ':attempts');

            return 0;
        }

        // The count resets if there's no further wrong attempt within the lock window
        Cache::put($key . ':attempts', $attempts, now()->addMinutes(self::LOCK_MINUTES));

        return self::MAX_ATTEMPTS - $attempts;
    }

    /** Successful login (or an admin unlock): forget the failed attempts and any lock. */
    public static function clear(string $login): void
    {
        Cache::forget(self::key($login) . ':attempts');
        Cache::forget(self::key($login) . ':until');
    }

    /** e.g. "Try again in 58 minutes." (a duration, not a clock time) */
    public static function retryText(string $login): string
    {
        $minutes = max(1, (int) ceil(self::secondsLeft($login) / 60));

        return 'Try again in ' . $minutes . ' ' . Str::plural('minute', $minutes) . '.';
    }

    public static function lockedMessage(string $login): string
    {
        return 'This account is locked after ' . self::MAX_ATTEMPTS . ' failed login attempts. ' . self::retryText($login);
    }

    /** One key per account, whichever of its identifiers (email / username) was typed. */
    private static function key(string $login): string
    {
        $login = Str::lower(trim($login));

        if ($id = User::whereRaw('LOWER(email) = ?', [$login])->orWhereRaw('LOWER(username) = ?', [$login])->value('id')) {
            return "login-lock:staff:{$id}";
        }
        if ($id = Customer::whereRaw('LOWER(email) = ?', [$login])->value('id')) {
            return "login-lock:customer:{$id}";
        }

        return 'login-lock:unknown:' . sha1($login);
    }
}
