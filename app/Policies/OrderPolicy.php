<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Cashiers may view/bill an order only once the kitchen has marked it
     * Ready or Completed, and only if it has not already been paid.
     */
    public function pay(User $user, Order $order): bool
    {
        return $user->isCashier() && $order->isAwaitingPayment();
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
