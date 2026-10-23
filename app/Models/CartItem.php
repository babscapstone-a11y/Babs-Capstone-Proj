<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'menu_item_id', 'quantity', 'notes'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * The dish's current menu price. Not stored on the cart line, so a price change shows in the
     * cart right away; the price actually paid is saved in order_details at checkout.
     */
    public function getUnitPriceAttribute(): float
    {
        return (float) $this->menuItem?->price;
    }

    public function getSubtotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
}
