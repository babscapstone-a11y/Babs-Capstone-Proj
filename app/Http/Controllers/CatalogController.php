<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->withCount(['menuItems' => fn ($q) => $q->where('is_active', true)->where('is_available', true)])
            ->orderBy('category_name')
            ->get();

        $query = MenuItem::with('category')
            ->where('is_active', true)
            ->where('is_available', true);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('menu_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', fn ($q2) => $q2->where('category_name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $menuItems = $query->orderBy('menu_name')->get();

        // Group by category for section display
        $itemsByCategory = $menuItems->groupBy('category_id');

        // Load active cart for current user
        $cart = Cart::where('user_id', auth()->id())
            ->where('status', 'active')
            ->with(['items.menuItem'])
            ->first();

        $cartCount  = $cart ? $cart->item_count : 0;
        $cartTotal  = $cart ? $cart->total : 0;
        $cartItems  = $cart ? $cart->items : collect();

        return view('customer.catalog', compact(
            'categories', 'menuItems', 'itemsByCategory',
            'cart', 'cartCount', 'cartTotal', 'cartItems'
        ));
    }
}
