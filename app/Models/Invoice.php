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

    /**
     * VAT taken off a Senior Citizen / PWD sale (see Discount::reductionsFor). Not stored: it's
     * what the subtotal, discount and service charge don't account for in the final total.
     */
    public function getVatExemptionAttribute(): float
    {
        return max(0.0, round((float) $this->subtotal - (float) $this->discount_amount
            + (float) $this->service_charge - (float) $this->final_total, 2));
    }

    public function isVatExempt(): bool
    {
        return $this->vat_exemption > 0;
    }

    /** A VAT-exempt (Senior Citizen / PWD) sale: the whole total, with no VAT in it */
    public function getVatExemptSalesAttribute(): float
    {
        return $this->isVatExempt() ? round((float) $this->final_total, 2) : 0.0;
    }

    /**
     * $numerator ÷ $denominator rounded to the nearest whole number, halves up — integer-only,
     * so peso amounts worked out in centavos round the same everywhere (no float drift).
     */
    public static function divideHalfUp(int $numerator, int $denominator): int
    {
        return intdiv(2 * $numerator + $denominator, 2 * $denominator);
    }

    /** The part of the total before VAT: total ÷ 1.12 (none on a VAT-exempt sale) */
    public function getVatableSalesAttribute(): float
    {
        if ($this->isVatExempt()) {
            return 0.0;
        }

        return self::divideHalfUp((int) round((float) $this->final_total * 100) * 100, (int) round(100 * (1 + self::VAT_RATE))) / 100;
    }

    /** The VAT inside the total, so VATable Sales + VAT always equals the total */
    public function getVatAmountAttribute(): float
    {
        return $this->isVatExempt() ? 0.0 : round((float) $this->final_total - $this->vatable_sales, 2);
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
