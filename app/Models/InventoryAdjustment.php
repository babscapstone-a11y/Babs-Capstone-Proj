<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A manual stock correction. Points at either a stock item (raw meat / beverage quantity)
 * or a menu item (its RTC servings).
 */
class InventoryAdjustment extends Model
{
    protected $fillable = [
        'inventory_item_id', 'menu_item_id', 'adjustment_type',
        'quantity_before', 'quantity_adjusted', 'quantity_after',
        'reason', 'remarks', 'adjusted_by',
    ];

    protected $casts = [
        'quantity_before'    => 'decimal:4',
        'quantity_adjusted'  => 'decimal:4',
        'quantity_after'     => 'decimal:4',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function isServings(): bool
    {
        return $this->menu_item_id !== null;
    }

    /** Name of what was adjusted. */
    public function getSubjectNameAttribute(): string
    {
        return $this->isServings() ? ($this->menuItem?->menu_name ?? '—') : ($this->inventoryItem?->item_name ?? '—');
    }

    /** "Raw Meat", "Beverage" or "RTC Servings". */
    public function getSubjectKindAttribute(): string
    {
        if ($this->isServings()) {
            return 'RTC Servings';
        }

        return $this->inventoryItem?->item_type === 'beverage' ? 'Beverage' : 'Raw Meat';
    }

    /** Short unit for display ("Kg", "Piece", "srv"). */
    public function getUnitLabelAttribute(): string
    {
        return $this->isServings() ? 'srv' : \App\Support\Units::short($this->inventoryItem?->unit);
    }

    public function getTypeLabel(): string
    {
        return match($this->adjustment_type) {
            'damaged'    => 'Damaged',
            'expired'    => 'Expired',
            'missing'    => 'Missing / Lost',
            'correction' => 'Manual Correction',
            default      => ucfirst($this->adjustment_type),
        };
    }
}
