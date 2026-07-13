<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'rtc_servings',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'rtc_quantity' => 'decimal:4',
        'rtc_servings' => 'decimal:4',
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

    public function conversionLogs(): HasMany
    {
        return $this->hasMany(ConversionLog::class);
    }

    /* ── RTC servings scopes/status ─────────────────────────── */

    public function scopeRtcTracked(Builder $q): Builder
    {
        return $q->whereNotNull('rtc_quantity');
    }

    public function scopeRtcLowStock(Builder $q): Builder
    {
        return $q->where('rtc_servings', '>', 0)->where('rtc_servings', '<=', 10);
    }

    public function scopeRtcOutOfStock(Builder $q): Builder
    {
        return $q->where('rtc_servings', '<=', 0);
    }

    public function getRtcServingsStatusAttribute(): string
    {
        $servings = (float) $this->rtc_servings;
        if ($servings <= 0) return 'out_of_stock';
        if ($servings <= 10) return 'low_stock';
        return 'available';
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
