<?php

namespace App\Policies;

use App\Models\CancellationRequest;
use App\Models\User;

class CancellationRequestPolicy
{
    /**
     * Only Administrators may view the cancellation review queue (REQ047).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Administrators may open a request's detailed review page (REQ048).
     */
    public function view(User $user, CancellationRequest $cancellationRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administrators may approve/reject a request only while it is still
     * pending — this blocks re-deciding a request that was already
     * approved or rejected, keeping historical decisions immutable.
     */
    public function decide(User $user, CancellationRequest $cancellationRequest): bool
    {
        return $user->isAdmin() && $cancellationRequest->isPending();
    }
}
