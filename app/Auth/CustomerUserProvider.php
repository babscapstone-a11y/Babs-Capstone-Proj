<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Login provider for the `customer` guard. Credentials live in `users` (role "customer");
 * the signed-in model is the customer's profile (App\Models\Customer), found through its
 * user_id. A staff account can never sign in here.
 */
class CustomerUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)
            ->with('user')
            ->whereHas('user', fn ($q) => $q->customerAccounts());
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (! isset($credentials['email'])) {
            return null;
        }

        return $this->newModelQuery()->withEmail($credentials['email'])->first();
    }

    /** "Remember me" token is stored on the users row */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $account = $user->user;
        $account->setRememberToken($token);

        $timestamps = $account->timestamps;
        $account->timestamps = false;
        $account->save();
        $account->timestamps = $timestamps;
    }

    /** Upgrades an outdated password hash on the users row (Laravel does this after login) */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        if (! $this->hasher->needsRehash($user->getAuthPassword()) && ! $force) {
            return;
        }

        $user->user->forceFill(['password' => $this->hasher->make($credentials['password'])])->save();
    }
}
