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
        'quantity_received', 'amount_paid',
        'unit', 'stock_status',
    ];

    protected $casts = [
        'current_stock'        => 'decimal:4',
        'threshold'            => 'decimal:4',
        'quantity_recommended' => 'decimal:4',
        'quantity_to_purchase' => 'decimal:4',
        'quantity_received'    => 'decimal:4',
        'amount_paid'          => 'decimal:2',
    ];

    /* ── Receiving (what was actually bought) ── */

    /** Bought quantity and amount paid are both filled in (0 bought = item not purchased) */
    public function isReceivingComplete(): bool
    {
        if ($this->quantity_received === null) {
            return false;
        }

        return (float) $this->quantity_received <= 0 || $this->amount_paid !== null;
    }

    /** Cost per unit based on the amount actually paid */
    public function getUnitCostAttribute(): ?float
    {
        if ($this->amount_paid === null || (float) $this->quantity_received <= 0) {
            return null;
        }

        return round((float) $this->amount_paid / (float) $this->quantity_received, 2);
    }

    /** How the bought quantity compares with the ordered quantity: pending, match, short, over, not_bought */
    public function getReceivingStatusAttribute(): string
    {
        if ($this->quantity_received === null) {
            return 'pending';
        }

        $received = (float) $this->quantity_received;
        $ordered  = (float) $this->quantity_to_purchase;

        return match (true) {
            $received <= 0                        => 'not_bought',
            abs($received - $ordered) < 0.0001    => 'match',
            $received < $ordered                  => 'short',
            default                               => 'over',
        };
    }

    public function getReceivingLabelAttribute(): string
    {
        return match ($this->receiving_status) {
            'match'      => 'Complete',
            'short'      => 'Short',
            'over'       => 'Over',
            'not_bought' => 'Not Bought',
            default      => 'Pending',
        };
    }

    public function getReceivingBadgeClassAttribute(): string
    {
        return match ($this->receiving_status) {
            'match'      => 'badge-rcv-match',
            'short'      => 'badge-rcv-short',
            'over'       => 'badge-rcv-over',
            'not_bought' => 'badge-rcv-none',
            default      => 'badge-rcv-pending',
        };
    }

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
