<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Support\Units;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryAdjustment::with(['inventoryItem', 'menuItem', 'adjuster']);

        if ($search = $request->input('q')) {
            $query->where(fn ($w) => $w
                ->whereHas('inventoryItem', fn ($q) => $q->where('item_name', 'like', "%{$search}%"))
                ->orWhereHas('menuItem', fn ($q) => $q->where('menu_name', 'like', "%{$search}%")));
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

    /**
     * Records a stock deduction on either a stock item's quantity (raw meat / beverage —
     * inventory_item_id) or a menu item's RTC servings (menu_item_id), then returns to the
     * page it came from. Adjustments only ever remove stock (adding stock is Stock-In or an
     * RTC conversion); the chosen adjustment type is the reason, with optional remarks.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'inventory_item_id' => ['required_without:menu_item_id', 'nullable', 'exists:inventory_items,id'],
            'menu_item_id'      => ['required_without:inventory_item_id', 'nullable', 'exists:menu_items,id'],
            'adjustment_type'   => ['required', 'in:damaged,expired,missing,correction'],
            'quantity_deducted' => ['required', 'numeric', 'gt:0'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ], [
            'adjustment_type.required'  => 'Please choose a reason.',
            'quantity_deducted.required' => 'Please enter a quantity to deduct.',
            'quantity_deducted.gt'       => 'Please enter a quantity to deduct greater than 0.',
        ]);

        $deduct = (float) $request->quantity_deducted;

        if ($request->filled('menu_item_id')) {
            $menuItem = MenuItem::findOrFail($request->menu_item_id);

            if (! $menuItem->isRtcTracked()) {
                return back()->with('error', "{$menuItem->menu_name} isn't tracked by RTC servings.");
            }
            if (floor($deduct) != $deduct) {
                return back()->with('error', 'RTC servings can only be deducted in whole numbers.');
            }

            return $this->deduct($request, MenuItem::class, $menuItem->id, 'rtc_servings', 'menu_item_id',
                $deduct, $menuItem->menu_name, 'serving(s)');
        }

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        return $this->deduct($request, InventoryItem::class, $item->id, 'quantity', 'inventory_item_id',
            $deduct, $item->item_name, Units::short($item->unit));
    }

    /** Locks the row, refuses to go below zero, then saves the record and the new stock. */
    private function deduct(Request $request, string $model, int $id, string $column, string $key,
                            float $deduct, string $name, string $unit): RedirectResponse
    {
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');

        $after = DB::transaction(function () use ($request, $model, $id, $column, $key, $deduct) {
            $locked = $model::whereKey($id)->lockForUpdate()->first();
            $before = (float) $locked->{$column};

            if ($deduct > $before + 1e-9) {
                return null;
            }
            $after = round($before - $deduct, 4);

            InventoryAdjustment::create([
                $key                => $locked->id,
                'adjustment_type'   => $request->adjustment_type,
                'quantity_before'   => $before,
                'quantity_adjusted' => -$deduct,
                'quantity_after'    => $after,
                // The chosen type is the reason (e.g. "Damaged"); details go in remarks
                'reason'            => (new InventoryAdjustment(['adjustment_type' => $request->adjustment_type]))->getTypeLabel(),
                'remarks'           => $request->remarks,
                'adjusted_by'       => auth()->id(),
            ]);
            $locked->update([$column => $after]);

            return $after;
        });

        if ($after === null) {
            return back()->with('error', "You can't deduct more than what's in stock for {$name}.");
        }

        return back()->with('success', 'Adjustment recorded: -' . $fmt($deduct) . " {$unit} from {$name}. New stock: " . $fmt($after) . " {$unit}.");
    }
}
