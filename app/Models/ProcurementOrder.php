<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcurementOrder extends Model
{
    protected $fillable = [
        'po_number', 'status', 'notes', 'total_items', 'prepared_by', 'finalized_at',
        'stocked_in_at', 'stocked_in_by', 'source_po_id',
    ];

    protected $casts = [
        'finalized_at'  => 'datetime',
        'stocked_in_at' => 'datetime',
    ];

    /* ── Relationships ── */
    public function items(): HasMany
    {
        return $this->hasMany(ProcurementOrderItem::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function stockedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stocked_in_by');
    }

    /** The earlier PO this one was generated from (its short / not-bought items) */
    public function sourcePo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_po_id');
    }

    /** The follow-up PO generated from this one's short / not-bought items */
    public function followUp(): HasOne
    {
        return $this->hasOne(self::class, 'source_po_id');
    }

    /** Stock-in transactions created from this purchase order */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    /* ── Helpers ── */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** True once the PO is locked (finalized or already stocked in) */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['finalized', 'stocked_in'], true);
    }

    public function isStockedIn(): bool
    {
        return $this->status === 'stocked_in';
    }

    /** Finalized but its items have not been added to inventory yet */
    public function awaitingStockIn(): bool
    {
        return $this->status === 'finalized';
    }

    public function getTotalAmountPaidAttribute(): float
    {
        return (float) $this->items->sum('amount_paid');
    }

    /**
     * Items whose bought quantity / amount paid are still missing.
     * A PO can only be finalized once this is empty.
     */
    public function incompleteItems()
    {
        return $this->items->reject(fn (ProcurementOrderItem $item) => $item->isReceivingComplete());
    }

    /** Items that were bought short or not bought at all — what a follow-up PO would carry over */
    public function remainingItems()
    {
        return $this->items->filter(fn (ProcurementOrderItem $item) => in_array($item->receiving_status, ['short', 'not_bought'], true));
    }

    /**
     * Lowest quantity to purchase allowed for a line: any amount above 0 (beverages are bought
     * whole, so at least 1). The item's threshold is only a guide, not a minimum.
     */
    public function minimumFor(ProcurementOrderItem $item): float
    {
        return self::minimumQuantity($item->item_type);
    }

    public static function minimumQuantity(?string $itemType): float
    {
        return $itemType === 'beverage' ? 1 : 0.01;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'stocked_in' => 'Stocked In',
            'finalized'  => 'Finalized',
            default      => 'Draft',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'stocked_in' => 'badge-po-stocked',
            'finalized'  => 'badge-po-finalized',
            default      => 'badge-po-draft',
        };
    }

    /* ── PO Number Generator ── */
    public static function generatePoNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = 'PO-' . $date . '-';
        $last   = static::where('po_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('po_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
