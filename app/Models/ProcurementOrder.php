<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementOrder extends Model
{
    protected $fillable = [
        'po_number', 'status', 'notes', 'total_items', 'prepared_by', 'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
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

    /* ── Helpers ── */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'finalized' => 'Finalized',
            default     => 'Draft',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'finalized' => 'badge-po-finalized',
            default     => 'badge-po-draft',
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
