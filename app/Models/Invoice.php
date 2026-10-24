<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = [
        'order_id', 'discount_id',
        'subtotal', 'discount_amount', 'service_charge', 'final_total',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'service_charge'  => 'decimal:2',
        'final_total'     => 'decimal:2',
    ];

    /** Philippine VAT. Menu prices already include it, so it's worked out of the total, not added. */
    const VAT_RATE = 0.12;

    /** The part of the total before VAT: total ÷ 1.12 */
    public function getVatableSalesAttribute(): float
    {
        return round((float) $this->final_total / (1 + self::VAT_RATE), 2);
    }

    /** The VAT inside the total, so VATable Sales + VAT always equals the total */
    public function getVatAmountAttribute(): float
    {
        return round((float) $this->final_total - $this->vatable_sales, 2);
    }

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
