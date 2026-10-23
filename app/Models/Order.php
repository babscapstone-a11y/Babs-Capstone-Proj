<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'total_amount', 'customer_id', 'order_status_id',
        'order_type', 'payment_status', 'payment_method', 'special_instructions',
        'extra_prep_minutes',
        'ready_at', 'handed_off_by', 'handed_off_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'ready_at'      => 'datetime',
        'handed_off_at' => 'datetime',
    ];

    /** Percentage of the order total the "pay half now" GCash checkout option charges. */
    const HALF_PAYMENT_PERCENT = 50;

    /** Orders below this total (₱) must be paid in full — "pay half now" isn't offered. */
    const HALF_PAYMENT_MIN_TOTAL = 500;

    /** Advance orders can be scheduled from today up to this many days ahead. */
    const ADVANCE_MAX_DAYS = 7;

    /** Largest "No. of Persons" an Advance Order can be booked for. */
    const PARTY_SIZE_MAX = 50;

    /** Restaurant operating hours (24h), used to bound customer-scheduled pickup times. */
    const OPEN_HOUR  = 11; // 11:00 AM
    const CLOSE_HOUR = 21; // 9:00 PM

    /* ── Customer emails (see App\Support\OrderMail) ── */

    protected static function booted(): void
    {
        static::updated(function (Order $order) {
            // Cancellation, approval and rejection emails are sent from OnlineOrder, which holds those fields

            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                \App\Support\OrderMail::finalReceipt($order);
            }
        });
    }

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

    /** Walk-in details (dine-in / take-out, table, who took it); none for online orders */
    public function walkInOrder(): HasOne
    {
        return $this->hasOne(WalkInOrder::class);
    }

    /** The food server who took a walk-in order (stored on walk_in_orders) */
    public function placedBy(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, WalkInOrder::class, 'order_id', 'id', 'id', 'placed_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function paymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }

    /** The food server who handed the order over: served it at the table or packaged it for pick-up */
    public function handedOffBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_off_by');
    }

    public function specialDiscountRequests(): HasMany
    {
        return $this->hasMany(SpecialDiscountRequest::class);
    }

    /* ── Scopes ── */

    /**
     * Orders the cashier is allowed to bill: the kitchen/food-server has
     * moved them to Ready, Served, or Packaged, and no payment has been
     * recorded for them yet. "Completed" is kept in the list for any
     * pre-existing order that still carries that legacy status; payment no
     * longer sets it (see finalizeOrderPayment()) since a customer may pay
     * before or after being served — payment_status alone tracks that.
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
     * Orders of one service type as the screens and filters know it: "dine_in" / "takeout"
     * (walk-in orders, from walk_in_orders) or "online".
     */
    public function scopeOfServiceType(Builder $q, string $type): Builder
    {
        return $type === 'online'
            ? $q->where('order_type', 'online')
            : $q->where('order_type', 'walk_in')->whereHas('walkInOrder', fn ($w) => $w->where('service_type', $type));
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
    /**
     * Online orders waiting on a cashier: pending and with a payment screenshot submitted.
     * An order whose customer only opened the QR code (no proof yet) isn't shown to cashiers;
     * a QR payment PayMongo confirms is approved automatically and skips this queue.
     */
    public function scopeAwaitingCashierReview(Builder $q): Builder
    {
        return $q->withApprovalStatus('pending')
            ->notCancelled()
            ->whereHas('paymentProof', fn ($pq) => $pq->whereNotNull('proof_image'));
    }

    /** Online orders that haven't been cancelled (cancelled_at lives in online_orders) */
    public function scopeNotCancelled(Builder $q): Builder
    {
        return $q->whereHas('onlineOrder', fn ($oq) => $oq->whereNull('cancelled_at'));
    }

    /** Online orders whose approval (stored in online_orders) is the given status */
    public function scopeWithApprovalStatus(Builder $q, string $status): Builder
    {
        return $q->whereHas('onlineOrder', fn ($oq) => $oq->where('approval_status', $status));
    }

    /**
     * Orders that have reached the floor: every dine-in / take-out order, but
     * online orders only once a cashier (or a confirmed QR payment) approved them.
     */
    public function scopeReleasedToKitchen(Builder $q): Builder
    {
        return $q->where(function ($q) {
            $q->where('order_type', '!=', 'online')
              ->orWhereHas('onlineOrder', fn ($oq) => $oq->where('approval_status', 'approved'));
        });
    }

    public function scopeVisibleForBilling(Builder $q): Builder
    {
        return $q->where('payment_status', 'pending')
            ->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Pending', 'Processing', 'Ready', 'Served', 'Packaged', 'Completed']))
            ->releasedToKitchen();
    }

    /**
     * Orders the food-server fulfillment board surfaces: Ready-and-not-yet-
     * handed-off (needs action — see canBeFulfilled()) plus today's
     * Served/Packaged (recently handled, kept visible for the summary
     * counts and quick lookup). Mirrors the Kitchen board's own "Served
     * today" pattern.
     */
    public function scopeVisibleForFulfillment(Builder $q): Builder
    {
        return $q->where(function ($query) {
            $query->where(function ($sub) {
                $sub->whereHas('orderStatus', fn ($sq) => $sq->where('status_name', 'Ready'))
                    ->whereNull('handed_off_at');
            })
            ->orWhere(function ($sub) {
                $sub->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Served', 'Packaged']))
                    ->where(function ($dateQ) {
                        $dateQ->whereDate('handed_off_at', today());
                    });
            });
        })
        ->releasedToKitchen();
    }

    /* ── Order Number Generation ── */

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /* ── Unclaimed pick-ups ── */

    /** Reason saved on orders cancelled by cancelUnclaimedPickups(); shown to the customer. */
    const UNCLAIMED_REASON = 'Not picked up by closing time (9:00 PM) on the scheduled pick-up day. The payment is forfeited because the order was already prepared.';

    /**
     * Cancels online orders that were prepared (Ready / Packaged) but not picked up by
     * closing time (CLOSE_HOUR) on their pick-up day. The payment is left as it is
     * (forfeited — the food was made). Orders the kitchen never prepared are not touched.
     * Runs every few minutes from the scheduler (routes/console.php) and, throttled,
     * from the kitchen and cashier screens. Returns how many orders were cancelled.
     */
    public static function cancelUnclaimedPickups(): int
    {
        // Pick-up days whose closing time has passed: every day before today,
        // plus today once it's 9:00 PM or later.
        $cutoff = now()->hour >= self::CLOSE_HOUR ? now()->addDay()->startOfDay() : now()->startOfDay();
        $cancelledStatusId = OrderStatus::where('status_name', 'Cancelled')->value('id');

        if (! $cancelledStatusId) {
            return 0;
        }

        $orders = self::where('order_type', 'online')
            ->whereHas('onlineOrder', fn (Builder $q) => $q->whereNull('cancelled_at')
                ->whereNotNull('pickup_at')->where('pickup_at', '<', $cutoff))
            ->whereHas('orderStatus', fn (Builder $q) => $q->whereIn('status_name', ['Ready', 'Packaged']))
            ->with('onlineOrder')
            ->get();

        // One by one (not a bulk update) so each order's "cancelled" email goes out
        // (sent once the transaction commits, so it sees both changes)
        foreach ($orders as $order) {
            DB::transaction(function () use ($order, $cancelledStatusId) {
                $order->update(['order_status_id' => $cancelledStatusId]);
                $order->onlineOrder->update([
                    'cancelled_at'        => now(),
                    'cancellation_reason' => self::UNCLAIMED_REASON,
                ]);
            });
        }

        return $orders->count();
    }

    /** cancelUnclaimedPickups(), at most once a minute — for screens that poll often. */
    public static function cancelUnclaimedPickupsThrottled(): void
    {
        if (\Illuminate\Support\Facades\Cache::add('orders:unclaimed-check', true, 60)) {
            self::cancelUnclaimedPickups();
        }
    }

    /* ── Online-order fields (stored in online_orders, read here as if on the order) ── */

    public function getPickupAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->onlineOrder?->pickup_at;
    }

    public function getPartySizeAttribute(): ?int
    {
        return $this->onlineOrder?->party_size;
    }

    public function getApprovalStatusAttribute(): ?string
    {
        return $this->onlineOrder?->approval_status;
    }

    public function getReviewedByAttribute(): ?int
    {
        return $this->onlineOrder?->reviewed_by;
    }

    public function getReviewedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->onlineOrder?->reviewed_at;
    }

    public function getRejectionReasonAttribute(): ?string
    {
        return $this->onlineOrder?->rejection_reason;
    }

    public function getCancelledAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->onlineOrder?->cancelled_at;
    }

    public function getCancellationReasonAttribute(): ?string
    {
        return $this->onlineOrder?->cancellation_reason;
    }

    /* ── Computed Attributes ── */

    /**
     * "dine_in" / "takeout" for a walk-in order (from walk_in_orders), "online" for an online one.
     * order_type itself only says the channel (walk_in / online).
     */
    public function getServiceTypeAttribute(): ?string
    {
        return $this->order_type === 'online' ? 'online' : $this->walkInOrder?->service_type;
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return match($this->service_type) {
            'dine_in' => 'Dine-In',
            'takeout' => 'Take-Out',
            'online'  => 'Take-Out (Online)',
            default   => 'Walk-In',
        };
    }

    public function getOrderTypeIconAttribute(): string
    {
        return match($this->service_type) {
            'dine_in' => 'fa-utensils',
            'takeout' => 'fa-bag-shopping',
            'online'  => 'fa-mobile-screen-button',
            default   => 'fa-receipt',
        };
    }

    /**
     * True once a cancelled order's payment status should read "Cancelled"
     * instead of its literal column value: the down-payment was never
     * completed (still pending/failed) and now never will be. Once money
     * has actually changed hands ('paid'/'refunded'), that fact stays
     * displayed as-is — cancellation doesn't erase it, only a real refund
     * moves it to 'refunded'.
     */
    public function getPaymentCancelledForDisplayAttribute(): bool
    {
        return $this->isCancelled() && in_array($this->payment_status, ['pending', 'failed'], true);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->payment_cancelled_for_display) {
            return 'Cancelled';
        }

        return match($this->payment_status) {
            'paid'     => 'Paid',
            'failed'   => 'Failed',
            'refunded' => 'Refunded',
            default    => 'Pending',
        };
    }

    public function getPaymentStatusClassAttribute(): string
    {
        if ($this->payment_cancelled_for_display) {
            return 'badge-cancelled';
        }

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

    /** Colour for customer_status_label: green once the order is completed, otherwise the status colour */
    public function getCustomerStatusColorAttribute(): string
    {
        return ! $this->isCancelled() && $this->isFullyClosed() ? '#16A34A' : $this->status_color;
    }

    public function getCustomerStatusLabelAttribute(): string
    {
        // Cancellation always wins: approval_status stays whatever it was
        // when the cancellation was approved (it's a separate workflow), so
        // without this check a cancelled order would keep showing its old
        // pre-cancellation approval state (e.g. "Awaiting Payment Verification").
        if ($this->isCancelled()) {
            return 'Cancelled';
        }

        // Paid and handed off (served / packaged): nothing left to happen, same as the
        // timeline's final "Completed" step, even though the raw status stays Served/Packaged
        if ($this->isFullyClosed()) {
            return 'Completed';
        }

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
            'Ready'      => $this->service_type === 'dine_in' ? 'Ready for Serving' : 'Ready for Pickup',
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

    /**
     * The kitchen's action chain stops at "Ready" — once there, the order is
     * handed off to the food server (Module 20's serve()/package()), so
     * there is no further kitchen-side action to offer.
     */
    public function getNextKitchenActionAttribute(): ?string
    {
        return match ($this->status_name) {
            'Pending'    => 'Start Preparing',
            'Processing' => 'Mark as Ready',
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

        // If the kitchen was on downtime when this order was placed, prep
        // can't start until service resumes — the estimate runs from
        // whenever that downtime ends rather than from created_at.
        $start = $this->downtime_at_placement?->ends_at?->copy() ?? $this->created_at?->copy();

        return $start?->addMinutes($this->estimated_prep_minutes);
    }

    /**
     * The restaurant downtime window (if any) that was active the moment
     * this order was placed.
     */
    public function getDowntimeAtPlacementAttribute(): ?RestaurantDowntime
    {
        if (! $this->created_at) {
            return null;
        }

        return RestaurantDowntime::coveringMoment($this->created_at)->first();
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

    /**
     * Amount already settled online via GCash QR Ph before the customer
     * reaches the counter (e.g. a "half payment" down payment at
     * checkout) — the cashier's billing screen deducts this from what's
     * still owed instead of re-charging the full total.
     *
     * An approved online order counts as paid too: approval is the cashier
     * verifying the down-payment, including one sent through the backup
     * screenshot upload that GCash never confirmed automatically.
     */
    public function amountPaidOnline(): float
    {
        if (! $this->relationLoaded('paymentProof')) {
            $this->load('paymentProof');
        }

        $verified = $this->paymentProof
            && ($this->paymentProof->status === 'paid' || ($this->isOnline() && $this->approval_status === 'approved'));

        return $verified ? (float) $this->paymentProof->amount : 0.0;
    }

    /** Paid in full online (nothing left to collect at the counter) */
    public function isFullyPaidOnline(): bool
    {
        if (! $this->relationLoaded('details')) {
            $this->load('details');
        }

        $paidOnline = $this->amountPaidOnline();

        return $paidOnline > 0 && $paidOnline + 0.004 >= (float) $this->details->sum('subtotal');
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
     * True once there's genuinely nothing left to happen — cancelled, or
     * paid AND handed off to the customer. Payment alone isn't enough: a
     * customer may pay before being served (finalizeOrderPayment() never
     * touches order_status_id, so paying early doesn't block or fast-forward
     * the service pipeline), and customer-facing polling/UI needs to keep
     * watching that order until it's actually been served/packaged too —
     * otherwise a pay-first order would stop updating before the food ever
     * arrives.
     */
    public function isFullyClosed(): bool
    {
        return $this->isCancelled() || ($this->payment_status === 'paid' && $this->handed_off_at !== null);
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
        return $this->isOnline() && $this->approval_status === 'pending' && $this->cancelled_at === null;
    }

    /**
     * A QR payment PayMongo confirmed is proof enough: send the order straight to the kitchen
     * without waiting for a cashier (reviewed_by stays empty = approved automatically).
     */
    public function approveForConfirmedPayment(): void
    {
        DB::transaction(function () {
            $locked = self::whereKey($this->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->needsApproval()) {
                return;
            }

            $locked->onlineOrder->update([
                'approval_status' => 'approved',
                'reviewed_by'     => null,
                'reviewed_at'     => now(),
            ]);
        });
    }

    /**
     * The customer uploaded a payment screenshot + reference number (both required on upload),
     * so a cashier can verify it and approve the order manually.
     */
    public function hasSubmittedPaymentProof(): bool
    {
        return (bool) $this->paymentProof?->proof_image;
    }

    /** Approved without a cashier, because PayMongo confirmed the QR payment */
    public function wasAutoApproved(): bool
    {
        return $this->approval_status === 'approved' && $this->reviewed_by === null && $this->reviewed_at !== null;
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
        return $this->service_type !== 'dine_in';
    }

    /**
     * The food server can act on this order once the kitchen has marked it
     * "Ready" — that's the intended hand-off signal. handed_off_at
     * (only ever set by serve()/package()) confirm it hasn't already
     * been handed off. Deliberately independent of payment_status: a
     * customer may pay before or after being served, and
     * CashierController::finalizeOrderPayment() never touches
     * order_status_id, so paying early can't block this from also happening.
     */
    public function canBeFulfilled(): bool
    {
        return $this->status_name === 'Ready' && $this->handed_off_at === null;
    }

    public function getFulfillmentActionLabelAttribute(): string
    {
        return $this->usesPackagingFlow() ? 'Package Order' : 'Serve Order';
    }

    /**
     * How this order's status should read to a food server — effectively
     * just the raw status_name, since canBeFulfilled() already keys off
     * "Ready" directly. Kept as its own accessor so callers don't need to
     * know that detail.
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
