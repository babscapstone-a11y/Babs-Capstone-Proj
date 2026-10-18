<?php

namespace App\Models;

use App\Support\Units;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionLog extends Model
{
    protected $fillable = [
        'batch_code', 'inventory_item_id', 'menu_item_id', 'raw_quantity_used', 'unit', 'portion_size',
        'rtc_units_produced', 'previous_raw_stock', 'remaining_raw_stock',
        'previous_rtc_servings', 'new_rtc_servings', 'converted_by', 'remarks',
    ];

    protected $casts = [
        'raw_quantity_used'     => 'decimal:4',
        'portion_size'          => 'decimal:4',
        'rtc_units_produced'    => 'decimal:4',
        'previous_raw_stock'    => 'decimal:4',
        'remaining_raw_stock'   => 'decimal:4',
        'previous_rtc_servings' => 'decimal:4',
        'new_rtc_servings'      => 'decimal:4',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /** Key shared by the log rows of one conversion (one row per raw meat used) */
    public function getBatchKeyAttribute(): string
    {
        return $this->batch_code ?? 'log-' . $this->id;
    }

    /** Portion per serving for display: grams for kilogram items, otherwise the item's own unit */
    public function getPortionLabelAttribute(): string
    {
        if ($this->unit === 'Kilogram') {
            return rtrim(rtrim(number_format((float) $this->portion_size * 1000, 1), '0'), '.') . ' g';
        }

        return rtrim(rtrim(number_format((float) $this->portion_size, 2), '0'), '.') . ' ' . Units::short($this->unit);
    }
}
