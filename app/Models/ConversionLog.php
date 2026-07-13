<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionLog extends Model
{
    protected $fillable = [
        'inventory_item_id', 'rtc_product_id', 'raw_quantity_used', 'unit', 'portion_size',
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

    public function rtcProduct(): BelongsTo
    {
        return $this->belongsTo(RtcProduct::class);
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }
}
