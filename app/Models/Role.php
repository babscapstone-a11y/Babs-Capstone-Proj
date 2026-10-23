<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['role_name'];

    /** Role of customer accounts; every other role is a staff role */
    const CUSTOMER = 'customer';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Readable role name for the screen: "kitchen_staff" → "Kitchen Staff" */
    public function getLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->role_name));
    }
}
