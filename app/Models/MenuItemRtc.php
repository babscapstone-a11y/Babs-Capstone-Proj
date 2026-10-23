<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RTC stock of a dish (subtype of `menu_items`): the raw meat it's prepared from, how much of it
 * goes into one serving, and the ready-to-cook servings in stock. Only dishes tracked by RTC
 * servings have a row; dishes without one are unlimited.
 *
 * portion_per_serving is in the unit the Conversion screen uses: grams for meat stocked in
 * kilograms, otherwise the meat's own unit (see getPortionUnitAttribute).
 */
class MenuItemRtc extends Model
{
    protected $table = 'menu_item_rtc';

    protected $fillable = ['menu_item_id', 'inventory_item_id', 'portion_per_serving', 'servings'];

    protected $casts = [
        'portion_per_serving' => 'decimal:4',
        'servings'            => 'decimal:4',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /** The raw meat the dish is prepared from */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /** Unit of portion_per_serving, worked out from the meat: "Gram" for meat in kilograms */
    public function getPortionUnitAttribute(): ?string
    {
        $unit = $this->inventoryItem?->unit;

        return $unit === 'Kilogram' ? 'Gram' : $unit;
    }
}
