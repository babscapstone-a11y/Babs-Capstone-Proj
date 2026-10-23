<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One delivery of stock received into inventory (table stock_ins): how much of an item came
 * in, its cost, and the stock level before and after. Created by App\Services\StockInService,
 * either entered directly or when a purchase order (ProcurementOrder) is stocked in.
 */
class StockIn extends Model
{
    protected $fillable = [
        'procurement_order_id', 'inventory_item_id',
        'quantity_purchased', 'unit', 'unit_cost', 'total_cost',
        'previous_quantity', 'new_quantity',
        'purchase_date', 'remarks', 'recorded_by',
    ];

    protected $casts = [
        'quantity_purchased' => 'decimal:4',
        'unit_cost'          => 'decimal:2',
        'total_cost'         => 'decimal:2',
        'previous_quantity'  => 'decimal:4',
        'new_quantity'       => 'decimal:4',
        'purchase_date'      => 'date',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /** The purchase order this stock-in came from (null when entered manually) */
    public function procurementOrder(): BelongsTo
    {
        return $this->belongsTo(ProcurementOrder::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Stock-ins of one item type ("rtc" raw meat / "beverage"), read from the stocked item */
    public function scopeOfType(Builder $q, string $type): Builder
    {
        return $q->whereHas('inventoryItem', fn ($i) => $i->where('item_type', $type));
    }
}
