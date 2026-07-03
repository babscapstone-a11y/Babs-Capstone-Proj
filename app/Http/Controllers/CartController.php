<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private function getOrCreateCart(): Cart
    {
        return Cart::firstOrCreate(
            ['customer_id' => auth('customer')->id(), 'status' => 'active']
        );
    }

    private function activeCart(): ?Cart
    {
        return Cart::where('customer_id', auth('customer')->id())
            ->where('status', 'active')
            ->with(['items.menuItem.category'])
            ->first();
    }

    public function index(Request $request): JsonResponse|View
    {
        $cart = $this->activeCart();

        if ($request->wantsJson()) {
            if (! $cart) {
                return response()->json(['items' => [], 'total' => 0, 'count' => 0]);
            }

            $items = $cart->items->map(function ($item) {
                $mi = $item->menuItem;
                return [
                    'id'        => $item->id,
                    'menu_item_id' => $item->menu_item_id,
                    'name'      => $mi->menu_name,
                    'price'     => (float) $item->unit_price,
                    'quantity'  => $item->quantity,
                    'subtotal'  => (float) $item->unit_price * $item->quantity,
                    'image'     => $mi->image_url,
                ];
            });

            return response()->json([
                'items' => $items,
                'total' => $cart->total,
                'count' => $cart->item_count,
            ]);
        }

        return view('cart.index', [
            'cart'      => $cart,
            'cartCount' => $cart?->item_count ?? 0,
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity'     => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $menuItem = MenuItem::where('id', $request->menu_item_id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->firstOrFail();

        $cart = $this->getOrCreateCart();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id'      => $cart->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $request->quantity,
                'unit_price'   => $menuItem->price,
            ]);
        }

        $cart->load('items');

        return response()->json([
            'message' => "{$menuItem->menu_name} added to your cart!",
            'count'   => $cart->item_count,
            'total'   => $cart->total,
        ]);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($cartItem);

        $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);

        $cartItem->update(['quantity' => $request->quantity]);

        $cart = $cartItem->cart->load('items');

        return response()->json([
            'subtotal' => $cartItem->subtotal,
            'total'    => $cart->total,
            'count'    => $cart->item_count,
        ]);
    }

    public function remove(CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($cartItem);

        $cart = $cartItem->cart->load('items');
        $cartItem->delete();
        $cart->load('items');

        return response()->json([
            'total' => $cart->total,
            'count' => $cart->item_count,
        ]);
    }

    public function clear(): JsonResponse
    {
        $cart = $this->activeCart();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['total' => 0, 'count' => 0]);
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        $cart = Cart::where('id', $cartItem->cart_id)
            ->where('customer_id', auth('customer')->id())
            ->firstOrFail();
    }
}
