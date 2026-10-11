<?php

namespace App\Http\Controllers;

use App\Models\ConversionLog;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $totalRtc         = InventoryItem::rtc()->count();
        $totalRtcProducts = MenuItem::rtcTracked()->count();
        $totalBeverage    = InventoryItem::beverage()->count();
        $lowStock         = InventoryItem::lowStock()->count();
        $outOfStock       = InventoryItem::outOfStock()->count();

        $rtcItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $beverageItems = InventoryItem::beverage()->orderBy('item_name')->get();

        // Latest 5 of each; the View All pages list everything 10 per page
        $recentStockIns    = PurchaseOrder::with(['inventoryItem', 'recorder'])->latest()->limit(5)->get();
        $recentConversions = ConversionLog::with(['inventoryItem', 'menuItem', 'converter'])->latest()->limit(5)->get();
        $recentAdjustments = InventoryAdjustment::with(['inventoryItem', 'menuItem', 'adjuster'])->latest()->limit(5)->get();

        return view('inventory.index', compact(
            'totalRtc', 'totalRtcProducts', 'totalBeverage', 'lowStock', 'outOfStock',
            'rtcItems', 'beverageItems',
            'recentStockIns', 'recentConversions', 'recentAdjustments'
        ));
    }

    public function rtc(Request $request): View|JsonResponse
    {
        $query = InventoryItem::rtc();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity >= reorder_level'),
                default        => null,
            };
        }

        $items = $query->orderBy('category')->orderBy('item_name')->get();

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
        $query = MenuItem::rtcTracked();

        if ($search = $request->input('search')) {
            $query->where('menu_name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->rtcLowStock(),
                'out_of_stock' => $query->rtcOutOfStock(),
                'available'    => $query->where('rtc_servings', '>=', MenuItem::LOW_SERVINGS),
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
        $totalServings = MenuItem::rtcTracked()->sum('rtc_servings');
        $rawItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $menuItems     = MenuItem::food()->orderBy('menu_name')->get();

        // Adjust Stock on this page corrects a dish's RTC servings
        $adjustOptions = MenuItem::rtcTracked()->orderBy('menu_name')->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->menu_name, 'qty' => (float) $m->rtc_servings, 'unit' => 'srv'])->all();

        return view('inventory.rtc-inventory', compact('items', 'totalRtc', 'lowServings', 'outOfServings', 'totalServings', 'rawItems', 'menuItems', 'adjustOptions'));
    }

    public function beverages(Request $request): View|JsonResponse
    {
        $query = InventoryItem::beverage();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity >= reorder_level'),
                default        => null,
            };
        }

        $items      = $query->orderBy('category')->orderBy('item_name')->get();

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

    public function restocking(Request $request): View
    {
        $statusFilter = in_array($request->input('status'), ['low_stock', 'out_of_stock'])
            ? $request->input('status')
            : null;

        $outOfStockItems = InventoryItem::outOfStock()
            ->orderBy('item_type')->orderBy('category')->orderBy('item_name')->get();

        $lowStockItems = InventoryItem::lowStock()
            ->orderBy('item_type')->orderBy('category')->orderBy('item_name')->get();

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
            'min_stock_level' => ['required', 'numeric', 'min:0'],
            'pieces_per_pack' => ['nullable', 'required_if:unit,Case,Box', 'integer', 'min:2', 'max:999'],
        ], [
            'pieces_per_pack.required_if' => 'Please enter how many pieces are in one :value.',
        ]);

        // A beverage bought by the Case/Box is counted per piece (so single damaged bottles can
        // be deducted); the pack and its size are kept so Stock-In can convert packs into pieces.
        $byPack = $validated['item_type'] === 'beverage' && in_array($validated['unit'], InventoryItem::PACK_UNITS, true);
        $validated['pack_unit']       = $byPack ? $validated['unit'] : null;
        $validated['pieces_per_pack'] = $byPack ? $validated['pieces_per_pack'] : null;
        $validated['unit']            = $byPack ? 'Piece' : $validated['unit'];

        $item = InventoryItem::create([
            ...$validated,
            'quantity'       => 0,
            'reorder_level'  => $validated['min_stock_level'],
            'cost_price'     => 0,
            'is_rtc'         => $validated['item_type'] === 'rtc',
            'is_active'      => true,
        ]);

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
            'category'        => ['nullable', 'string', 'max:100'],
            'supplier'        => ['nullable', 'string', 'max:255'],
            'cost_price'      => ['nullable', 'numeric', 'min:0'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
            'reorder_level'   => ['required', 'numeric', 'min:0'],
            'portion_size'    => ['nullable', 'numeric', 'min:0.001'],
            'portion_unit'    => ['nullable', 'string', 'max:50'],
            'pack_unit'       => ['nullable', Rule::in(InventoryItem::PACK_UNITS)],
            'pieces_per_pack' => ['nullable', 'required_with:pack_unit', 'integer', 'min:2', 'max:999'],
        ], [
            'pieces_per_pack.required_with' => 'Please enter how many pieces are in one pack.',
        ]);

        $item->update($request->only([
            'unit', 'category', 'supplier', 'cost_price',
            'min_stock_level', 'reorder_level',
            'portion_size', 'portion_unit',
        ]));

        // Beverages: the pack it arrives in (Case/Box) and its size; "Piece only" clears both
        if ($item->isBeverage() && $request->has('pack_unit')) {
            $item->update([
                'pack_unit'       => $request->pack_unit ?: null,
                'pieces_per_pack' => $request->pack_unit ? $request->pieces_per_pack : null,
            ]);
        }

        return back()->with('success', "{$item->item_name} updated successfully.");
    }

    /** Items for the shared Adjust Stock modal (inventory.partials.adjust-modal). */
    private static function stockAdjustOptions($query): array
    {
        return $query->orderBy('item_name')->get()
            ->map(fn ($i) => ['id' => $i->id, 'name' => $i->item_name, 'qty' => (float) $i->quantity, 'unit' => \App\Support\Units::short($i->unit)])->all();
    }
}
