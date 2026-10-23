<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Walk-in details of an order (subtype of `orders`, order_type "walk_in"): whether it's eaten
 * in or taken out, the table for dine-in, and the food server who took it. Online orders keep
 * their own details in `online_orders` instead.
 */
class WalkInOrder extends Model
{
    protected $fillable = ['order_id', 'service_type', 'table_number', 'placed_by'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** The staff member who took the order */
    public function placedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }
}
