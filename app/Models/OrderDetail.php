<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'item_name', 'quantity', 'notes', 'price', 'subtotal',
        'rtc_deducted_at',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'subtotal'         => 'decimal:2',
        'quantity'         => 'integer',
        'rtc_deducted_at'  => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
