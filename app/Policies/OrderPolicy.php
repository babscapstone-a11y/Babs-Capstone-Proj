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
}
