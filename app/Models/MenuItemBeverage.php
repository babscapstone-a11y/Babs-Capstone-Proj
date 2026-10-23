<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stock link of a beverage (subtype of `menu_items`): the stock item it's served from, one piece
 * per order. Only stock-tracked beverages have a row; beverages without one are unlimited.
 */
class MenuItemBeverage extends Model
{
    protected $fillable = ['menu_item_id', 'inventory_item_id'];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
