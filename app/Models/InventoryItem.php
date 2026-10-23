<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InventoryItem extends Model
{
    protected $fillable = [
        'item_name', 'item_type', 'category', 'unit', 'pack_unit', 'pieces_per_pack',
        'quantity', 'min_stock_level', 'reorder_level',
        'cost_price', 'is_rtc',
        'portion_size', 'portion_unit', 'supplier', 'is_active',
    ];

    protected $casts = [
        'pieces_per_pack' => 'integer',
        'quantity'        => 'decimal:4',
        'min_stock_level' => 'decimal:4',
        'reorder_level'   => 'decimal:4',
        'cost_price'      => 'decimal:2',
        'portion_size'    => 'decimal:4',
        'is_rtc'          => 'boolean',
        'is_active'       => 'boolean',
    ];

    /* ── Scopes ── */
    public function scopeRtc(Builder $q): Builder
    {
        return $q->where('item_type', 'rtc');
    }

    public function scopeBeverage(Builder $q): Builder
    {
        return $q->where('item_type', 'beverage');
    }

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->where('quantity', '>', 0)
                 ->whereRaw('quantity < reorder_level');   // low = below the minimum, not equal to it
    }

    public function scopeOutOfStock(Builder $q): Builder
    {
        return $q->where('quantity', '<=', 0);
    }

    /* ── Computed status ── */
    public function getStockStatusAttribute(): string
    {
        if ((float) $this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ((float) $this->quantity < (float) $this->reorder_level) {
            return 'low_stock';
        }
        return 'available';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            default        => 'Available',
        };
    }

    public function getSuggestedRestockAttribute(): float
    {
        $deficit = (float) $this->reorder_level - (float) $this->quantity;
        return max(0, round($deficit * 1.5, 2));
    }

    public function isRtc(): bool  { return $this->item_type === 'rtc'; }
    public function isBeverage(): bool { return $this->item_type === 'beverage'; }

    /** Packs a beverage can arrive in. Stock itself is counted per piece (bottle/can). */
    const PACK_UNITS = ['Case', 'Box'];

    /**
     * How many of this beverage's own unit (bottle, can, Piece) come in one pack_unit (Case or
     * Box), or null when it is only received by the piece. Set per item: a Case of 1 L Coke
     * holds 6, a Case of 8 oz holds 12.
     */
    public function packSize(): ?int
    {
        $size = (int) $this->pieces_per_pack;

        return $this->isBeverage() && in_array($this->pack_unit, self::PACK_UNITS, true)
            && $this->unit !== $this->pack_unit && $size > 1 ? $size : null;
    }

    /** Stock as full packs plus loose pieces ("1 Case + 4 bottle"), for items with a pack size. */
    public function getPackBreakdownAttribute(): ?string
    {
        $size = $this->packSize();
        $qty  = (int) floor((float) $this->quantity);
        if (! $size || $qty <= 0) {
            return null;
        }

        return intdiv($qty, $size) . " {$this->pack_unit} + " . ($qty % $size) . ' ' . $this->unit;
    }

    /* ── Relationships ── */
    /** Dishes prepared from this raw meat (through menu_item_rtc) */
    public function menuItems(): HasManyThrough
    {
        return $this->hasManyThrough(MenuItem::class, MenuItemRtc::class, 'inventory_item_id', 'id', 'id', 'menu_item_id');
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function conversionLogs(): HasMany
    {
        return $this->hasMany(ConversionLog::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    /* ── Helpers ── */
    public function getLabelAttribute(): string
    {
        return "{$this->item_name} ({$this->unit})";
    }
}
