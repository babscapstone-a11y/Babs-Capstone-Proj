<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Services\StockInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $stockedInCount = ProcurementOrder::where('status', 'stocked_in')->count();
        $lowStockCount  = InventoryItem::lowStock()->count();
        $outOfStockCount= InventoryItem::outOfStock()->count();

        $recentOrders = ProcurementOrder::with('preparedBy')->latest()->limit(5)->get();

        return view('purchase-orders.index', compact(
            'orders', 'draftCount', 'finalizedCount', 'stockedInCount',
            'lowStockCount', 'outOfStockCount', 'recentOrders'
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
            ->with('success', "Draft Purchase Order {$po->po_number} generated successfully with {$items->count()} item(s). Review the items and quantities, then click Save Changes to view and print it.");
    }

    public function show(ProcurementOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['items.inventoryItem', 'preparedBy']);
        return view('purchase-orders.show', ['po' => $purchaseOrder]);
    }

    /**
     * One edit page, two stages of a draft:
     *  - planning  (default)       : adjust items/quantities → Save Changes → view & print
     *  - recording (?mode=record)  : after buying, enter qty bought + amount paid → Save & Finalize
     */
    public function edit(Request $request, ProcurementOrder $purchaseOrder): View|RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('info', 'This purchase order is already finalized. You can only view or print it.');
        }
        $purchaseOrder->load(['items.inventoryItem', 'preparedBy']);

        $availableItems = InventoryItem::where('is_active', true)
            ->whereNotIn('id', $purchaseOrder->items->pluck('inventory_item_id'))
            ->orderBy('item_type')->orderBy('item_name')
            ->get();

        return view('purchase-orders.edit', [
            'po'             => $purchaseOrder,
            'availableItems' => $availableItems,
            'recording'      => $request->query('mode') === 'record',
        ]);
    }

    /** Edit-page URL that keeps the current stage (planning or recording) */
    private function editUrl(ProcurementOrder $po, bool $recording): string
    {
        return route('purchase-orders.edit', $recording ? [$po, 'mode' => 'record'] : $po);
    }

    public function update(Request $request, ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'Finalized purchase orders cannot be modified.');
        }

        $rules = [
            'notes'      => ['nullable', 'string', 'max:1000'],
            'quantities' => ['required', 'array'],
            'received'   => ['nullable', 'array'],
            'paid'       => ['nullable', 'array'],
        ];
        $messages = [];
        foreach ($purchaseOrder->items as $item) {
            $min = max((float) $item->threshold, 0.01);
            $rules["quantities.{$item->id}"] = ['required', 'numeric', "min:{$min}"];
            $rules["received.{$item->id}"]   = ['nullable', 'numeric', 'min:0'];
            $rules["paid.{$item->id}"]       = ['nullable', 'numeric', 'min:0'];
            $messages["quantities.{$item->id}.min"] = "{$item->item_name}: quantity to purchase cannot be below the minimum threshold ({$min} {$item->unit}).";
            $messages["received.{$item->id}.min"]   = "{$item->item_name}: quantity bought cannot be negative.";
            $messages["paid.{$item->id}.min"]       = "{$item->item_name}: amount paid cannot be negative.";
        }

        $request->validate($rules, $messages);

        // Planning stage has no bought/paid inputs, so leave any recorded values untouched there
        $recording = $request->input('mode') === 'record';

        // Notes are only on the planning page; don't clear them when saving from the recording page
        if ($request->has('notes')) {
            $purchaseOrder->update(['notes' => $request->notes]);
        }

        foreach ($purchaseOrder->items as $item) {
            $changes = ['quantity_to_purchase' => (float) $request->input("quantities.{$item->id}")];

            if ($recording) {
                $received = $request->input("received.{$item->id}");
                $paid     = $request->input("paid.{$item->id}");
                $changes['quantity_received'] = $received === null || $received === '' ? null : (float) $received;
                $changes['amount_paid']       = $paid === null || $paid === '' ? null : (float) $paid;
            }

            $item->update($changes);
        }

        // "Save & Finalize" (recording stage only): save first, then run the normal finalize checks
        if ($recording && $request->input('intent') === 'finalize') {
            return $this->finalize($purchaseOrder->fresh());
        }

        if ($recording) {
            return redirect($this->editUrl($purchaseOrder, true))
                ->with('success', 'Purchase recorded. Finalize once every item is filled in.');
        }

        // Planning stage: show the saved PO so it can be printed and taken shopping
        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} saved. Print it and bring it when buying the items.");
    }

    public function addItem(Request $request, ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'Finalized purchase orders cannot be modified.');
        }

        $request->validate([
            'inventory_item_id'    => ['required', 'exists:inventory_items,id'],
            'quantity_to_purchase' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($purchaseOrder->items()->where('inventory_item_id', $request->inventory_item_id)->exists()) {
            return back()->with('error', 'That item is already on this purchase order.')->withInput();
        }

        $item = InventoryItem::findOrFail($request->inventory_item_id);
        $min  = max((float) $item->reorder_level, 0.01);

        if ((float) $request->quantity_to_purchase < $min) {
            return back()
                ->withErrors(['quantity_to_purchase' => "Quantity to purchase cannot be below the minimum threshold ({$min} {$item->unit})."])
                ->withInput();
        }

        $purchaseOrder->items()->create([
            'inventory_item_id'    => $item->id,
            'item_name'            => $item->item_name,
            'category'             => $item->category,
            'item_type'            => $item->item_type,
            'current_stock'        => $item->quantity,
            'threshold'            => $item->reorder_level,
            'quantity_recommended' => (float) $request->quantity_to_purchase,
            'quantity_to_purchase' => (float) $request->quantity_to_purchase,
            'unit'                 => $item->unit,
            'stock_status'         => $item->stock_status,
        ]);

        $purchaseOrder->update(['total_items' => $purchaseOrder->items()->count()]);

        return redirect($this->editUrl($purchaseOrder, $request->query('mode') === 'record'))
            ->with('success', "{$item->item_name} added to the purchase order.");
    }

    public function removeItem(Request $request, ProcurementOrder $purchaseOrder, ProcurementOrderItem $item): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'Finalized purchase orders cannot be modified.');
        }
        if ($item->procurement_order_id !== $purchaseOrder->id) {
            abort(404);
        }

        $name = $item->item_name;
        $item->delete();
        $purchaseOrder->update(['total_items' => $purchaseOrder->items()->count()]);

        return redirect($this->editUrl($purchaseOrder, $request->query('mode') === 'record'))
            ->with('success', "{$name} removed from the purchase order.");
    }

    public function finalize(ProcurementOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isFinalized()) {
            return back()->with('error', 'This purchase order is already finalized.');
        }
        $purchaseOrder->load('items');

        if ($purchaseOrder->items->isEmpty()) {
            return back()->with('error', 'Cannot finalize an empty purchase order. Add items first.');
        }

        // Every item must have its bought quantity and amount paid recorded before finalizing
        $incomplete = $purchaseOrder->incompleteItems();
        if ($incomplete->isNotEmpty()) {
            $names = $incomplete->pluck('item_name')->take(5)->implode(', ');
            $more  = $incomplete->count() > 5 ? ' and ' . ($incomplete->count() - 5) . ' more' : '';

            return redirect($this->editUrl($purchaseOrder, true))
                ->with('error', "Enter the quantity bought and amount paid for every item before finalizing. Missing: {$names}{$more}. (Enter 0 bought if an item was not purchased.)");
        }

        if ($purchaseOrder->items->every(fn ($item) => (float) $item->quantity_received <= 0)) {
            return redirect($this->editUrl($purchaseOrder, true))
                ->with('error', 'Cannot finalize: no items were bought. Enter the quantity bought for at least one item.');
        }

        $purchaseOrder->update([
            'status'       => 'finalized',
            'finalized_at' => now(),
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->po_number} has been finalized. Click \"Record Stock-In\" to add the bought items to inventory.");
    }

    /**
     * Adds every bought item of a finalized PO to inventory in one step,
     * creating a stock-in transaction per item linked back to this PO.
     */
    public function stockIn(ProcurementOrder $purchaseOrder, StockInService $stockIn): RedirectResponse
    {
        $result = DB::transaction(function () use ($purchaseOrder, $stockIn) {
            // Lock the PO row so a double-click cannot stock the same order in twice
            $po = ProcurementOrder::whereKey($purchaseOrder->id)->lockForUpdate()->first();

            if ($po->isStockedIn()) {
                return ['error' => "Purchase Order {$po->po_number} has already been stocked in."];
            }
            if (! $po->awaitingStockIn()) {
                return ['error' => 'Only finalized purchase orders can be stocked in.'];
            }

            $stocked = 0;
            $skipped = [];

            foreach ($po->items()->with('inventoryItem')->get() as $line) {
                $qty = (float) $line->quantity_received;
                if ($qty <= 0) {
                    continue;
                }
                if (! $line->inventoryItem) {
                    $skipped[] = $line->item_name;
                    continue;
                }

                $stockIn->record(
                    $line->inventoryItem,
                    $qty,
                    $line->unit,
                    $line->amount_paid !== null ? (float) $line->amount_paid : null,
                    now()->toDateString(),
                    [
                        'procurement_order_id' => $po->id,
                        'remarks'              => "Stocked in from {$po->po_number}",
                    ],
                );
                $stocked++;
            }

            $po->update([
                'status'        => 'stocked_in',
                'stocked_in_at' => now(),
                'stocked_in_by' => auth()->id(),
            ]);

            return ['po' => $po, 'stocked' => $stocked, 'skipped' => $skipped];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        $message = "{$result['stocked']} item(s) from {$result['po']->po_number} were added to inventory.";
        if ($result['skipped']) {
            $message .= ' Skipped (no longer in inventory): ' . implode(', ', $result['skipped']) . '.';
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $message);
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
