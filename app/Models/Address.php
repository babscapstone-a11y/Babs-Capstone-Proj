<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $fillable = [
        'street', 'barangay', 'municipality', 'province', 'postal_code',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->street,
            $this->barangay,
            $this->municipality,
            trim($this->province . ' ' . $this->postal_code),
        ]));
    }
}
