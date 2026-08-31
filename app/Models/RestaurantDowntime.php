<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A window during which the restaurant cannot cater orders (e.g. a staff
 * shortage or equipment outage). Customers can still place orders while one
 * is active — Order::getEstimatedCompletionAttribute() pushes their prep
 * estimate out to whenever service resumes.
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

    public function wasEndedEarly(): bool
    {
        return $this->ended_early_at !== null;
    }
}
