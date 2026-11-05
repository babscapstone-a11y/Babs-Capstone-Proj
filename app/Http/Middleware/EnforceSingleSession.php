<?php

namespace App\Http\Middleware;

use App\Support\SingleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backs up the login check for sign-ins that skip the login form, i.e. a "Remember me"
 * cookie quietly signing someone back in. If the account is already in use on another live
 * session, this one is signed out; otherwise this session becomes the account's session.
 */
class EnforceSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach (['staff', 'customer'] as $guard) {
            $user = Auth::guard($guard)->user();

            if (! $user) {
                continue;
            }

            $sessionId = $request->session()->getId();
            $owner = SingleSession::ownerOf($user);

            if ($owner === $sessionId) {
                break;
            }

            if (SingleSession::isAlive($owner)) {
                // Not logout(): that would reset the account's remember token and sign the
                // other device out of "Remember me" too. Just drop this session and its cookie.
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                Cookie::queue(Cookie::forget(Auth::guard($guard)->getRecallerName()));

                return redirect()->route('login')->withErrors(['login' => SingleSession::IN_USE_MESSAGE]);
            }

            SingleSession::claim($user, $sessionId);
            break;
        }

        return $next($request);
    }
}
