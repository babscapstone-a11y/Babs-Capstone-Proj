<?php

namespace App\Http\Controllers;

use App\Models\ConversionLog;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\RtcProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $totalRtc         = InventoryItem::rtc()->count();
        $totalRtcProducts = RtcProduct::count();
        $totalBeverage    = InventoryItem::beverage()->count();
        $lowStock         = InventoryItem::lowStock()->count();
        $outOfStock       = InventoryItem::outOfStock()->count();

        $rtcItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $rtcProducts   = RtcProduct::orderBy('name')->get();
        $beverageItems = InventoryItem::beverage()->orderBy('item_name')->get();

        $recentStockIns   = PurchaseOrder::with(['inventoryItem', 'recorder'])
            ->latest()->limit(5)->get();
        $recentConversions = ConversionLog::with(['inventoryItem', 'rtcProduct', 'converter'])
            ->latest()->limit(5)->get();
        $recentAdjustments = InventoryAdjustment::with(['inventoryItem', 'adjuster'])
            ->latest()->limit(5)->get();

        return view('inventory.index', compact(
            'totalRtc', 'totalRtcProducts', 'totalBeverage', 'lowStock', 'outOfStock',
            'rtcItems', 'rtcProducts', 'beverageItems',
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
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity > reorder_level'),
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

        return view('inventory.rtc', compact('items', 'totalRtc', 'lowStock', 'outOfStock'));
    }

    public function rtcInventory(Request $request): View|JsonResponse
    {
        $query = RtcProduct::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->lowStock(),
                'out_of_stock' => $query->outOfStock(),
                'available'    => $query->where('servings', '>', 10),
                default        => null,
            };
        }

        $items = $query->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('inventory._rtc-inventory-results', compact('items'))->render(),
                'count' => $items->count(),
            ]);
        }

        $totalRtc      = RtcProduct::count();
        $lowServings   = RtcProduct::lowStock()->count();
        $outOfServings = RtcProduct::outOfStock()->count();
        $totalServings = RtcProduct::sum('servings');
        $rawItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $rtcNames      = RtcProduct::orderBy('name')->pluck('name');

        return view('inventory.rtc-inventory', compact('items', 'totalRtc', 'lowServings', 'outOfServings', 'totalServings', 'rawItems', 'rtcNames'));
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
                'available'    => $query->where('quantity', '>', 0)->whereRaw('quantity > reorder_level'),
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

        return view('inventory.beverages', compact('items', 'totalBev', 'lowStock', 'outOfStock'));
    }

    public function restocking(): View
    {
        $outOfStockItems = InventoryItem::outOfStock()
            ->orderBy('item_type')->orderBy('category')->orderBy('item_name')->get();

        $lowStockItems = InventoryItem::lowStock()
            ->orderBy('item_type')->orderBy('category')->orderBy('item_name')->get();

        return view('inventory.restocking', compact('outOfStockItems', 'lowStockItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_name'       => ['required', 'string', 'max:255'],
            'item_type'       => ['required', 'in:rtc,beverage'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
        ]);

        $item = InventoryItem::create([
            ...$validated,
            'unit'           => '',
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
        ]);

        $item->update($request->only([
            'unit', 'category', 'supplier', 'cost_price',
            'min_stock_level', 'reorder_level',
            'portion_size', 'portion_unit',
        ]));

        return back()->with('success', "{$item->item_name} updated successfully.");
    }
}
