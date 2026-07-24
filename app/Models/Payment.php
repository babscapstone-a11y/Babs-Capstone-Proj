<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'order_id', 'cashier_id', 'mode_of_payment_id',
        'amount_paid', 'amount_received', 'change_amount',
        'reference_number', 'transaction_number', 'receipt_number', 'payment_date',
    ];

    protected $casts = [
        'amount_paid'     => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'payment_date'    => 'datetime',
    ];

    /* ── Relationships ── */

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function modeOfPayment(): BelongsTo
    {
        return $this->belongsTo(ModeOfPayment::class);
    }

    /* ── Number Generation ── */

    public static function generateTransactionNumber(): string
    {
        do {
            $number = 'TXN-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (self::where('transaction_number', $number)->exists());

        return $number;
    }

    public static function generateReceiptNumber(): string
    {
        do {
            $number = 'RCT-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (self::where('receipt_number', $number)->exists());

        return $number;
    }
}
