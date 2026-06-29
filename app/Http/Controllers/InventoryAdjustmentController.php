<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryAdjustment::with(['inventoryItem', 'adjuster']);

        if ($search = $request->input('q')) {
            $query->whereHas('inventoryItem', fn ($q) => $q->where('item_name', 'like', "%{$search}%"));
        }
        if ($type = $request->input('type')) {
            $query->where('adjustment_type', $type);
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $adjustments   = $query->latest()->paginate(15)->withQueryString();
        $beverageItems = InventoryItem::beverage()->orderBy('item_name')->get();
        $rtcItems      = InventoryItem::rtc()->orderBy('item_name')->get();

        return view('inventory.adjustments', compact('adjustments', 'beverageItems', 'rtcItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'adjustment_type'   => ['required', 'in:damaged,expired,missing,correction'],
            'quantity_adjusted' => ['required', 'numeric'],
            'reason'            => ['required', 'string', 'max:255'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $item   = InventoryItem::findOrFail($request->inventory_item_id);
        $before = (float) $item->quantity;
        $adj    = (float) $request->quantity_adjusted;
        $after  = max(0, $before + $adj);

        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'adjustment_type'   => $request->adjustment_type,
            'quantity_before'   => $before,
            'quantity_adjusted' => $adj,
            'quantity_after'    => $after,
            'reason'            => $request->reason,
            'remarks'           => $request->remarks,
            'adjusted_by'       => auth()->id(),
        ]);

        $item->update(['quantity' => $after]);

        $sign = $adj >= 0 ? '+' : '';
        return redirect()->route('inventory.adjustments.index')
            ->with('success', "Adjustment recorded: {$sign}{$adj} {$item->unit} on {$item->item_name}. New quantity: {$after}.");
    }
}
