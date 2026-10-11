<?php

namespace App\Support;

/**
 * Short unit names for display ("Kilogram" → "Kg"). Inventory stores the full names —
 * the Stock-In gram/kilogram conversion relies on them — so this only changes what is shown.
 * Blade: @unit($item->unit). Page scripts: unitShort(unit) (layouts/admin).
 */
class Units
{
    const SHORT = [
        'Kilogram' => 'Kg',
        'Gram'     => 'g',
    ];

    public static function short(?string $unit): string
    {
        return self::SHORT[$unit] ?? (string) $unit;
    }
}
