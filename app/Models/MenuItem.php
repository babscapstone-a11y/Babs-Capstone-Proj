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
        'beverage_inventory_item_id',
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

    /** The stock item a beverage is served from — one order uses one piece of it. */
    public function beverageItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'beverage_inventory_item_id');
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
     * Whether this beverage's orderable quantity is capped by stock: a beverage linked
     * to a stock item on Add/Edit Menu Item. An unlinked beverage is unlimited.
     */
    public function isBeverageTracked(): bool
    {
        return $this->item_type === 'beverage' && $this->beverage_inventory_item_id !== null;
    }

    /** Capped by stock either way: RTC servings for food, pieces in stock for a beverage. */
    public function isStockTracked(): bool
    {
        return $this->isRtcTracked() || $this->isBeverageTracked();
    }

    /**
     * How many can actually be ordered right now — servings for food, pieces for a
     * beverage. Null means "not stock-tracked" (no cap), not "zero available".
     */
    public function getAvailableStockAttribute(): ?int
    {
        if ($this->isRtcTracked()) {
            return (int) floor((float) $this->rtc_servings);
        }
        if ($this->isBeverageTracked()) {
            return (int) floor((float) $this->beverageItem?->quantity);
        }

        return null;
    }

    /**
     * 'available' | 'low_stock' | 'out_of_stock' for a stock-tracked item, or null
     * when it isn't tracked. Used by the menu cards to show Sold Out / Low Stock.
     */
    public function getStockStatusAttribute(): ?string
    {
        if ($this->isRtcTracked()) {
            return $this->rtc_servings_status;
        }
        if ($this->isBeverageTracked()) {
            return $this->beverageItem?->stock_status ?? 'out_of_stock';
        }

        return null;
    }

    /** "Only 3 serving(s) of Pork Sisig left." — shown when an order asks for more than is in stock. */
    public function stockShortageMessage(): string
    {
        $unit = $this->isBeverageTracked() ? '' : ' serving(s)';

        return "Only {$this->available_stock}{$unit} of {$this->menu_name} left.";
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

    /* ── Availability (automatic, from RTC servings / beverage stock) ─────────── */

    /** Fewer servings than this shows as Low Stock. */
    const LOW_SERVINGS = 10;

    /**
     * 'available' | 'low_stock' | 'sold_out', worked out from servings stock:
     * 0 servings = Sold Out, fewer than LOW_SERVINGS = Low Stock, otherwise Available.
     * A beverage linked to a stock item follows that item's stock instead (0 = Sold Out,
     * below its reorder level = Low Stock). Items that aren't stock-tracked (unlinked
     * beverages, and food not marked "Requires RTC servings") are always Available.
     * Taking a dish off the menu entirely is the separate Active / Inactive status.
     */
    public function getAvailabilityAttribute(): string
    {
        if ($this->isBeverageTracked()) {
            return $this->stock_status === 'out_of_stock' ? 'sold_out' : $this->stock_status;
        }
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
        $soldOut = fn ($w) => $w
            ->where(fn ($f) => $f->where('item_type', 'food')->where('requires_rtc', true)
                ->where(fn ($n) => $n->whereNull('rtc_servings')->orWhere('rtc_servings', '<=', 0)))
            ->orWhere(fn ($b) => $b->where('item_type', 'beverage')
                ->whereHas('beverageItem', fn ($i) => $i->where('quantity', '<=', 0)));

        $lowStock = fn ($w) => $w
            ->where(fn ($f) => $f->where('item_type', 'food')->where('requires_rtc', true)
                ->where('rtc_servings', '>', 0)->where('rtc_servings', '<', self::LOW_SERVINGS))
            ->orWhere(fn ($b) => $b->where('item_type', 'beverage')
                ->whereHas('beverageItem', fn ($i) => $i->lowStock()));

        return match ($availability) {
            'sold_out'  => $q->where($soldOut),
            'low_stock' => $q->where($lowStock),
            'available' => $q->whereNot($soldOut)->whereNot($lowStock),
            default     => $q,
        };
    }
}
