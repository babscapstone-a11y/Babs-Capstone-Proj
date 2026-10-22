<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

/**
 * Login provider for the `staff` guard: only `users` rows with a staff role (anything but
 * "customer") can sign in to the staff side, even with a correct password.
 */
class StaffUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->staffAccounts();
    }
}
