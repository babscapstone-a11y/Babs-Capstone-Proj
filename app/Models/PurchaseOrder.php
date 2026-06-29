<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'inventory_item_id', 'po_type', 'supplier',
        'quantity_purchased', 'unit', 'previous_quantity', 'new_quantity',
        'purchase_date', 'remarks', 'recorded_by',
    ];

    protected $casts = [
        'quantity_purchased' => 'decimal:4',
        'previous_quantity'  => 'decimal:4',
        'new_quantity'       => 'decimal:4',
        'purchase_date'      => 'date',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeRtc(Builder $q): Builder      { return $q->where('po_type', 'rtc'); }
    public function scopeBeverage(Builder $q): Builder { return $q->where('po_type', 'beverage'); }
}
