<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
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

        $previousQty = (float) $item->quantity;
        $purchased   = (float) $request->quantity_purchased;
        $newQty      = $previousQty + $purchased;
        $unit        = $request->unit;
        $totalCost   = $request->filled('total_cost') ? (float) $request->total_cost : null;
        $unitCost    = $totalCost !== null ? round($totalCost / $purchased, 2) : null;

        // Record the stock-in transaction
        PurchaseOrder::create([
            'inventory_item_id' => $item->id,
            'po_type'           => $item->item_type,
            'quantity_purchased'=> $purchased,
            'unit'              => $unit,
            'unit_cost'         => $unitCost,
            'total_cost'        => $totalCost,
            'previous_quantity' => $previousQty,
            'new_quantity'      => $newQty,
            'purchase_date'     => $request->purchase_date,
            'recorded_by'       => auth()->id(),
        ]);

        // Update inventory — keep the item's current cost price in sync with the latest purchase
        $itemUpdate = ['quantity' => $newQty];
        if ($unitCost !== null) {
            $itemUpdate['cost_price'] = $unitCost;
        }
        $item->update($itemUpdate);

        $costNote = $unitCost !== null ? " at ₱{$unitCost}/{$unit} (₱{$totalCost} total)" : '';

        return back()->with('success', "Stock-in recorded: +{$purchased} {$unit} of {$item->item_name}{$costNote}. New total: {$newQty} {$unit}.");
    }
}
