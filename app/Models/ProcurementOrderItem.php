<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementOrderItem extends Model
{
    protected $fillable = [
        'procurement_order_id', 'inventory_item_id',
        'item_name', 'category', 'item_type',
        'current_stock', 'threshold',
        'quantity_recommended', 'quantity_to_purchase',
        'unit', 'stock_status',
    ];

    protected $casts = [
        'current_stock'        => 'decimal:4',
        'threshold'            => 'decimal:4',
        'quantity_recommended' => 'decimal:4',
        'quantity_to_purchase' => 'decimal:4',
    ];

    public function procurementOrder(): BelongsTo
    {
        return $this->belongsTo(ProcurementOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'badge-po-out',
            'low_stock'    => 'badge-po-low',
            default        => 'badge-po-ok',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            default        => 'Available',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->item_type === 'rtc' ? 'RTC Raw Meat' : 'Beverage';
    }
}
