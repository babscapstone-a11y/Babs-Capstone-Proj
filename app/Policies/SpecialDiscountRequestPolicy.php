<?php

namespace App\Policies;

use App\Models\SpecialDiscountRequest;
use App\Models\User;

class SpecialDiscountRequestPolicy
{
    /**
     * Only Administrators may view the special discount review queue.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Administrators may open a request's detailed review page.
     */
    public function view(User $user, SpecialDiscountRequest $specialDiscountRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administrators may approve/reject a request only while it is still
     * pending — this blocks re-deciding a request that was already approved
     * or rejected, keeping historical decisions immutable.
     */
    public function decide(User $user, SpecialDiscountRequest $specialDiscountRequest): bool
    {
        return $user->isAdmin() && $specialDiscountRequest->isPending();
    }
}
