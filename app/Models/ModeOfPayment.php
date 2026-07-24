<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeOfPayment extends Model
{
    protected $fillable = ['method_name'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
