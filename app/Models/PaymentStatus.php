<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentStatus extends Model
{
    protected $fillable = ['status_name', 'description'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
