<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ProcurementOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProcurementOrder::with('preparedBy');

        if ($search = $request->input('q')) {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $orders         = $query->latest()->paginate(10)->withQueryString();
        $draftCount     = ProcurementOrder::where('status', 'draft')->count();
        $finalizedCount = ProcurementOrder::where('status', 'finalized')->count();
        $lowStockCount  = InventoryItem::lowStock()->count();
        $outOfStockCount= InventoryItem::outOfStock()->count();

        $needsRestocking = InventoryItem::outOfStock()
            ->union(InventoryItem::lowStock())
            ->orderBy('item_type')
            ->orderBy('item_name')
            ->limit(8)
            ->get();

        $recentOrders = ProcurementOrder::with('preparedBy')->latest()->limit(5)->get();

        return view('purchase-orders.index', compact(
            'orders', 'draftCount', 'finalizedCount',
            'lowStockCount', 'outOfStockCount',
            'needsRestocking', 'recentOrders'
        ));
    }

    public function generate(): RedirectResponse
    {
        $outOfStock = InventoryItem::outOfStock()
            ->orderBy('item_type')->orderBy('item_name')->get();
        $lowStock = InventoryItem::lowStock()
            ->orderBy('item_type')->orderBy('item_name')->get();

        $items = $outOfStock->merge($lowStock)->unique('id');

        if ($items->isEmpty()) {
            return redirect()->route('purchase-orders.index')
                ->with('info', 'All inventory levels are sufficient. No items need restocking at this time.');
        }

        $po = ProcurementOrder::create([
            'po_number'   => ProcurementOrder::generatePoNumber(),
            'status'      => 'draft',
            'prepared_by' => auth()->id(),
            'total_items' => $items->count(),
        ]);

        foreach ($items as $item) {
            $suggested = max((float) $item->suggested_restock, 1);
            $po->items()->create([
                'inventory_item_id'    => $item->id,
                'item_name'            => $item->item_name,
                'category'             => $item->category,
                'item_type'            => $item->item_type,
                'current_stock'        => $item->quantity,
                'threshold'            => $item->reorder_level,
                'quantity_recommended' => $suggested,
                'quantity_to_purchase' => $suggested,
                'unit'                 => $item->unit,
                'stock_status'         => $item->stock_status,
            ]);
        }

        return redirect()->route('purchase-orders.edit', $po)
            ->with('success', "Draft Purchase Order {$po->po_number} generated successfully with {$items->count()} item(s). Review and edit quantities before finalizing.");
    }

    public function show(ProcurementOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['items.inventoryItem', 'preparedBy']);
        return view('purchase-orders.show', ['po' => $purchaseOrder]);
    }

    public function edit(ProcurementOrder $purchaseOrder): View|RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('info', 'This purchase order is already finalized. You can only view or print it.');
        }
        $purchaseOrder->load(['items.inventoryItem', 'preparedBy']);
        return view('purchase-orders.edit', ['po' => $purchaseOrder]);
    }

    public function update(Request $request, ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'Finalized purchase orders cannot be modified.');
        }

        $request->validate([
            'notes'        => ['nullable', 'string', 'max:1000'],
            'quantities'   => ['required', 'array'],
            'quantities.*' => ['required', 'numeric', 'min:0.01'],
        ]);

        $purchaseOrder->update(['notes' => $request->notes]);

        foreach ($request->quantities as $itemId => $qty) {
            $purchaseOrder->items()->where('id', $itemId)->update([
                'quantity_to_purchase' => (float) $qty,
            ]);
        }

        return back()->with('success', 'Purchase order quantities updated successfully.');
    }

    public function finalize(ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'This purchase order is already finalized.');
        }
        if ($purchaseOrder->items()->count() === 0) {
            return back()->with('error', 'Cannot finalize an empty purchase order. Add items first.');
        }

        $purchaseOrder->update([
            'status'       => 'finalized',
            'finalized_at' => now(),
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} has been finalized successfully. You can now print it.");
    }

    public function print(ProcurementOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['items', 'preparedBy']);
        return view('purchase-orders.print', ['po' => $purchaseOrder]);
    }

    public function destroy(ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'Finalized purchase orders cannot be deleted.');
        }

        $num = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', "Draft purchase order {$num} has been deleted.");
    }
}
