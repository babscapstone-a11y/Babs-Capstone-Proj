<?php

namespace App\Models;

use App\Support\Units;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One raw meat used in a conversion: how much was used, the portion per serving, and the meat's
 * stock before and after. Its date is the conversion's, so it has no timestamps of its own.
 */
class ConversionLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversion_id', 'inventory_item_id', 'raw_quantity_used', 'unit', 'portion_size',
        'previous_raw_stock', 'remaining_raw_stock',
    ];

    protected $casts = [
        'raw_quantity_used'   => 'decimal:4',
        'portion_size'        => 'decimal:4',
        'previous_raw_stock'  => 'decimal:4',
        'remaining_raw_stock' => 'decimal:4',
    ];

    public function conversion(): BelongsTo
    {
        return $this->belongsTo(Conversion::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
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
