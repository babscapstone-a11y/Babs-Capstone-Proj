<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\MenuItemRtc;
use App\Models\RtcConversion;
use App\Models\StockIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        // The item counts are shown on the tabs (inventory/partials/tabs)
        $rtcItems     = InventoryItem::rtc()->orderBy('item_name')->get();
        $beverageItems = InventoryItem::beverage()->with('packConversion')->orderBy('item_name')->get();

        // Latest 5 of each; the View All pages list everything 10 per page
        $recentStockIns    = StockIn::with(['inventoryItem', 'recorder'])->latest()->limit(5)->get();
        $recentConversions = RtcConversion::with(['details.inventoryItem', 'menuItem', 'converter'])->latest()->latest('id')->limit(5)->get();
        $recentAdjustments = InventoryAdjustment::with(['inventoryItem', 'menuItem', 'adjuster'])->latest()->limit(5)->get();

        return view('inventory.index', compact(
            'rtcItems', 'beverageItems',
            'recentStockIns', 'recentConversions', 'recentAdjustments'
        ));
    }

    public function rtc(Request $request): View|JsonResponse
    {
        $query = InventoryItem::rtc();

        if ($search = $request->input('search')) {
            $query->where('item_name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity >= reorder_level'),
                default        => null,
            };
        }

        $items = $query->orderBy('item_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('inventory._rtc-results', compact('items'))->render(),
                'count' => $items->count(),
            ]);
        }

        $totalRtc   = InventoryItem::rtc()->count();
        $lowStock   = InventoryItem::rtc()->lowStock()->count();
        $outOfStock = InventoryItem::rtc()->outOfStock()->count();

        $adjustOptions = self::stockAdjustOptions(InventoryItem::rtc());

        return view('inventory.rtc', compact('items', 'totalRtc', 'lowStock', 'outOfStock', 'adjustOptions'));
    }

    public function rtcInventory(Request $request): View|JsonResponse
    {
        $query = MenuItem::rtcTracked()->with('rtcStock.inventoryItem');

        if ($search = $request->input('search')) {
            $query->where('menu_name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->rtcLowStock(),
                'out_of_stock' => $query->rtcOutOfStock(),
                'available'    => $query->rtcAvailable(),
                default        => null,
            };
        }

        $items = $query->orderBy('menu_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('inventory._rtc-inventory-results', compact('items'))->render(),
                'count' => $items->count(),
            ]);
        }

        $totalRtc      = MenuItem::rtcTracked()->count();
        $lowServings   = MenuItem::rtcTracked()->rtcLowStock()->count();
        $outOfServings = MenuItem::rtcTracked()->rtcOutOfStock()->count();
        $totalServings = MenuItemRtc::sum('servings');
        $rawItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $menuItems     = MenuItem::food()->orderBy('menu_name')->get();

        // Adjust Stock on this page corrects a dish's RTC servings
        $adjustOptions = MenuItem::rtcTracked()->with('rtcStock')->orderBy('menu_name')->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->menu_name, 'qty' => (float) $m->rtc_servings, 'unit' => 'srv'])->all();

        return view('inventory.rtc-inventory', compact('items', 'totalRtc', 'lowServings', 'outOfServings', 'totalServings', 'rawItems', 'menuItems', 'adjustOptions'));
    }

    public function beverages(Request $request): View|JsonResponse
    {
        $query = InventoryItem::beverage()->with('packConversion');

        if ($search = $request->input('search')) {
            $query->where('item_name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity >= reorder_level'),
                default        => null,
            };
        }

        $items      = $query->orderBy('item_name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('inventory._beverages-results', compact('items'))->render(),
                'count' => $items->count(),
            ]);
        }

        $totalBev   = InventoryItem::beverage()->count();
        $lowStock   = InventoryItem::beverage()->lowStock()->count();
        $outOfStock = InventoryItem::beverage()->outOfStock()->count();

        $adjustOptions = self::stockAdjustOptions(InventoryItem::beverage());

        return view('inventory.beverages', compact('items', 'totalBev', 'lowStock', 'outOfStock', 'adjustOptions'));
    }

    public function restocking(Request $request): View|JsonResponse
    {
        $statusFilter = in_array($request->input('status'), ['low_stock', 'out_of_stock'])
            ? $request->input('status')
            : null;

        // The Low Stock / Out of Stock tabs: one searchable table, laid out like the Beverages page
        if ($statusFilter) {
            $base  = fn () => $statusFilter === 'low_stock' ? InventoryItem::lowStock() : InventoryItem::outOfStock();
            $query = $base()->with('packConversion');

            if ($search = $request->input('search')) {
                $query->where('item_name', 'like', "%{$search}%");
            }
            if (in_array($type = $request->input('type'), ['rtc', 'beverage'], true)) {
                $query->where('item_type', $type);
            }

            $items = $query->orderBy('item_type')->orderBy('item_name')->get();

            if ($request->ajax()) {
                return response()->json([
                    'html'  => view('inventory._restocking-results', compact('items', 'statusFilter'))->render(),
                    'count' => $items->count(),
                ]);
            }

            $totalCount    = $base()->count();
            $rawMeatCount  = $base()->where('item_type', 'rtc')->count();
            $beverageCount = $base()->where('item_type', 'beverage')->count();

            return view('inventory.restocking', compact('items', 'statusFilter', 'totalCount', 'rawMeatCount', 'beverageCount'));
        }

        $outOfStockItems = InventoryItem::outOfStock()
            ->orderBy('item_type')->orderBy('item_name')->get();

        $lowStockItems = InventoryItem::lowStock()
            ->orderBy('item_type')->orderBy('item_name')->get();

        return view('inventory.restocking', compact('outOfStockItems', 'lowStockItems', 'statusFilter'));
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedUnits = $request->input('item_type') === 'beverage'
            ? ['Box', 'Piece', 'Case']
            : ['Gram', 'Kilogram'];

        // Tidy the name first ("  chicken   breast " → "chicken breast") so spacing can't sneak past the duplicate check
        $request->merge([
            'item_name' => preg_replace('/\s+/', ' ', trim((string) $request->input('item_name'))),
        ]);

        $validated = $request->validate([
            'item_name'       => [
                'required', 'string', 'max:255',
                // No duplicates regardless of capitalization: "chicken" blocks "Chicken" and "CHICKEN"
                function (string $attribute, mixed $value, \Closure $fail) {
                    $existing = InventoryItem::whereRaw('LOWER(item_name) = ?', [mb_strtolower($value)])->first();
                    if ($existing) {
                        $fail("\"{$existing->item_name}\" is already in the inventory. Use the existing item instead of adding a duplicate.");
                    }
                },
            ],
            'item_type'       => ['required', 'in:rtc,beverage'],
            'unit'            => ['required', Rule::in($allowedUnits)],
            'reorder_level'   => ['required', 'numeric', 'min:0'],
            'pieces_per_pack' => ['nullable', 'required_if:unit,Case,Box', 'integer', 'min:2', 'max:999'],
        ], [
            'pieces_per_pack.required_if' => 'Please enter how many pieces are in one :value.',
        ]);

        // A beverage bought by the Case/Box is counted per piece (so single damaged bottles can
        // be deducted); the pack and its size are kept so Stock-In can convert packs into pieces.
        $byPack = $validated['item_type'] === 'beverage' && in_array($validated['unit'], InventoryItem::PACK_UNITS, true);

        $item = DB::transaction(function () use ($validated, $byPack) {
            $item = InventoryItem::create([
                'item_name'     => $validated['item_name'],
                'item_type'     => $validated['item_type'],
                'unit'          => $byPack ? 'Piece' : $validated['unit'],
                'quantity'      => 0,
                'reorder_level' => $validated['reorder_level'],
                'cost_price'    => 0,
                'is_active'     => true,
            ]);

            if ($byPack) {
                $item->setPack($validated['unit'], (int) $validated['pieces_per_pack']);
            }

            return $item;
        });

        return back()->with('success', "\"{$item->item_name}\" has been added. Set its unit and stock in the Edit page, then record its opening stock via Stock In.");
    }

    public function edit(InventoryItem $item): View
    {
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $request->validate([
            'unit'            => ['required', 'string', 'max:50'],
            'cost_price'      => ['nullable', 'numeric', 'min:0'],
            'reorder_level'   => ['required', 'numeric', 'min:0'],
            'pack_unit'       => ['nullable', Rule::in(InventoryItem::PACK_UNITS)],
            'pieces_per_pack' => ['nullable', 'required_with:pack_unit', 'integer', 'min:2', 'max:999'],
        ], [
            'pieces_per_pack.required_with' => 'Please enter how many pieces are in one pack.',
        ]);

        DB::transaction(function () use ($request, $item) {
            $item->update($request->only(['unit', 'cost_price', 'reorder_level']));

            // Beverages: the pack it arrives in (Case/Box) and its size; "Piece only" removes it
            if ($item->isBeverage() && $request->has('pack_unit')) {
                $item->setPack($request->pack_unit ?: null, $request->pack_unit ? (int) $request->pieces_per_pack : null);
            }
        });

        return back()->with('success', "{$item->item_name} updated successfully.");
    }

    /** Items for the shared Adjust Stock modal (inventory.partials.adjust-modal). */
    private static function stockAdjustOptions($query): array
    {
        return $query->orderBy('item_name')->get()
            ->map(fn ($i) => ['id' => $i->id, 'name' => $i->item_name, 'qty' => (float) $i->quantity, 'unit' => \App\Support\Units::short($i->unit)])->all();
    }
}
