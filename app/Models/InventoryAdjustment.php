<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'inventory_item_id', 'adjustment_type',
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

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
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
