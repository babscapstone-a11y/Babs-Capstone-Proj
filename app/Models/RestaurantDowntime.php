<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A window during which the restaurant cannot cater orders (e.g. a staff
 * shortage or equipment outage). While one is active, customers are blocked
 * from adding items to their cart or checking out — see the guards in
 * CartController::add() and CheckoutController.
 */
class RestaurantDowntime extends Model
{
    protected $fillable = [
        'starts_at', 'ends_at', 'reason',
        'set_by_id', 'ended_early_at', 'ended_by_id',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'ended_early_at' => 'datetime',
    ];

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_id');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ends_at', '>', now());
    }

    /** The downtime window (if any) that was in effect at a given moment. */
    public function scopeCoveringMoment(Builder $query, \DateTimeInterface $moment): Builder
    {
        return $query->where('starts_at', '<=', $moment)->where('ends_at', '>', $moment);
    }

    public static function current(): ?self
    {
        return static::active()->orderByDesc('starts_at')->first();
    }

    public static function isRestaurantDown(): bool
    {
        return static::current() !== null;
    }

    /** Shared copy for the error customers see when ordering is blocked. */
    public static function blockedOrderingMessage(): string
    {
        return 'The restaurant is temporarily unavailable and not accepting new orders right now. Please check back later.';
    }

    public function wasEndedEarly(): bool
    {
        return $this->ended_early_at !== null;
    }
}
