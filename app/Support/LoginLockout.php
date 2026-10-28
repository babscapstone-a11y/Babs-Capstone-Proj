<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Account lockout: after MAX_ATTEMPTS wrong passwords in a row, the account can't log in or
 * reset its password for a while. The first lock lasts FIRST_LOCK_MINUTES; if the account gets
 * locked again within LOCK_HISTORY_HOURS, every further lock lasts REPEAT_LOCK_MINUTES.
 * A successful login (or an admin unlock) starts it over at the shorter lock.
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

    /** First lock: 15 minutes */
    const FIRST_LOCK_MINUTES = 15;

    /** Locked again after that: 1 hour */
    const REPEAT_LOCK_MINUTES = 60;

    /** How long an earlier lock is remembered when deciding the next lock's length */
    const LOCK_HISTORY_HOURS = 24;

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
            $minutes = self::nextLockMinutes($login);

            Cache::put($key . ':until', now()->addMinutes($minutes)->timestamp, now()->addMinutes($minutes));
            // Remember this lock, so locking again soon after gets the longer lock
            Cache::put($key . ':locks', (int) Cache::get($key . ':locks', 0) + 1, now()->addHours(self::LOCK_HISTORY_HOURS));
            Cache::forget($key . ':attempts');

            return 0;
        }

        // The count resets if there's no further wrong attempt within an hour
        Cache::put($key . ':attempts', $attempts, now()->addMinutes(self::REPEAT_LOCK_MINUTES));

        return self::MAX_ATTEMPTS - $attempts;
    }

    /** Length of the lock the account would get next: 15 minutes the first time, then 1 hour */
    public static function nextLockMinutes(string $login): int
    {
        return (int) Cache::get(self::key($login) . ':locks', 0) > 0
            ? self::REPEAT_LOCK_MINUTES
            : self::FIRST_LOCK_MINUTES;
    }

    /** e.g. "15 minutes" or "1 hour" — for "… before this account is locked for 15 minutes." */
    public static function nextLockText(string $login): string
    {
        $minutes = self::nextLockMinutes($login);

        return $minutes % 60 === 0
            ? ($minutes / 60) . ' ' . Str::plural('hour', $minutes / 60)
            : $minutes . ' ' . Str::plural('minute', $minutes);
    }

    /** Successful login (or an admin unlock): forget the failed attempts, any lock and past locks. */
    public static function clear(string $login): void
    {
        Cache::forget(self::key($login) . ':attempts');
        Cache::forget(self::key($login) . ':until');
        Cache::forget(self::key($login) . ':locks');
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

        // Staff and customer logins are both users rows
        if ($id = User::whereRaw('LOWER(email) = ?', [$login])->orWhereRaw('LOWER(username) = ?', [$login])->value('id')) {
            return "login-lock:user:{$id}";
        }

        return 'login-lock:unknown:' . sha1($login);
    }
}
