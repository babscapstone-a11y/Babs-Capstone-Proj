<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['category_name', 'item_type', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->category_name;
    }
}
