<?php

namespace App\Support;

use App\Models\Discount;

/**
 * Settings for the admin confirmation pop-up (openModal) shown before a discount
 * is deactivated or activated. Used by the discount list and the discount view page.
 */
class DiscountToggle
{
    public static function confirm(Discount $discount): array
    {
        $deactivating = (bool) $discount->is_active;

        return [
            'type'        => $deactivating ? 'danger' : 'warn',
            'iconClass'   => $deactivating ? 'fas fa-ban' : 'fas fa-circle-check',
            'title'       => $deactivating ? 'Deactivate Discount?' : 'Activate Discount?',
            'desc'        => $deactivating
                ? "\"{$discount->discount_name}\" will no longer be available in the POS or at online checkout. You can activate it again at any time."
                : "\"{$discount->discount_name}\" will become available in the POS and at online checkout.",
            'action'      => route('discounts.toggle-status', $discount),
            'method'      => 'PUT',
            'confirmText' => $deactivating ? 'Deactivate' : 'Activate',
        ];
    }
}
