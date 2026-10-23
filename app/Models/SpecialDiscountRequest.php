<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SpecialDiscountRequest extends Model
{
    protected $fillable = [
        'request_number',
        'order_id',
        'discount_id',
        'cashier_id',
        'requested_amount',
        'reason',
        'review_status',
        'reviewed_by',
        'review_date',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'review_date'      => 'datetime',
    ];

    /* ── Relationships ── */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* ── Scopes ── */

    public function scopePending(Builder $q): Builder
    {
        return $q->where('review_status', 'pending');
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('review_status', 'approved');
    }

    public function scopeRejected(Builder $q): Builder
    {
        return $q->where('review_status', 'rejected');
    }

    /* ── Request Number Generation ── */

    public static function generateRequestNumber(): string
    {
        do {
            $number = 'SDR-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (self::where('request_number', $number)->exists());

        return $number;
    }

    /* ── Helpers ── */

    public function isPending(): bool
    {
        return $this->review_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->review_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->review_status === 'rejected';
    }

    public function getReviewStatusLabelAttribute(): string
    {
        return match ($this->review_status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => 'Pending Review',
        };
    }

    public function getReviewStatusBadgeClassAttribute(): string
    {
        return match ($this->review_status) {
            'approved' => 'badge-approved',
            'rejected' => 'badge-rejected',
            default    => 'badge-pending',
        };
    }
}
