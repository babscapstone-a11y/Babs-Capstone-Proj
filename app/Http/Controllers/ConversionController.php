<?php

namespace App\Http\Controllers;

use App\Models\ConversionLog;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversionController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConversionLog::with(['inventoryItem', 'converter']);

        if ($search = $request->input('q')) {
            $query->whereHas('inventoryItem', fn ($q) => $q->where('item_name', 'like', "%{$search}%"));
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs     = $query->latest()->paginate(15)->withQueryString();
        $rtcItems = InventoryItem::rtc()->where('quantity', '>', 0)->orderBy('item_name')->get();

        return view('inventory.conversions', compact('logs', 'rtcItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'raw_quantity_used' => ['required', 'numeric', 'min:0.01'],
            'portion_size'      => ['required', 'numeric', 'min:0.001'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        if ($item->item_type !== 'rtc') {
            return back()->withErrors(['inventory_item_id' => 'Only RTC items can be converted.']);
        }

        $rawUsed  = (float) $request->raw_quantity_used;
        $portion  = (float) $request->portion_size;

        if ($rawUsed > (float) $item->quantity) {
            return back()->withErrors(['raw_quantity_used' => "Insufficient raw stock. Available: {$item->quantity} {$item->unit}."]);
        }

        $unitsProduced      = floor($rawUsed / $portion);
        $previousRawStock   = (float) $item->quantity;
        $remainingRawStock  = $previousRawStock - $rawUsed;
        $previousServings   = (float) $item->rtc_servings;
        $newServings        = $previousServings + $unitsProduced;

        ConversionLog::create([
            'inventory_item_id'  => $item->id,
            'raw_quantity_used'  => $rawUsed,
            'unit'               => $item->unit,
            'portion_size'       => $portion,
            'rtc_units_produced' => $unitsProduced,
            'previous_raw_stock' => $previousRawStock,
            'remaining_raw_stock'=> $remainingRawStock,
            'previous_rtc_servings' => $previousServings,
            'new_rtc_servings'   => $newServings,
            'converted_by'       => auth()->id(),
            'remarks'            => $request->remarks,
        ]);

        $item->update([
            'quantity'     => $remainingRawStock,
            'rtc_servings' => $newServings,
        ]);

        return redirect()->route('inventory.conversions.index')
            ->with('success', "Converted {$rawUsed} {$item->unit} of {$item->item_name} → {$unitsProduced} RTC servings.");
    }
}
