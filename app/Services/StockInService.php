<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockIn;

/**
 * Records a stock-in transaction and adds the purchased quantity to inventory.
 * Shared by the manual Stock-In page and the Purchase Order "Record Stock-In" button.
 */
class StockInService
{
    public const UNIT_FAMILIES = [
        'Gram'     => ['Gram', 'Kilogram'],
        'Kilogram' => ['Gram', 'Kilogram'],
        'Piece'    => ['Piece'],
        'Box'      => ['Box'],
        'Case'     => ['Case'],
    ];

    private const UNIT_CONVERSION_FACTORS = [
        'Gram->Kilogram' => 0.001,
        'Kilogram->Gram' => 1000.0,
    ];

    public function allowedUnits(InventoryItem $item): array
    {
        $units = self::UNIT_FAMILIES[$item->unit] ?? [$item->unit];

        // A beverage counted per bottle/can can also be received by its pack (Case or Box)
        return $item->packSize() ? [...$units, $item->pack_unit] : $units;
    }

    public function unitConversionFactor(string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === $toUnit) {
            return 1.0;
        }

        return self::UNIT_CONVERSION_FACTORS["{$fromUnit}->{$toUnit}"] ?? 1.0;
    }

    /**
     * @param  array  $extra  Additional stock_ins columns (e.g. procurement_order_id, remarks)
     */
    public function record(
        InventoryItem $item,
        float $purchased,
        string $unit,
        ?float $totalCost,
        string $purchaseDate,
        array $extra = [],
    ): StockIn {
        // Received by the pack (Case/Box): count it in the item's own unit (1 Case of 6 = 6 bottles),
        // so stock and history stay in whole pieces; the remarks keep how it actually arrived
        if ($unit === $item->pack_unit && ($packSize = $item->packSize())) {
            $extra    += ['remarks' => "Received as {$purchased} {$unit} ({$packSize} {$item->unit} per {$unit})"];
            $purchased *= $packSize;
            $unit       = $item->unit;
        }

        $unitCost = $totalCost !== null ? round($totalCost / $purchased, 2) : null;

        // Convert the purchased amount into the item's own tracked unit before touching stock
        $factorToBase        = $this->unitConversionFactor($unit, $item->unit);
        $purchasedInBaseUnit = $purchased * $factorToBase;

        $previousQtyBaseUnit = (float) $item->quantity;
        $newQtyBaseUnit      = $previousQtyBaseUnit + $purchasedInBaseUnit;

        // Keep this transaction's previous/new quantity columns in the unit that was actually entered
        $factorBaseToEntered = $this->unitConversionFactor($item->unit, $unit);
        $previousQtyEntered  = $previousQtyBaseUnit * $factorBaseToEntered;
        $newQtyEntered       = $previousQtyEntered + $purchased;

        // Record the stock-in transaction
        $transaction = StockIn::create(array_merge([
            'inventory_item_id'  => $item->id,
            'quantity_purchased' => $purchased,
            'unit'               => $unit,
            'unit_cost'          => $unitCost,
            'total_cost'         => $totalCost,
            'previous_quantity'  => $previousQtyEntered,
            'new_quantity'       => $newQtyEntered,
            'purchase_date'      => $purchaseDate,
            'recorded_by'        => auth()->id(),
        ], $extra));

        // Update inventory — keep the item's current cost price in sync with the latest purchase
        $itemUpdate = ['quantity' => $newQtyBaseUnit];
        if ($unitCost !== null && $purchasedInBaseUnit > 0) {
            $itemUpdate['cost_price'] = round($totalCost / $purchasedInBaseUnit, 2);
        }
        $item->update($itemUpdate);

        return $transaction;
    }
}
