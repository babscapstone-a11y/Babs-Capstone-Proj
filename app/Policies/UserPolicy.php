<?php

namespace App\Policies;

use App\Models\User;

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

    /* Admins can toggle status — but NOT their own account */
    public function toggleStatus(User $currentUser, User $target): bool
    {
        return $currentUser->isAdmin() && $currentUser->id !== $target->id;
    }

    /* Admins can request a password reset — but NOT for themselves */
    public function resetPassword(User $currentUser, User $target): bool
    {
        return $currentUser->isAdmin() && $currentUser->id !== $target->id;
    }

    /* Only admins can manage (approve/reject) password reset requests */
    public function managePasswordResets(User $currentUser): bool
    {
        return $currentUser->isAdmin();
    }
}
