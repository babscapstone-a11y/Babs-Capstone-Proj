<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Online-only details of an order (subtype of `orders`): the customer's pick-up
 * schedule and the cashier's payment approval. Walk-in orders keep their own
 * details in `walk_in_orders` instead.
 */
class OnlineOrder extends Model
{
    protected $fillable = [
        'order_id', 'pickup_at', 'party_size',
        'approval_status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
        'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'pickup_at'    => 'datetime',
        'party_size'   => 'integer',
        'reviewed_at'  => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /* ── Customer emails (see App\Support\OrderMail) ── */

    protected static function booted(): void
    {
        static::updated(function (OnlineOrder $online) {
            $order = $online->order->setRelation('onlineOrder', $online);

            if ($online->wasChanged('cancelled_at') && $online->cancelled_at && ! $online->getOriginal('cancelled_at')) {
                \App\Support\OrderMail::cancelled($order);
            }

            if ($online->wasChanged('approval_status')) {
                match ($online->approval_status) {
                    'rejected' => \App\Support\OrderMail::rejected($order),
                    // Covers the screenshot-upload path; a GCash payment confirmed
                    // automatically has already been emailed (sent once only)
                    'approved' => \App\Support\OrderMail::paymentReceived($order),
                    default    => null,
                };
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
