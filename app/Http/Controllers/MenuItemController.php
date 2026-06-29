<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MenuItem::class);

        $query = MenuItem::with(['category', 'rtcItem']);

        if ($search = $request->input('search')) {
            $query->where('menu_name', 'like', "%{$search}%");
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($itemType = $request->input('item_type')) {
            $query->where('item_type', $itemType);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $menuItems  = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('category_name')->get();

        $totalItems     = MenuItem::count();
        $activeItems    = MenuItem::where('is_active', true)->count();
        $availableItems = MenuItem::where('is_available', true)->where('is_active', true)->count();
        $foodCount      = MenuItem::where('item_type', 'food')->count();
        $beverageCount  = MenuItem::where('item_type', 'beverage')->count();

        return view('menu.index', compact(
            'menuItems', 'categories',
            'totalItems', 'activeItems', 'availableItems', 'foodCount', 'beverageCount'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', MenuItem::class);

        $categories = Category::where('is_active', true)->orderBy('category_name')->get();
        $rtcItems   = InventoryItem::where('is_rtc', true)->orderBy('item_name')->get();

        return view('menu.create', compact('categories', 'rtcItems'));
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $this->authorize('create', MenuItem::class);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        MenuItem::create($data);

        return redirect()->route('menu.index')
            ->with('success', "Menu item \"{$data['menu_name']}\" was added successfully.");
    }

    public function show(MenuItem $menu): View
    {
        $this->authorize('view', $menu);

        $menu->load(['category', 'rtcItem']);

        return view('menu.show', compact('menu'));
    }

    public function edit(MenuItem $menu): View
    {
        $this->authorize('update', $menu);

        $categories = Category::where('is_active', true)->orderBy('category_name')->get();
        $rtcItems   = InventoryItem::where('is_rtc', true)->orderBy('item_name')->get();

        return view('menu.edit', compact('menu', 'categories', 'rtcItems'));
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menu): RedirectResponse
    {
        $this->authorize('update', $menu);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        } else {
            unset($data['image']);
        }

        if (empty($data['rtc_inventory_item_id'])) {
            $data['rtc_inventory_item_id'] = null;
            $data['rtc_quantity']          = null;
            $data['rtc_unit']              = null;
        }

        $menu->update($data);

        return redirect()->route('menu.show', $menu)
            ->with('success', "Menu item \"{$menu->menu_name}\" was updated successfully.");
    }

    public function toggleStatus(MenuItem $menu): RedirectResponse
    {
        $this->authorize('toggleStatus', $menu);

        $menu->update(['is_active' => ! $menu->is_active]);

        $label = $menu->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Menu item \"{$menu->menu_name}\" has been {$label}.");
    }
}
