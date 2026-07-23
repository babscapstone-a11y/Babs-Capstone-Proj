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
        $rtcProducts   = MenuItem::rtcTracked()->orderBy('menu_name')->get();
        $beverageItems = InventoryItem::beverage()->orderBy('item_name')->get();

        $recentStockIns   = PurchaseOrder::with(['inventoryItem', 'recorder'])
            ->latest()->limit(5)->get();
        $recentConversions = ConversionLog::with(['inventoryItem', 'menuItem', 'converter'])
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
        $query = MenuItem::rtcTracked();

        if ($search = $request->input('search')) {
            $query->where('menu_name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            match($status) {
                'low_stock'    => $query->rtcLowStock(),
                'out_of_stock' => $query->rtcOutOfStock(),
                'available'    => $query->where('rtc_servings', '>', 10),
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

        return view('inventory.rtc-inventory', compact('items', 'totalRtc', 'lowServings', 'outOfServings', 'totalServings', 'rawItems', 'menuItems'));
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
        $validated = $request->validate([
            'item_name'       => ['required', 'string', 'max:255'],
            'item_type'       => ['required', 'in:rtc,beverage'],
            'unit'            => ['required', 'in:Gram,Kilogram,Piece,Box,Case'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
        ]);

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
        ]);

        $item->update($request->only([
            'unit', 'category', 'supplier', 'cost_price',
            'min_stock_level', 'reorder_level',
            'portion_size', 'portion_unit',
        ]));

        return back()->with('success', "{$item->item_name} updated successfully.");
    }
}
