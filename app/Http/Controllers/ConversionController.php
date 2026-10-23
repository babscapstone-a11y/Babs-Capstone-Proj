<?php

namespace App\Http\Controllers;

use App\Models\ConversionLog;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\MenuItemRtc;
use App\Support\Units;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $logs      = $query->latest()->paginate(10)->withQueryString();
        $rtcItems  = InventoryItem::rtc()->where('quantity', '>', 0)->orderBy('item_name')->get();
        $menuItems = MenuItem::food()->orderBy('menu_name')->get();

        return view('inventory.conversions', compact('logs', 'rtcItems', 'menuItems'));
    }

    /**
     * Converts one or more raw meats into RTC servings of a dish. Each meat line has its own raw
     * quantity and portion per serving (kilogram meats: raw in kg, portion in grams; piece meats:
     * both in pieces). The dish gets as many servings as its scarcest meat allows; every line's
     * raw quantity is deducted as entered.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'menu_item_id'              => ['required', 'exists:menu_items,id'],
            'lines'                     => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['required', 'distinct', 'exists:inventory_items,id'],
            'lines.*.raw_quantity'      => ['required', 'numeric', 'min:0.01'],
            'lines.*.portion_size'      => ['required', 'numeric', 'min:0.001'],
        ], [
            'lines.required'                     => 'Add at least one raw meat item.',
            'lines.*.inventory_item_id.required' => 'Select a raw meat item on every row.',
            'lines.*.inventory_item_id.distinct' => 'The same raw meat item is selected more than once.',
            'lines.*.raw_quantity.required'      => 'Enter the raw quantity to use on every row.',
            'lines.*.raw_quantity.min'           => 'Raw quantity to use must be greater than 0.',
            'lines.*.portion_size.required'      => 'Enter the portion size per serving on every row.',
            'lines.*.portion_size.min'           => 'Portion size per serving must be greater than 0.',
        ]);

        $result = DB::transaction(function () use ($request) {
            $menuItem = MenuItem::whereKey($request->menu_item_id)->lockForUpdate()->firstOrFail();
            // Converting stock for a dish means its servings are counted from now on
            $rtc = MenuItemRtc::where('menu_item_id', $menuItem->id)->lockForUpdate()->first()
                ?? $menuItem->rtcStock()->create(['servings' => 0]);
            $lines    = [];

            foreach (array_values($request->input('lines')) as $line) {
                $item = InventoryItem::whereKey($line['inventory_item_id'])->lockForUpdate()->firstOrFail();

                if ($item->item_type !== 'rtc') {
                    return ['error' => "{$item->item_name} is not a raw meat item and can't be converted."];
                }

                $rawUsed = (float) $line['raw_quantity'];
                // Kilogram meats take the portion in grams; store it in the meat's own unit (kg)
                $portion = $item->unit === 'Kilogram' ? (float) $line['portion_size'] / 1000 : (float) $line['portion_size'];

                if ($rawUsed > (float) $item->quantity) {
                    return ['error' => "Insufficient {$item->item_name} stock. Available: " . (float) $item->quantity . ' ' . Units::short($item->unit) . '.'];
                }

                // Small allowance so e.g. 0.3 kg ÷ 0.1 kg counts as 3, not 2.999…
                $servings = (int) floor($rawUsed / $portion + 1e-9);

                if ($servings < 1) {
                    return ['error' => "{$item->item_name}: the raw quantity is too small for even 1 serving at that portion size."];
                }

                $lines[] = compact('item', 'rawUsed', 'portion', 'servings') + ['enteredPortion' => (float) $line['portion_size']];
            }

            // The dish can only be made as many times as its scarcest meat allows
            $unitsProduced    = min(array_column($lines, 'servings'));
            $previousServings = (float) $rtc->servings;
            $newServings      = $previousServings + $unitsProduced;
            $batchCode        = (string) Str::uuid();

            foreach ($lines as $line) {
                $item              = $line['item'];
                $previousRawStock  = (float) $item->quantity;
                $remainingRawStock = $previousRawStock - $line['rawUsed'];

                ConversionLog::create([
                    'batch_code'            => $batchCode,
                    'inventory_item_id'     => $item->id,
                    'menu_item_id'          => $menuItem->id,
                    'raw_quantity_used'     => $line['rawUsed'],
                    'unit'                  => $item->unit,
                    'portion_size'          => $line['portion'],
                    'rtc_units_produced'    => $unitsProduced,
                    'previous_raw_stock'    => $previousRawStock,
                    'remaining_raw_stock'   => $remainingRawStock,
                    'previous_rtc_servings' => $previousServings,
                    'new_rtc_servings'      => $newServings,
                    'converted_by'          => auth()->id(),
                ]);

                $item->update(['quantity' => $remainingRawStock]);
            }

            // The dish remembers its first (main) meat and portion as the default for next time
            $main = $lines[0];
            // (the portion is kept as entered: grams for meat in kilograms, see MenuItemRtc)
            $rtc->update([
                'servings'            => $newServings,
                'inventory_item_id'   => $main['item']->id,
                'portion_per_serving' => $main['enteredPortion'],
            ]);

            return [
                'menuItem' => $menuItem,
                'units'    => $unitsProduced,
                'total'    => $newServings,
                'meats'    => collect($lines)->map(fn ($l) => (float) $l['rawUsed'] . ' ' . Units::short($l['item']->unit) . ' ' . $l['item']->item_name)->implode(', '),
            ];
        });

        if (isset($result['error'])) {
            return back()->withInput()->withErrors(['lines' => $result['error']]);
        }

        return redirect()->route('inventory.conversions.index')
            ->with('success', "Converted {$result['meats']} → {$result['units']} servings of \"{$result['menuItem']->menu_name}\" ({$result['total']} total).");
    }
}
