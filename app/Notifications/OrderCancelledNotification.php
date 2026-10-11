<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the customer their online order is off.
 *   'cancelled' — cancelled (approved cancellation request, or not picked up by closing time)
 *   'rejected'  — staff rejected the order during payment verification
 * Sent through App\Support\OrderMail.
 */
class OrderCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order, public readonly string $kind = 'cancelled')
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order    = $this->order;
        $rejected = $this->kind === 'rejected';
        $reason   = $rejected ? $order->rejection_reason : $order->cancellation_reason;

        $mail = (new MailMessage)
            ->subject('Order #' . $order->order_number . ($rejected ? ' was not accepted' : ' has been cancelled'))
            ->greeting('Hi ' . ($notifiable->first_name ?? 'there') . ',')
            ->line($rejected
                ? 'We\'re sorry — we could not accept your order **#' . $order->order_number . '**.'
                : 'Your order **#' . $order->order_number . '** has been cancelled.');

        if ($reason) {
            $mail->line('**Reason:** ' . $reason);
        }

        // The unclaimed-order reason already explains the payment, so don't add a second note
        if ($reason !== Order::UNCLAIMED_REASON) {
            $mail->line('If you have any questions about your payment, please contact us.');
        }

        return $mail->action('View Your Order', route('account.orders.show', $order))
            ->line('Thank you, and we hope to serve you again soon.');
    }
}
