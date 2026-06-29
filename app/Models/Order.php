<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'total_amount', 'customer_id', 'order_status_id',
        'order_type', 'payment_status', 'special_instructions',
        'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    /* ── Relationships ── */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function onlineOrder(): HasOne
    {
        return $this->hasOne(OnlineOrder::class);
    }

    /* ── Computed Attributes ── */

    public function getOrderTypeLabelAttribute(): string
    {
        return match($this->order_type) {
            'dine_in' => 'Dine-In',
            'takeout' => 'Take-Out',
            'online'  => 'Delivery',
            default   => ucfirst($this->order_type ?? 'Unknown'),
        };
    }

    public function getOrderTypeIconAttribute(): string
    {
        return match($this->order_type) {
            'dine_in' => 'fa-utensils',
            'takeout' => 'fa-bag-shopping',
            'online'  => 'fa-motorcycle',
            default   => 'fa-receipt',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid'     => 'Paid',
            'failed'   => 'Failed',
            'refunded' => 'Refunded',
            default    => 'Pending',
        };
    }

    public function getPaymentStatusClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid'     => 'badge-paid',
            'failed'   => 'badge-failed',
            'refunded' => 'badge-refunded',
            default    => 'badge-pending',
        };
    }

    public function getStatusNameAttribute(): string
    {
        return $this->orderStatus?->status_name ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->orderStatus?->color ?? '#6B7280';
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->details->sum('quantity');
    }

    public function isCancelled(): bool
    {
        return strtolower($this->status_name) === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return strtolower($this->status_name) === 'completed';
    }

    public function isDelivery(): bool
    {
        return $this->order_type === 'online';
    }
}
