<?php

namespace App\Http\Controllers;

use App\Models\ConversionLog;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversionController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConversionLog::with(['inventoryItem', 'menuItem', 'converter']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('inventoryItem', fn ($q) => $q->where('item_name', 'like', "%{$search}%"))
                  ->orWhereHas('menuItem', fn ($q) => $q->where('menu_name', 'like', "%{$search}%"));
            });
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs      = $query->latest()->paginate(15)->withQueryString();
        $rtcItems  = InventoryItem::rtc()->where('quantity', '>', 0)->orderBy('item_name')->get();
        $menuItems = MenuItem::orderBy('menu_name')->get();

        return view('inventory.conversions', compact('logs', 'rtcItems', 'menuItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'menu_item_id'      => ['required', 'exists:menu_items,id'],
            'raw_quantity_used' => ['required', 'numeric', 'min:0.01'],
            'portion_size'      => ['required', 'numeric', 'min:0.001'],
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        if ($item->item_type !== 'rtc') {
            return back()->withErrors(['inventory_item_id' => 'Only raw meat items can be converted.']);
        }

        $rawUsed  = (float) $request->raw_quantity_used;
        $portion  = (float) $request->portion_size;

        if ($rawUsed > (float) $item->quantity) {
            return back()->withErrors(['raw_quantity_used' => "Insufficient raw stock. Available: {$item->quantity} {$item->unit}."]);
        }

        $unitsProduced = floor($rawUsed / $portion);

        if ($unitsProduced < 1) {
            return back()->withErrors(['raw_quantity_used' => "Raw quantity too small to produce at least 1 RTC serving. Need at least {$portion} {$item->unit}."]);
        }

        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        $previousRawStock  = (float) $item->quantity;
        $remainingRawStock = $previousRawStock - $rawUsed;
        $previousServings  = (float) $menuItem->rtc_servings;
        $newServings       = $previousServings + $unitsProduced;

        ConversionLog::create([
            'inventory_item_id'     => $item->id,
            'menu_item_id'          => $menuItem->id,
            'raw_quantity_used'     => $rawUsed,
            'unit'                  => $item->unit,
            'portion_size'          => $portion,
            'rtc_units_produced'    => $unitsProduced,
            'previous_raw_stock'    => $previousRawStock,
            'remaining_raw_stock'   => $remainingRawStock,
            'previous_rtc_servings' => $previousServings,
            'new_rtc_servings'      => $newServings,
            'converted_by'          => auth()->id(),
        ]);

        $item->update(['quantity' => $remainingRawStock]);

        $menuItem->update([
            'rtc_servings'          => $newServings,
            'rtc_inventory_item_id' => $item->id,
            'rtc_quantity'          => $portion,
            'rtc_unit'              => $item->unit,
        ]);

        return redirect()->route('inventory.conversions.index')
            ->with('success', "Converted {$rawUsed} {$item->unit} of {$item->item_name} → {$unitsProduced} servings of \"{$menuItem->menu_name}\" ({$newServings} total).");
    }
}
