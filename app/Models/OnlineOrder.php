<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineOrder extends Model
{
    protected $fillable = ['order_id', 'delivery_address', 'contact_number'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
