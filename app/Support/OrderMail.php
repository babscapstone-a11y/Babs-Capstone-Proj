<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderReceiptNotification;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Customer emails for online orders, triggered from the Order / PaymentProof model events.
 *
 * - Each email is sent at most once (a cache key per order/payment), since the same
 *   payment can be confirmed by the return page, the QR status poll and the webhook.
 * - Sending waits until the database transaction commits, then runs after the web
 *   response is sent, so staff screens don't wait on the mail server. A failed send
 *   is logged and never breaks the action that triggered it.
 * - Orders without a customer email (walk-in / dine-in) are skipped.
 */
class OrderMail
{
    /** GCash payment received (or the payment screenshot was approved). */
    public static function paymentReceived(Order $order): void
    {
        self::send($order, 'mail:order-payment:' . $order->id,
            fn (Order $o) => new OrderReceiptNotification($o, 'online'));
    }

    /** Cashier collected the remaining balance at pick-up. Skipped when nothing was collected. */
    public static function finalReceipt(Order $order): void
    {
        self::send($order, 'mail:order-final:' . $order->id, function (Order $o) {
            $payment = Payment::where('order_id', $o->id)->latest('id')->first();

            return $payment && (float) $payment->amount_paid > 0
                ? new OrderReceiptNotification($o, 'final', $payment)
                : null;
        });
    }

    public static function cancelled(Order $order): void
    {
        self::send($order, 'mail:order-cancelled:' . $order->id,
            fn (Order $o) => new OrderCancelledNotification($o, 'cancelled'));
    }

    public static function rejected(Order $order): void
    {
        self::send($order, 'mail:order-rejected:' . $order->id,
            fn (Order $o) => new OrderCancelledNotification($o, 'rejected'));
    }

    /** @param Closure(Order): (\Illuminate\Notifications\Notification|null) $build */
    private static function send(Order $order, string $onceKey, Closure $build): void
    {
        if ($order->order_type !== 'online' || ! $order->customer_id) {
            return;
        }

        $orderId = $order->id;
        $deliver = function () use ($orderId, $onceKey, $build) {
            $claimed = false;
            try {
                $order = Order::with('customer')->find($orderId);
                if (! $order || ! $order->customer?->email) {
                    return;
                }
                $notification = $build($order);
                if ($notification && ($claimed = Cache::add($onceKey, true, now()->addDays(60)))) {
                    $order->customer->notify($notification);
                }
            } catch (\Throwable $e) {
                if ($claimed) {
                    Cache::forget($onceKey);   // let a later trigger try again
                }
                Log::error('Order email failed', ['order_id' => $orderId, 'email' => $onceKey, 'error' => $e->getMessage()]);
            }
        };

        DB::afterCommit(function () use ($deliver) {
            app()->runningInConsole() ? $deliver() : app()->terminating($deliver);
        });
    }
}
