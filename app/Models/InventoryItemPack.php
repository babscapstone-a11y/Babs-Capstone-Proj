<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The pack (Case or Box) a beverage can be bought by, and how many pieces it holds. Only items
 * bought by the pack have a row; stock itself stays counted per piece on the inventory item.
 */
class InventoryItemPack extends Model
{
    protected $fillable = ['inventory_item_id', 'pack_unit', 'pieces_per_pack'];

    protected $casts = [
        'pieces_per_pack' => 'integer',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
