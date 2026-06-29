<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'item_name', 'item_type', 'category', 'unit',
        'quantity', 'min_stock_level', 'reorder_level',
        'cost_price', 'is_rtc', 'rtc_servings',
        'portion_size', 'portion_unit', 'supplier', 'is_active',
    ];

    protected $casts = [
        'quantity'        => 'decimal:4',
        'min_stock_level' => 'decimal:4',
        'reorder_level'   => 'decimal:4',
        'cost_price'      => 'decimal:2',
        'rtc_servings'    => 'decimal:4',
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
                 ->whereRaw('quantity <= reorder_level');
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
        if ((float) $this->quantity <= (float) $this->reorder_level) {
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

    public function getRtcServingsStatusAttribute(): string
    {
        if ($this->item_type !== 'rtc') return 'n/a';
        $servings = (float) $this->rtc_servings;
        if ($servings <= 0) return 'out_of_stock';
        if ($servings <= 10) return 'low_stock';
        return 'available';
    }

    public function getSuggestedRestockAttribute(): float
    {
        $deficit = (float) $this->reorder_level - (float) $this->quantity;
        return max(0, round($deficit * 1.5, 2));
    }

    public function isRtc(): bool  { return $this->item_type === 'rtc'; }
    public function isBeverage(): bool { return $this->item_type === 'beverage'; }

    /* ── Relationships ── */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'rtc_inventory_item_id');
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
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
