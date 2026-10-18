<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /* Admins can view the staff list */
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    /* Admins can view any individual staff profile */
    public function view(User $currentUser, User $target): bool
    {
        return $currentUser->isAdmin();
    }

    /* Only admins can create new staff accounts */
    public function create(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }

    /* Admins can edit staff — but NOT their own account through this module */
    public function update(User $currentUser, User $target): bool
    {
        return $currentUser->isAdmin() && $currentUser->id !== $target->id;
    }

    /* Admins can toggle status — but NOT their own account, and NOT deactivate another active admin */
    public function toggleStatus(User $currentUser, User $target): Response
    {
        if (! $currentUser->isAdmin() || $currentUser->id === $target->id) {
            return Response::deny('You are not allowed to change the status of this account.');
        }

        if ($target->isAdmin() && $target->status === 'active') {
            return Response::deny('An administrator account cannot be deactivated by another administrator.');
        }

        return Response::allow();
    }

    /* Only admins can manage (approve/reject) password reset requests */
    public function managePasswordResets(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }
}
