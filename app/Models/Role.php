<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['role_name', 'display_name'];

    /** Role of customer accounts; every other role is a staff role */
    const CUSTOMER = 'customer';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->display_name ?? ucfirst(str_replace('_', ' ', $this->role_name));
    }
}
