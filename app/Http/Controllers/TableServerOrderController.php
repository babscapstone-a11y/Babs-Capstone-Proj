<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTableServerOrderRequest;
use App\Models\Category;
use App\Models\DineInOrder;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TableServerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->withCount(['menuItems' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('category_name')
            ->get();

        // Unlike CatalogController, unavailable items are NOT filtered out here —
        // the food server needs to see them (disabled) rather than have them vanish.
        $query = MenuItem::with('category')->where('is_active', true);

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
        $itemsByCategory = $menuItems->groupBy('category_id');

        return view('table-server.index', compact('categories', 'menuItems', 'itemsByCategory'));
    }

    public function store(StoreTableServerOrderRequest $request): JsonResponse
    {
        $menuItems = MenuItem::whereIn('id', collect($request->items)->pluck('menu_item_id'))
            ->get()
            ->keyBy('id');

        $total = collect($request->items)->sum(
            fn ($line) => $menuItems[$line['menu_item_id']]->price * $line['quantity']
        );

        $pendingStatus = OrderStatus::where('status_name', 'Pending')->first();

        $order = DB::transaction(function () use ($request, $menuItems, $total, $pendingStatus) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'total_amount'          => $total,
                'customer_id'           => null,
                'placed_by'             => auth()->id(),
                'order_status_id'       => $pendingStatus?->id,
                'order_type'            => 'dine_in',
                'payment_status'        => 'pending',
                'payment_method'        => 'cash',
                'special_instructions'  => $request->special_instructions,
            ]);

            foreach ($request->items as $line) {
                $menuItem = $menuItems[$line['menu_item_id']];

                OrderDetail::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name'    => $menuItem->menu_name,
                    'quantity'     => $line['quantity'],
                    'notes'        => $line['notes'] ?? null,
                    'price'        => $menuItem->price,
                    'subtotal'     => $menuItem->price * $line['quantity'],
                ]);
            }

            DineInOrder::create([
                'order_id'     => $order->id,
                'table_number' => $request->table_number,
            ]);

            return $order;
        });

        return response()->json([
            'message'      => "Order #{$order->order_number} has been submitted successfully.",
            'order_number' => $order->order_number,
        ]);
    }

    public function myOrders(): View
    {
        $orders = Order::where('placed_by', auth()->id())
            ->with(['orderStatus', 'details', 'dineInOrder'])
            ->latest()
            ->paginate(15);

        return view('table-server.orders.index', compact('orders'));
    }
}
