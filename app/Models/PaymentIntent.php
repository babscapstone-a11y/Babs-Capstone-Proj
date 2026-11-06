<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcashPaymentIntent extends Model
{
    protected $fillable = [
        'order_id', 'cashier_id', 'discount_id', 'payment_id',
        'subtotal', 'discount_amount', 'service_charge', 'grand_total',
        'paymongo_payment_intent_id', 'paymongo_payment_method_id',
        'paymongo_checkout_url', 'next_action_type', 'status',
    ];

    /** How long a QR Ph code stays scannable before PayMongo expires it. */
    const QR_EXPIRY_MINUTES = 30;

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'service_charge'  => 'decimal:2',
        'grand_total'     => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * PayMongo doesn't reliably surface QR Ph's 30-minute code expiry on the
     * Payment Intent's own status, so it's tracked here off created_at
     * instead. Only meaningful while still `pending` — a redirect-based
     * method (GCash) doesn't expire the same way, but checking this against
     * an already-resolved intent is harmless (it just won't be consulted).
     */
    public function isExpired(): bool
    {
        return $this->status === 'pending'
            && $this->created_at->addMinutes(self::QR_EXPIRY_MINUTES)->isPast();
    }
}
