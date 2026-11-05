<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * One login per account: users.current_session_id names the session the account is signed
 * in on, and a login from anywhere else is refused while that session is still alive.
 *
 * "Alive" = its row is still in the sessions table (logging out deletes it) and it was used
 * within the session lifetime (SESSION_LIFETIME, 120 minutes), so a browser that was simply
 * closed without logging out frees the account once its session expires.
 */
class SingleSession
{
    public const IN_USE_MESSAGE = 'This account is already logged in on another device. '
        . 'Log out there first.';

    /** The `users` row behind a signed-in staff member or customer. */
    public static function accountId(Authenticatable $user): int
    {
        return $user instanceof Customer ? (int) $user->user_id : (int) $user->getAuthIdentifier();
    }

    /** The session the account is currently signed in on, if any. */
    public static function ownerOf(Authenticatable $user): ?string
    {
        return DB::table('users')->where('id', self::accountId($user))->value('current_session_id');
    }

    /** Is that session still alive (not logged out, not expired)? */
    public static function isAlive(?string $sessionId): bool
    {
        if (! $sessionId) {
            return false;
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('id', $sessionId)
            ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime'))->getTimestamp())
            ->exists();
    }

    /** Is the account signed in on a live session other than this one? */
    public static function isInUseElsewhere(Authenticatable $user, string $currentSessionId): bool
    {
        $owner = self::ownerOf($user);

        return $owner !== null && $owner !== $currentSessionId && self::isAlive($owner);
    }

    /** Record this session as the one the account is signed in on. */
    public static function claim(Authenticatable $user, string $sessionId): void
    {
        DB::table('users')->where('id', self::accountId($user))->update(['current_session_id' => $sessionId]);
    }

    /** Free the account on logout (only if this session still owns it). */
    public static function release(Authenticatable $user, string $sessionId): void
    {
        DB::table('users')
            ->where('id', self::accountId($user))
            ->where('current_session_id', $sessionId)
            ->update(['current_session_id' => null]);
    }
}
