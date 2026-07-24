<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderKitchenStatusRequest;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class KitchenController extends Controller
{
    /**
     * GET /kitchen — Kanban board shell. Order data is fetched client-side via orders().
     */
    public function index(): View
    {
        return view('kitchen.index');
    }

    /**
     * GET /kitchen/orders — JSON poll endpoint feeding both the board cards and the
     * detail modal (single payload, no separate per-order show endpoint).
     */
    public function orders(): JsonResponse
    {
        $orders = Order::with(['orderStatus', 'customer', 'dineInOrder', 'details'])
            ->where(function ($query) {
                $query->whereHas('orderStatus', function ($q) {
                    $q->whereIn('status_name', ['Pending', 'Processing', 'Ready']);
                })->orWhere(function ($q) {
                    $q->whereHas('orderStatus', fn ($sq) => $sq->where('status_name', 'Completed'))
                      ->whereDate('created_at', today());
                });
            })
            // Online pre-orders stay invisible to the kitchen until a cashier
            // has verified the down-payment and approved them (Module 23).
            ->where(function ($query) {
                $query->where('order_type', '!=', 'online')
                      ->orWhere('approval_status', 'approved');
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order) => $this->serializeOrder($order));

        return response()->json([
            'orders'      => $orders,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * PATCH /kitchen/orders/{order}/status — the only mutation endpoint. Kitchen staff
     * may only move an order through the sequential Pending→Processing→Ready→Completed
     * chain; they cannot edit customer info or ordered items.
     */
    public function updateStatus(UpdateOrderKitchenStatusRequest $request, Order $order): JsonResponse
    {
        $order->load('orderStatus');
        $currentStatus = $order->status_name;
        $requestedStatus = $request->status;

        $allowed = UpdateOrderKitchenStatusRequest::ALLOWED_TRANSITIONS[$currentStatus] ?? null;

        if ($allowed !== $requestedStatus) {
            return response()->json([
                'message' => "Cannot move order #{$order->order_number} from {$currentStatus} to {$requestedStatus}.",
            ], 422);
        }

        $newStatus = OrderStatus::where('status_name', $requestedStatus)->firstOrFail();

        $order->update(['order_status_id' => $newStatus->id]);
        $order->refresh()->load(['orderStatus', 'customer', 'dineInOrder', 'details']);

        return response()->json([
            'message' => "Order #{$order->order_number} has been marked as {$order->kitchen_status_label}.",
            'order'   => $this->serializeOrder($order),
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id'                    => $order->id,
            'order_number'          => $order->order_number,
            'customer_name'         => $order->customer?->full_name ?? 'Walk-in',
            'order_type'            => $order->order_type,
            'order_type_label'      => $order->order_type_label,
            'table_number'          => $order->dineInOrder?->table_number,
            'status'                => $order->status_name,
            'status_label'          => $order->kitchen_status_label,
            'next_action'           => $order->next_kitchen_action,
            'created_at'            => $order->created_at?->toIso8601String(),
            'item_count'            => $order->item_count,
            'estimated_completion'  => $order->estimated_completion?->toIso8601String(),
            'special_instructions'  => $order->special_instructions,
            'items'                 => $order->details->map(fn ($d) => [
                'name'     => $d->item_name,
                'quantity' => $d->quantity,
                'notes'    => $d->notes,
            ])->values(),
        ];
    }
}
