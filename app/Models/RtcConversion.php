<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One conversion of raw meat into RTC servings of a dish: how many servings it produced and the
 * dish's servings before and after. The raw meats used are its details (RtcConversionDetail), one per meat.
 */
class RtcConversion extends Model
{
    protected $fillable = [
        'menu_item_id', 'servings_produced', 'previous_servings', 'new_servings', 'converted_by', 'remarks',
    ];

    protected $casts = [
        'servings_produced' => 'decimal:4',
        'previous_servings' => 'decimal:4',
        'new_servings'      => 'decimal:4',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(RtcConversionDetail::class)->orderBy('id');
    }

    /** The dish the servings were made for (none on older per-meat conversions) */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function converter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /** "Pork, Pork Belly" — the raw meats used */
    public function getMeatNamesAttribute(): string
    {
        return $this->details->map(fn (RtcConversionDetail $d) => $d->inventoryItem?->item_name ?? '—')->implode(', ');
    }
}
