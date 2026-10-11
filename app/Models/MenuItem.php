<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_name',
        'item_type',
        'description',
        'price',
        'prep_time_minutes',
        'image',
        'is_available',
        'is_active',
        'category_id',
        'rtc_inventory_item_id',
        'rtc_quantity',
        'rtc_unit',
        'requires_rtc',
        'rtc_servings',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'prep_time_minutes'  => 'integer',
        'rtc_quantity'       => 'decimal:4',
        'rtc_servings'       => 'decimal:4',
        'is_available'       => 'boolean',
        'is_active'          => 'boolean',
        'requires_rtc'       => 'boolean',
    ];

    /* ── Relationships ───────────────────────────────────────── */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rtcItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'rtc_inventory_item_id');
    }

    public function conversionLogs(): HasMany
    {
        return $this->hasMany(ConversionLog::class);
    }

    /* ── RTC servings scopes/status ─────────────────────────── */

    public function scopeRtcTracked(Builder $q): Builder
    {
        return $q->where('item_type', 'food')->where('requires_rtc', true);
    }

    public function scopeFood(Builder $q): Builder
    {
        return $q->where('item_type', 'food');
    }

    public function scopeRtcLowStock(Builder $q): Builder
    {
        return $q->where('rtc_servings', '>', 0)->where('rtc_servings', '<', 10);
    }

    public function scopeRtcOutOfStock(Builder $q): Builder
    {
        return $q->where('rtc_servings', '<=', 0);
    }

    public function getRtcServingsStatusAttribute(): string
    {
        $servings = (float) $this->rtc_servings;
        if ($servings <= 0) return 'out_of_stock';
        if ($servings < 10) return 'low_stock';
        return 'available';
    }

    /**
     * Whether this dish's orderable quantity is capped by RTC servings stock:
     * food marked "Requires RTC servings" on Add/Edit Menu Item. Everything else
     * (beverages, desserts and other dishes not made from converted raw meat) is unlimited.
     */
    public function isRtcTracked(): bool
    {
        return $this->item_type === 'food' && (bool) $this->requires_rtc;
    }

    /**
     * How many servings can actually be ordered right now. Null means
     * "not stock-tracked" (no cap), not "zero available".
     */
    public function getAvailableStockAttribute(): ?int
    {
        return $this->isRtcTracked() ? (int) floor((float) $this->rtc_servings) : null;
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return asset('images/menu-placeholder.png');
    }

    public function isBeverage(): bool
    {
        return $this->item_type === 'beverage';
    }

    public function isFood(): bool
    {
        return $this->item_type === 'food';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /* ── Availability (automatic, from RTC servings) ─────────── */

    /** Fewer servings than this shows as Low Stock. */
    const LOW_SERVINGS = 10;

    /**
     * 'available' | 'low_stock' | 'sold_out', worked out from servings stock:
     * 0 servings = Sold Out, fewer than LOW_SERVINGS = Low Stock, otherwise Available.
     * Items that aren't stock-tracked (beverages, and food not marked "Requires RTC
     * servings") are always Available. Taking a dish off the menu entirely is
     * the separate Active / Inactive status.
     */
    public function getAvailabilityAttribute(): string
    {
        if (! $this->isRtcTracked()) {
            return 'available';
        }
        $servings = (float) $this->rtc_servings;

        return $servings <= 0 ? 'sold_out' : ($servings < self::LOW_SERVINGS ? 'low_stock' : 'available');
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match ($this->availability) {
            'sold_out'  => 'Sold Out',
            'low_stock' => 'Low Stock',
            default     => 'Available',
        };
    }

    /** Same rules as getAvailabilityAttribute(), for filtering lists. */
    public function scopeAvailability(Builder $q, string $availability): Builder
    {
        $tracked = fn ($w) => $w->where('item_type', 'food')->where('requires_rtc', true);

        return match ($availability) {
            'sold_out'  => $q->where($tracked)->where(fn ($w) => $w->whereNull('rtc_servings')->orWhere('rtc_servings', '<=', 0)),
            'low_stock' => $q->where($tracked)->where('rtc_servings', '>', 0)->where('rtc_servings', '<', self::LOW_SERVINGS),
            'available' => $q->where(fn ($w) => $w->where('item_type', '!=', 'food')->orWhere('requires_rtc', false)
                                ->orWhere('rtc_servings', '>=', self::LOW_SERVINGS)),
            default     => $q,
        };
    }
}
