<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_name',
        'item_type',
        'description',
        'price',
        'image',
        'is_available',
        'is_active',
        'category_id',
        'rtc_inventory_item_id',
        'rtc_quantity',
        'rtc_unit',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'rtc_quantity' => 'decimal:4',
        'is_available' => 'boolean',
        'is_active'    => 'boolean',
    ];

    /* ── Relationships ───────────────────────────────────────── */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rtcItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'rtc_inventory_item_id');
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return asset('images/menu-placeholder.png');
    }

    public function isBeverage(): bool
    {
        return $this->item_type === 'beverage';
    }

    public function isFood(): bool
    {
        return $this->item_type === 'food';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return $this->is_available ? 'Available' : 'Unavailable';
    }
}
