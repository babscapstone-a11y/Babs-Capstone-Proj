<?php

namespace App\Support;

use App\Jobs\SendOrderMail;
use App\Models\Order;

/**
 * Customer emails for online orders, triggered from the Order / OnlineOrder / PaymentProof
 * model events.
 *
 * - Emails are queued (App\Jobs\SendOrderMail), so staff and customer screens never wait
 *   on the mail server. The queue worker sends them: `composer run dev` locally, the
 *   scheduler (routes/console.php) on the server.
 * - A queued email is only sent once the database transaction commits, and each email is
 *   sent at most once per order.
 * - Orders without a customer email (walk-in / dine-in) are skipped.
 */
class OrderMail
{
    /** GCash payment received (or the payment screenshot was approved). */
    public static function paymentReceived(Order $order): void
    {
        self::send($order, 'payment');
    }

    /** Cashier collected the remaining balance at pick-up. Skipped when nothing was collected. */
    public static function finalReceipt(Order $order): void
    {
        self::send($order, 'final');
    }

    public static function cancelled(Order $order): void
    {
        self::send($order, 'cancelled');
    }

    public static function rejected(Order $order): void
    {
        self::send($order, 'rejected');
    }

    private static function send(Order $order, string $kind): void
    {
        if ($order->order_type !== 'online' || ! $order->customer_id) {
            return;
        }

        SendOrderMail::dispatch($order->id, $kind);
    }
}
