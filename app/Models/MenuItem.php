<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * A dish or drink on the menu. Stock tracking lives in subtype tables: menu_item_rtc for food
 * tracked by ready-to-cook servings, menu_item_beverages for drinks tracked by a stock item.
 * The type (food / beverage) comes from the category. The rtc_* / requires_rtc / item_type /
 * beverage_inventory_item_id attributes below read those, so screens work as before.
 */
class MenuItem extends Model
{
    protected $fillable = [
        'menu_name',
        'description',
        'price',
        'prep_time_minutes',
        'image',
        'is_available',
        'is_active',
        'category_id',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'prep_time_minutes'  => 'integer',
        'is_available'       => 'boolean',
        'is_active'          => 'boolean',
    ];

    /* ── Relationships ───────────────────────────────────────── */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** RTC stock (raw meat, portion, servings) — only for dishes tracked by RTC servings */
    public function rtcStock(): HasOne
    {
        return $this->hasOne(MenuItemRtc::class);
    }

    /** Stock link — only for beverages tracked by a stock item */
    public function beverageStock(): HasOne
    {
        return $this->hasOne(MenuItemBeverage::class);
    }

    /** The raw meat the dish is prepared from (through menu_item_rtc) */
    public function rtcItem(): HasOneThrough
    {
        return $this->hasOneThrough(InventoryItem::class, MenuItemRtc::class, 'menu_item_id', 'id', 'id', 'inventory_item_id');
    }

    /** The stock item a beverage is served from — one order uses one piece of it (through menu_item_beverages). */
    public function beverageItem(): HasOneThrough
    {
        return $this->hasOneThrough(InventoryItem::class, MenuItemBeverage::class, 'menu_item_id', 'id', 'id', 'inventory_item_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    /* ── RTC servings scopes/status ─────────────────────────── */

    public function scopeRtcTracked(Builder $q): Builder
    {
        return $q->has('rtcStock');
    }

    /** "food" or "beverage", from the category */
    public function scopeOfType(Builder $q, string $type): Builder
    {
        return $q->whereHas('category', fn ($c) => $c->where('item_type', $type));
    }

    public function scopeFood(Builder $q): Builder
    {
        return $q->ofType('food');
    }

    public function scopeRtcLowStock(Builder $q): Builder
    {
        return $q->whereHas('rtcStock', fn ($r) => $r->where('servings', '>', 0)->where('servings', '<', self::LOW_SERVINGS));
    }

    public function scopeRtcOutOfStock(Builder $q): Builder
    {
        return $q->whereHas('rtcStock', fn ($r) => $r->where('servings', '<=', 0));
    }

    public function scopeRtcAvailable(Builder $q): Builder
    {
        return $q->whereHas('rtcStock', fn ($r) => $r->where('servings', '>=', self::LOW_SERVINGS));
    }

    /* ── Stock attributes (stored in menu_item_rtc / menu_item_beverages / categories) ── */

    public function getItemTypeAttribute(): string
    {
        return $this->category?->item_type ?? 'food';
    }

    public function getRequiresRtcAttribute(): bool
    {
        return $this->rtcStock !== null;
    }

    public function getRtcServingsAttribute(): float
    {
        return (float) ($this->rtcStock?->servings ?? 0);
    }

    public function getRtcInventoryItemIdAttribute(): ?int
    {
        return $this->rtcStock?->inventory_item_id;
    }

    /** Portion of raw meat per serving, in rtc_unit */
    public function getRtcQuantityAttribute(): ?float
    {
        return $this->rtcStock?->portion_per_serving !== null ? (float) $this->rtcStock->portion_per_serving : null;
    }

    public function getRtcUnitAttribute(): ?string
    {
        return $this->rtcStock?->portion_unit;
    }

    public function getBeverageInventoryItemIdAttribute(): ?int
    {
        return $this->beverageStock?->inventory_item_id;
    }

    /**
     * Applies the Add/Edit Menu Item stock settings: an RTC row while "Requires RTC servings" is
     * ticked on food, a stock link while a beverage has a stock item. Returns an error message
     * instead of dropping RTC tracking while servings are still in stock, or null when done.
     */
    public function syncStockTracking(bool $tracksRtc, ?int $beverageStockId): ?string
    {
        $tracksRtc       = $tracksRtc && $this->item_type === 'food';
        $beverageStockId = $this->item_type === 'beverage' ? $beverageStockId : null;

        $rtc = $this->rtcStock()->first();
        if (! $tracksRtc && $rtc && (float) $rtc->servings > 0) {
            $servings = (int) floor((float) $rtc->servings);

            return "{$this->menu_name} still has {$servings} RTC " . Str::plural('serving', $servings)
                . ' in stock. Deduct them on the RTC Inventory page before turning off "Requires RTC servings".';
        }

        if ($tracksRtc && ! $rtc) {
            $this->rtcStock()->create(['servings' => 0]);
        } elseif (! $tracksRtc && $rtc) {
            $rtc->delete();
        }

        if ($beverageStockId) {
            $this->beverageStock()->updateOrCreate([], ['inventory_item_id' => $beverageStockId]);
        } else {
            $this->beverageStock()->delete();
        }

        $this->unsetRelation('rtcStock')->unsetRelation('beverageStock')->unsetRelation('beverageItem')->unsetRelation('rtcItem');

        return null;
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
        return $this->rtcStock !== null;
    }

    /**
     * Whether this beverage's orderable quantity is capped by stock: a beverage linked
     * to a stock item on Add/Edit Menu Item. An unlinked beverage is unlimited.
     */
    public function isBeverageTracked(): bool
    {
        return $this->beverageStock !== null;
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
            ->whereHas('rtcStock', fn ($r) => $r->where('servings', '<=', 0))
            ->orWhereHas('beverageStock.inventoryItem', fn ($i) => $i->where('quantity', '<=', 0));

        $lowStock = fn ($w) => $w
            ->whereHas('rtcStock', fn ($r) => $r->where('servings', '>', 0)->where('servings', '<', self::LOW_SERVINGS))
            ->orWhereHas('beverageStock.inventoryItem', fn ($i) => $i->lowStock());

        return match ($availability) {
            'sold_out'  => $q->where($soldOut),
            'low_stock' => $q->where($lowStock),
            'available' => $q->whereNot($soldOut)->whereNot($lowStock),
            default     => $q,
        };
    }
}
