<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;

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
        return self::UNIT_FAMILIES[$item->unit] ?? [$item->unit];
    }

    public function unitConversionFactor(string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === $toUnit) {
            return 1.0;
        }

        return self::UNIT_CONVERSION_FACTORS["{$fromUnit}->{$toUnit}"] ?? 1.0;
    }

    /**
     * @param  array  $extra  Additional purchase_orders columns (e.g. procurement_order_id, remarks)
     */
    public function record(
        InventoryItem $item,
        float $purchased,
        string $unit,
        ?float $totalCost,
        string $purchaseDate,
        array $extra = [],
    ): PurchaseOrder {
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
        $transaction = PurchaseOrder::create(array_merge([
            'inventory_item_id'  => $item->id,
            'po_type'            => $item->item_type,
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
