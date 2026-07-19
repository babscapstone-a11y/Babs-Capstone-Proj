<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
    private const UNIT_FAMILIES = [
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

    private function unitConversionFactor(string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === $toUnit) {
            return 1.0;
        }

        return self::UNIT_CONVERSION_FACTORS["{$fromUnit}->{$toUnit}"] ?? 1.0;
    }

    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['inventoryItem', 'recorder']);

        if ($search = $request->input('q')) {
            $query->whereHas('inventoryItem', fn ($q) => $q->where('item_name', 'like', "%{$search}%"));
        }
        if ($type = $request->input('type')) {
            $query->where('po_type', $type);
        }
        if ($from = $request->input('from')) {
            $query->where('purchase_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('purchase_date', '<=', $to);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.stock-in', compact('transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity_purchased' => ['required', 'numeric', 'min:0.01'],
            'unit'              => ['required', 'string', 'max:50'],
            'total_cost'        => ['nullable', 'numeric', 'min:0'],
            'purchase_date'     => ['required', 'date'],
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        $allowedUnits = self::UNIT_FAMILIES[$item->unit] ?? [$item->unit];
        if (! in_array($request->unit, $allowedUnits, true)) {
            return back()->withErrors(['unit' => "Unit must be one of: " . implode(', ', $allowedUnits) . " for {$item->item_name}."]);
        }

        $unit      = $request->unit;
        $purchased = (float) $request->quantity_purchased;
        $totalCost = $request->filled('total_cost') ? (float) $request->total_cost : null;
        $unitCost  = $totalCost !== null ? round($totalCost / $purchased, 2) : null;

        // Convert the purchased amount into the item's own tracked unit before touching stock
        $factorToBase        = $this->unitConversionFactor($unit, $item->unit);
        $purchasedInBaseUnit = $purchased * $factorToBase;

        $previousQtyBaseUnit = (float) $item->quantity;
        $newQtyBaseUnit      = $previousQtyBaseUnit + $purchasedInBaseUnit;

        // Keep this transaction's previous/new quantity columns in the unit that was actually entered
        $factorBaseToEntered  = $this->unitConversionFactor($item->unit, $unit);
        $previousQtyEntered   = $previousQtyBaseUnit * $factorBaseToEntered;
        $newQtyEntered        = $previousQtyEntered + $purchased;

        // Record the stock-in transaction
        PurchaseOrder::create([
            'inventory_item_id' => $item->id,
            'po_type'           => $item->item_type,
            'quantity_purchased'=> $purchased,
            'unit'              => $unit,
            'unit_cost'         => $unitCost,
            'total_cost'        => $totalCost,
            'previous_quantity' => $previousQtyEntered,
            'new_quantity'      => $newQtyEntered,
            'purchase_date'     => $request->purchase_date,
            'recorded_by'       => auth()->id(),
        ]);

        // Update inventory — keep the item's current cost price in sync with the latest purchase
        $itemUpdate = ['quantity' => $newQtyBaseUnit];
        if ($unitCost !== null && $purchasedInBaseUnit > 0) {
            $itemUpdate['cost_price'] = round($totalCost / $purchasedInBaseUnit, 2);
        }
        $item->update($itemUpdate);

        $costNote = $unitCost !== null ? " at ₱{$unitCost}/{$unit} (₱{$totalCost} total)" : '';

        return back()->with('success', "Stock-in recorded: +{$purchased} {$unit} of {$item->item_name}{$costNote}. New total: {$newQtyBaseUnit} {$item->unit}.");
    }
}
