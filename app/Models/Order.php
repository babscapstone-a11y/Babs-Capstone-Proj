<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'total_amount', 'customer_id', 'placed_by', 'order_status_id',
        'order_type', 'payment_status', 'payment_method', 'special_instructions',
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

    public function dineInOrder(): HasOne
    {
        return $this->hasOne(DineInOrder::class);
    }

    public function placedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /* ── Scopes ── */

    /**
     * Orders the cashier is allowed to bill: kitchen has marked them Ready or
     * Completed, and no payment has been recorded for them yet.
     */
    public function scopeAwaitingPayment(Builder $q): Builder
    {
        return $q->where('payment_status', 'pending')
            ->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Ready', 'Completed']));
    }

    /* ── Order Number Generation ── */

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('order_number', $number)->exists());

        return $number;
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

    public function getCustomerStatusLabelAttribute(): string
    {
        return match ($this->status_name) {
            'Pending'    => 'Order Received',
            'Processing' => 'Preparing',
            'Ready'      => $this->order_type === 'dine_in' ? 'Ready for Serving' : 'Ready for Pickup',
            'Completed'  => 'Completed',
            'Cancelled'  => 'Cancelled',
            default      => $this->status_name,
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cashless' => 'Cashless',
            default    => 'Cash',
        };
    }

    public function getKitchenStatusLabelAttribute(): string
    {
        return match ($this->status_name) {
            'Pending'    => 'Order Received',
            'Processing' => 'Preparing',
            'Ready'      => 'Ready',
            'Completed'  => 'Completed',
            'Cancelled'  => 'Cancelled',
            default      => $this->status_name,
        };
    }

    public function getNextKitchenActionAttribute(): ?string
    {
        return match ($this->status_name) {
            'Pending'    => 'Start Preparing',
            'Processing' => 'Mark as Ready',
            'Ready'      => 'Mark as Completed',
            default      => null,
        };
    }

    public function getEstimatedCompletionAttribute(): ?\Illuminate\Support\Carbon
    {
        if ($this->isCancelled() || $this->isCompleted()) {
            return null;
        }

        return $this->created_at?->copy()->addMinutes(30);
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->details->sum('quantity');
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->customer?->full_name ?? 'Walk-in';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isAwaitingPayment(): bool
    {
        return $this->payment_status === 'pending'
            && in_array($this->status_name, ['Ready', 'Completed'], true);
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
