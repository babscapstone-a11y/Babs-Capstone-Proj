<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CancellationRequest extends Model
{
    protected $fillable = [
        'request_number',
        'order_id',
        'customer_id',
        'cancellation_reason',
        'review_status',
        'reviewed_by',
        'review_date',
        'rejection_reason',
    ];

    protected $casts = [
        'review_date' => 'datetime',
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
            $number = 'CR-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
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
