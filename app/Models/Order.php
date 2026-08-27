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
        'extra_prep_minutes',
        'cancelled_at', 'cancellation_reason',
        'pickup_at', 'approval_status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
        'ready_at', 'served_by', 'served_at', 'packaged_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'pickup_at'    => 'datetime',
        'reviewed_at'  => 'datetime',
        'ready_at'     => 'datetime',
        'served_at'    => 'datetime',
        'packaged_at'  => 'datetime',
    ];

    /** Percentage of the order total the "pay half now" GCash checkout option charges. */
    const HALF_PAYMENT_PERCENT = 50;

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

    public function paymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function cancellationRequest(): HasOne
    {
        return $this->hasOne(CancellationRequest::class);
    }

    /* ── Scopes ── */

    /**
     * Orders the cashier is allowed to bill: kitchen/food-server has moved
     * them to Ready, Served, or Packaged (or a kitchen self-completed them
     * without going through the food-server handoff), and no payment has
     * been recorded for them yet.
     */
    public function scopeAwaitingPayment(Builder $q): Builder
    {
        return $q->where('payment_status', 'pending')
            ->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Ready', 'Served', 'Packaged', 'Completed']));
    }

    /**
     * Online pre-orders — the ones that go through the cashier approval
     * checkpoint before the Kitchen Display System ever sees them.
     */
    public function scopeOnlineOrders(Builder $q): Builder
    {
        return $q->where('order_type', 'online');
    }

    /**
     * Orders the cashier billing screen's search should surface: anything
     * unpaid that has reached the kitchen queue, from the moment it's
     * placed (not just once it's Ready) — visibility is separate from
     * being allowed to actually take payment (see isAwaitingPayment()).
     * Online pre-orders stay excluded until approved, same gate the
     * Kitchen Display System uses, since before that they're still sitting
     * in the separate Online Orders approval queue, not on the floor.
     */
    public function scopeVisibleForBilling(Builder $q): Builder
    {
        return $q->where('payment_status', 'pending')
            ->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Pending', 'Processing', 'Ready', 'Served', 'Packaged', 'Completed']))
            ->where(function ($q) {
                $q->where('order_type', '!=', 'online')->orWhere('approval_status', 'approved');
            });
    }

    /**
     * Orders the food-server fulfillment board surfaces: Completed-by-the-
     * kitchen-but-not-yet-handed-off (needs action — see canBeFulfilled())
     * plus today's Served/Packaged (recently handled, kept visible for the
     * summary counts and quick lookup). Mirrors the Kitchen board's own
     * "Completed today" pattern.
     *
     * "Completed" is intentionally the trigger here, not "Ready" — the
     * kitchen uses Ready as an internal in-progress step and only notifies
     * the food server once they hit their own "Mark as Completed". That
     * same status_name gets reused again later once the cashier takes
     * payment, so served_at/packaged_at (set the moment a food server acts)
     * are what actually distinguish "kitchen just finished, needs serving"
     * from "already served/packaged and since paid" — both sit at
     * status_name = Completed, but only the first should show up here.
     */
    public function scopeVisibleForFulfillment(Builder $q): Builder
    {
        return $q->where(function ($query) {
            $query->where(function ($sub) {
                $sub->whereHas('orderStatus', fn ($sq) => $sq->where('status_name', 'Completed'))
                    ->whereNull('served_at')
                    ->whereNull('packaged_at');
            })
            ->orWhere(function ($sub) {
                $sub->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Served', 'Packaged']))
                    ->where(function ($dateQ) {
                        $dateQ->whereDate('served_at', today())->orWhereDate('packaged_at', today());
                    });
            });
        })
        ->where(function ($query) {
            $query->where('order_type', '!=', 'online')->orWhere('approval_status', 'approved');
        });
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
            'online'  => 'Take-Out (Online)',
            default   => ucfirst($this->order_type ?? 'Unknown'),
        };
    }

    public function getOrderTypeIconAttribute(): string
    {
        return match($this->order_type) {
            'dine_in' => 'fa-utensils',
            'takeout' => 'fa-bag-shopping',
            'online'  => 'fa-mobile-screen-button',
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
        if ($this->isOnline() && $this->approval_status && $this->approval_status !== 'approved') {
            return match ($this->approval_status) {
                'pending'   => 'Awaiting Payment Verification',
                'rejected'  => 'Order Rejected',
                'cancelled' => 'Cancelled',
                default     => $this->status_name,
            };
        }

        return match ($this->status_name) {
            'Pending'    => 'Order Received',
            'Processing' => 'Preparing',
            'Ready'      => $this->order_type === 'dine_in' ? 'Ready for Serving' : 'Ready for Pickup',
            'Served'     => 'Served',
            'Packaged'   => 'Packaged — Ready for Pickup',
            'Completed'  => 'Completed',
            'Cancelled'  => 'Cancelled',
            default      => $this->status_name,
        };
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending'   => 'Pending Approval',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => 'N/A',
        };
    }

    public function getApprovalStatusBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'pending'   => 'badge-pending',
            'approved'  => 'badge-approved',
            'rejected'  => 'badge-rejected',
            'cancelled' => 'badge-cancelled',
            default     => 'badge-pending',
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
            'Served'     => 'Served',
            'Packaged'   => 'Packaged',
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

    /**
     * Default prep estimate for orders containing items with no configured
     * prep time (or none at all).
     */
    private const DEFAULT_PREP_MINUTES = 30;

    /**
     * Total prep estimate in minutes: the slowest item's configured prep
     * time (items are prepared in parallel, not summed), plus any minutes
     * the kitchen has tacked on via "Extend Prep Time".
     */
    public function getEstimatedPrepMinutesAttribute(): int
    {
        $basePrepMinutes = $this->details
            ->pluck('menuItem.prep_time_minutes')
            ->filter()
            ->max() ?? self::DEFAULT_PREP_MINUTES;

        return $basePrepMinutes + $this->extra_prep_minutes;
    }

    public function getEstimatedCompletionAttribute(): ?\Illuminate\Support\Carbon
    {
        if ($this->isCancelled() || $this->isCompleted()) {
            return null;
        }

        return $this->created_at?->copy()->addMinutes($this->estimated_prep_minutes);
    }

    /**
     * The kitchen may only push back an order's estimate while it's still
     * being worked on — once it's Ready there's nothing left to extend.
     */
    public function canExtendPrepTime(): bool
    {
        return in_array($this->status_name, ['Pending', 'Processing'], true);
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
            && in_array($this->status_name, ['Ready', 'Served', 'Packaged', 'Completed'], true);
    }

    public function isCancelled(): bool
    {
        return strtolower($this->status_name) === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return strtolower($this->status_name) === 'completed';
    }

    /**
     * True once there's genuinely nothing left to happen — paid or
     * cancelled. Deliberately distinct from isCompleted(): the kitchen
     * marks an order "Completed" the moment they finish prep, to notify
     * the food server, well before it's been served/packaged or paid — so
     * isCompleted() alone goes true too early to mean "stop watching this
     * order" for customer-facing polling/UI.
     */
    public function isFullyClosed(): bool
    {
        return $this->isCancelled() || $this->payment_status === 'paid';
    }

    public function isDelivery(): bool
    {
        return $this->order_type === 'online';
    }

    public function isOnline(): bool
    {
        return $this->order_type === 'online';
    }

    /**
     * True while this online pre-order still needs a cashier's approval
     * decision — the checkpoint that keeps it out of the KDS until cleared.
     */
    public function needsApproval(): bool
    {
        return $this->isOnline() && $this->approval_status === 'pending';
    }

    /**
     * Cancellation Policy: only an order still sitting in the kitchen's
     * intake queue (raw status "Pending" — shown to staff as "Order
     * Received") may be cancelled. Once the kitchen has started preparing
     * it (Processing/Ready/Completed), a cancellation request must be
     * rejected. An already-cancelled order is closed to further requests.
     */
    public function isCancellationEligible(): bool
    {
        return $this->status_name === 'Pending';
    }

    /* ── Service Fulfillment (Module 20) ── */

    public function isServed(): bool
    {
        return $this->status_name === 'Served';
    }

    public function isPackaged(): bool
    {
        return $this->status_name === 'Packaged';
    }

    /**
     * Dine-in orders get handed to the customer at the table ("Serve");
     * everything else (take-out, online pickup) gets boxed at the counter
     * ("Package") — there's no table to serve at.
     */
    public function usesPackagingFlow(): bool
    {
        return $this->order_type !== 'dine_in';
    }

    /**
     * The food server can act on this order once the kitchen has clicked
     * their own "Mark as Completed" — that's the intended hand-off signal,
     * not "Ready" (which the kitchen treats as an internal in-progress
     * step). Since status_name = Completed gets reused again later once
     * the cashier takes payment, served_at/packaged_at (only ever set by
     * serve()/package() below) are what confirm this is the *first* time
     * this order has hit Completed, not the final post-payment one.
     */
    public function canBeFulfilled(): bool
    {
        return $this->status_name === 'Completed' && $this->served_at === null && $this->packaged_at === null;
    }

    public function getFulfillmentActionLabelAttribute(): string
    {
        return $this->usesPackagingFlow() ? 'Package Order' : 'Serve Order';
    }

    /**
     * How this order's status should read to a food server: the kitchen's
     * internal "Completed" (pre-handoff) shows as "Ready" here, since
     * that's what it actually means from the food server's side —
     * "Completed" would misleadingly suggest the order is already fully
     * done. Served/Packaged pass through unchanged.
     */
    public function getFulfillmentStatusAttribute(): string
    {
        return $this->canBeFulfilled() ? 'Ready' : $this->status_name;
    }

    public function getFulfillmentStatusLabelAttribute(): string
    {
        return $this->canBeFulfilled() ? 'Ready' : $this->kitchen_status_label;
    }

    public function getFulfillmentStatusColorAttribute(): string
    {
        return $this->canBeFulfilled() ? '#16A34A' : $this->status_color;
    }
}
