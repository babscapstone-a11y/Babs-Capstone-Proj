<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentProof extends Model
{
    protected $fillable = [
        'order_id', 'customer_id', 'amount', 'payment_type', 'payment_method',
        'reference_number', 'proof_image', 'paid_at',
        'paymongo_payment_intent_id', 'paymongo_payment_method_id',
        'paymongo_checkout_url', 'status',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    const METHODS = [
        'gcash'         => 'GCash',
        'maya'          => 'Maya',
        'bank_transfer' => 'Bank Transfer',
        'other'         => 'Other Electronic Payment',
    ];

    const PAYMENT_TYPES = [
        'half' => 'Half Payment (50%)',
        'full' => 'Full Payment',
    ];

    const STATUSES = [
        'awaiting_payment' => 'Awaiting Payment',
        'paid'              => 'Paid',
        'failed'            => 'Failed',
    ];

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /* ── Computed Attributes ── */

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return self::PAYMENT_TYPES[$this->payment_type] ?? ucfirst((string) $this->payment_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getProofImageUrlAttribute(): ?string
    {
        return $this->proof_image ? Storage::url($this->proof_image) : null;
    }
}
