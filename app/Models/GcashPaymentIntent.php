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
        'paymongo_checkout_url', 'status',
    ];

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
}
