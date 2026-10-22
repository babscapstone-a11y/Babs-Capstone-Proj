<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderReceiptNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sends one customer email for an online order from the queue, so the screen that
 * triggered it (cashier approve/reject, payment confirmation…) responds right away
 * instead of waiting on the mail server. See App\Support\OrderMail.
 */
class SendOrderMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Seconds to wait before retrying a failed send (e.g. mail server down) */
    public int $backoff = 60;

    public function __construct(public int $orderId, public string $kind)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $order = Order::with('customer.user')->find($this->orderId);
        if (! $order || ! $order->customer?->email) {
            return;
        }

        $notification = $this->buildNotification($order);
        $onceKey = "mail:order-{$this->kind}:{$order->id}";

        // Each email is sent at most once: the same payment can be confirmed by the
        // return page, the QR status poll and the webhook
        if (! $notification || ! Cache::add($onceKey, true, now()->addDays(60))) {
            return;
        }

        try {
            $order->customer->notify($notification);
        } catch (\Throwable $e) {
            Cache::forget($onceKey);   // let the retry try again
            Log::error('Order email failed', ['order_id' => $order->id, 'email' => $onceKey, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function buildNotification(Order $order): ?Notification
    {
        return match ($this->kind) {
            'payment'   => new OrderReceiptNotification($order, 'online'),
            'cancelled' => new OrderCancelledNotification($order, 'cancelled'),
            'rejected'  => new OrderCancelledNotification($order, 'rejected'),
            'final'     => $this->finalReceipt($order),
            default     => null,
        };
    }

    /** Skipped when nothing was collected at pick-up */
    private function finalReceipt(Order $order): ?Notification
    {
        $payment = Payment::where('order_id', $order->id)->latest('id')->first();

        return $payment && (float) $payment->amount_paid > 0
            ? new OrderReceiptNotification($order, 'final', $payment)
            : null;
    }
}
