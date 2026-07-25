<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Cashiers may bill an order only once the kitchen has marked it Ready
     * or Completed, and only if it has not already been paid.
     */
    public function pay(User $user, Order $order): bool
    {
        return $user->isCashier() && $order->isAwaitingPayment();
    }

    /**
     * Cashiers may look up any order's detail as soon as it's on the floor
     * (in the kitchen queue), even before it's ready for billing — this is
     * read-only visibility, separate from the `pay` gate above. Online
     * pre-orders stay excluded until approved, mirroring the visibility
     * scope used to populate the billing search results.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isCashier() && (! $order->isOnline() || $order->approval_status === 'approved');
    }

    /**
     * Cashiers may review any online pre-order (pending, approved, rejected,
     * or cancelled) — this is the read-only history/detail view.
     */
    public function viewOnline(User $user, Order $order): bool
    {
        return $user->isCashier() && $order->isOnline();
    }

    /**
     * Cashiers may approve/reject an online pre-order only once — while it
     * still sits in the Pending Approval state. This also blocks re-deciding
     * an order that was already approved or rejected by another cashier.
     */
    public function decideOnline(User $user, Order $order): bool
    {
        return $user->isCashier() && $order->needsApproval();
    }
}
