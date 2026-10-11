<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Emailed receipt for an online order.
 *   'online' — the GCash payment made at checkout was received (half or full)
 *   'final'  — the cashier collected the remaining balance at pick-up
 * Sent through App\Support\OrderMail.
 */
class OrderReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $kind,
        public readonly ?Payment $payment = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order  = $this->order->loadMissing(['details', 'paymentProof']);
        $peso   = fn ($v) => '₱' . number_format((float) $v, 2);
        $total  = (float) $order->total_amount;
        $online = $order->amountPaidOnline() ?: (float) $order->paymentProof?->amount;

        $mail = (new MailMessage)
            ->subject(($this->kind === 'final' ? 'Receipt' : 'Payment received') . ' — Order #' . $order->order_number)
            ->greeting('Hi ' . ($notifiable->first_name ?? 'there') . '!');

        if ($this->kind === 'final') {
            $mail->line('Thank you! Your order **#' . $order->order_number . '** is now fully paid. Here is your receipt.');
        } else {
            $mail->line('We received your GCash payment of **' . $peso($online) . '** for order **#' . $order->order_number . '**.');
        }

        // Items
        $rows = $order->details->map(fn ($d) => '<tr><td style="padding:6px 0">' . e($d->item_name) . ' × ' . $d->quantity . '</td>'
            . '<td style="padding:6px 0;text-align:right">' . $peso($d->subtotal) . '</td></tr>')->implode('');

        // Totals
        $summary = [['Total', $peso($total)]];
        if ($online > 0) {
            $summary[] = ['Paid via GCash' . ($order->paymentProof?->reference_number ? ' (Ref. ' . e($order->paymentProof->reference_number) . ')' : ''), $peso($online)];
        }
        if ($this->kind === 'final' && $this->payment) {
            $summary[] = ['Paid at pick-up (' . ($order->payment_method === 'cash' ? 'Cash' : 'Cashless') . ')', $peso($this->payment->amount_paid)];
            $summary[] = ['Receipt No.', e($this->payment->receipt_number)];
        } else {
            $summary[] = ['Balance due at pick-up', '<strong>' . $peso(max(0, $total - $online)) . '</strong>'];
        }
        $summaryRows = collect($summary)->map(fn ($r) => '<tr><td style="padding:4px 0;color:#6B7280">' . $r[0] . '</td>'
            . '<td style="padding:4px 0;text-align:right">' . $r[1] . '</td></tr>')->implode('');

        $mail->line(new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 4px;border-top:1px solid #E5E7EB;border-bottom:1px solid #E5E7EB">' . $rows . '</table>'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px">' . $summaryRows . '</table>'
        ));

        if ($this->kind === 'online' && $order->pickup_at) {
            $mail->line('**Pick-up:** ' . $order->pickup_at->format('D, M j, Y \a\t g:i A')
                . ($order->party_size ? ' · ' . $order->party_size . ' ' . ($order->party_size === 1 ? 'person' : 'persons') : ''));
            if ($order->approval_status === 'pending') {
                $mail->line('We will confirm your order shortly.');
            }
        }

        return $mail->action('View Your Order', route('account.orders.show', $order))
            ->line('Thank you for ordering from BAB\'S RESTO!');
    }
}
