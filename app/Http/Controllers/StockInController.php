<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Services\StockInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['inventoryItem', 'recorder', 'procurementOrder']);

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

    public function store(Request $request, StockInService $stockIn): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity_purchased' => ['required', 'numeric', 'min:0.01'],
            'unit'              => ['required', 'string', 'max:50'],
            'total_cost'        => ['nullable', 'numeric', 'min:0'],
            'purchase_date'     => ['required', 'date'],
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        $allowedUnits = $stockIn->allowedUnits($item);
        if (! in_array($request->unit, $allowedUnits, true)) {
            return back()->withErrors(['unit' => "Unit must be one of: " . implode(', ', $allowedUnits) . " for {$item->item_name}."]);
        }

        $unit      = $request->unit;
        $purchased = (float) $request->quantity_purchased;
        $totalCost = $request->filled('total_cost') ? (float) $request->total_cost : null;

        $tx = $stockIn->record($item, $purchased, $unit, $totalCost, $request->purchase_date);

        $costNote = $tx->unit_cost !== null ? " at ₱{$tx->unit_cost}/{$unit} (₱{$totalCost} total)" : '';
        $newQty   = (float) $item->quantity;

        return back()->with('success', "Stock-in recorded: +{$purchased} {$unit} of {$item->item_name}{$costNote}. New total: {$newQty} {$item->unit}.");
    }
}
