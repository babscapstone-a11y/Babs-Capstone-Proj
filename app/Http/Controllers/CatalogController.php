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

        $query = MenuItem::with(['category', 'rtcStock', 'beverageStock', 'beverageItem'])
            ->where('is_active', true)
            ->where('is_available', true);

        // No server-side search: the search box filters the cards in the browser as the
        // customer types (see customer/catalog.blade.php), so every item is sent here.

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $menuItems = $query->orderBy('menu_name')->get();

        // Group by category for section display
        $itemsByCategory = $menuItems->groupBy('category_id');

        // Cart badge count only — the catalog page links straight to the
        // cart page rather than rendering cart contents inline.
        $cart = Cart::where('customer_id', auth('customer')->id())->first();

        $cartCount = $cart ? $cart->item_count : 0;

        return view('customer.catalog', compact(
            'categories', 'menuItems', 'itemsByCategory', 'cartCount'
        ));
    }
}
