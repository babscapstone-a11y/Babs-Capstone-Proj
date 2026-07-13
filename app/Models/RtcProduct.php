<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RtcProduct extends Model
{
    protected $fillable = [
        'name', 'servings', 'portion_size', 'portion_unit',
    ];

    protected $casts = [
        'servings'     => 'decimal:4',
        'portion_size' => 'decimal:4',
    ];

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->where('servings', '>', 0)->where('servings', '<=', 10);
    }

    public function scopeOutOfStock(Builder $q): Builder
    {
        return $q->where('servings', '<=', 0);
    }

    public function getServingsStatusAttribute(): string
    {
        $servings = (float) $this->servings;
        if ($servings <= 0) return 'out_of_stock';
        if ($servings <= 10) return 'low_stock';
        return 'available';
    }

    public function conversionLogs(): HasMany
    {
        return $this->hasMany(ConversionLog::class);
    }
}
