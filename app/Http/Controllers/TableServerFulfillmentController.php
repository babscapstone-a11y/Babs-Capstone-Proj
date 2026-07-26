<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Module 20 — Order Completion & Service Fulfillment.
 *
 * The Kitchen's job ends at "Ready"; from there the food server takes over,
 * matches the order to its table via the Table Card Number, hands the food
 * off, and confirms it — the last operational step before the dining
 * experience (or take-out handoff) is complete.
 */
class TableServerFulfillmentController extends Controller
{
    /**
     * GET /table-server/service — Ready Orders board shell. Order data is
     * fetched client-side via readyOrders(), same polling pattern as the KDS.
     */
    public function index(): View
    {
        return view('table-server.service.index');
    }

    /**
     * GET /table-server/service/orders — JSON poll endpoint feeding the
     * board's cards, summary counts, and search/filter.
     */
    public function readyOrders(): JsonResponse
    {
        $orders = Order::visibleForFulfillment()
            ->with(['orderStatus', 'customer', 'dineInOrder', 'details', 'servedBy'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'orders' => $orders->map(fn (Order $order) => $this->serializeOrder($order)),
            'summary' => [
                'ready_to_serve'   => $orders->where('fulfillment_status', 'Ready')->count(),
                'served_today'     => $orders->where('status_name', 'Served')->count(),
                'ready_for_pickup' => $orders->where('status_name', 'Packaged')->count(),
                'avg_serving_minutes' => $this->averageServingMinutes(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /table-server/service/{order} — the Service Fulfillment detail page.
     */
    public function show(Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        if (! in_array($order->fulfillment_status, ['Ready', 'Served', 'Packaged'], true)) {
            return redirect()->route('table-server.service.index')
                ->with('error', "Order #{$order->order_number} is no longer in the fulfillment queue.");
        }

        $order->load(['orderStatus', 'customer', 'dineInOrder', 'details', 'servedBy', 'placedBy']);

        return view('table-server.service.show', ['order' => $order]);
    }

    /**
     * POST /table-server/service/{order}/serve — REQ093 (dine-in).
     */
    public function serve(Order $order): JsonResponse
    {
        $this->authorize('serve', $order);

        $servedStatus = OrderStatus::where('status_name', 'Served')->firstOrFail();

        $order->update([
            'order_status_id' => $servedStatus->id,
            'served_by'        => auth()->id(),
            'served_at'        => now(),
        ]);

        return response()->json([
            'message' => 'Order successfully served.',
            'order'   => $this->serializeOrder($order->refresh()->load(['orderStatus', 'customer', 'dineInOrder', 'details', 'servedBy'])),
        ]);
    }

    /**
     * POST /table-server/service/{order}/package — take-out/online equivalent of serve().
     */
    public function package(Order $order): JsonResponse
    {
        $this->authorize('package', $order);

        $packagedStatus = OrderStatus::where('status_name', 'Packaged')->firstOrFail();

        $order->update([
            'order_status_id' => $packagedStatus->id,
            'served_by'        => auth()->id(),
            'packaged_at'      => now(),
        ]);

        return response()->json([
            'message' => 'Order successfully packaged and ready for customer pickup.',
            'order'   => $this->serializeOrder($order->refresh()->load(['orderStatus', 'customer', 'dineInOrder', 'details', 'servedBy'])),
        ]);
    }

    /**
     * Average minutes between an order being marked Ready (by the kitchen)
     * and being marked Served/Packaged (by a food server), over today's
     * completed handoffs — the dashboard's "Average Serving Time" KPI. This
     * is deliberately just the handoff leg, not total order age, so it
     * reflects food-server responsiveness rather than kitchen prep time.
     */
    private function averageServingMinutes(): ?float
    {
        $today = Order::whereNotNull('ready_at')
            ->where(function ($q) {
                $q->whereDate('served_at', today())->orWhereDate('packaged_at', today());
            })
            ->get(['ready_at', 'served_at', 'packaged_at']);

        if ($today->isEmpty()) {
            return null;
        }

        $minutes = $today->map(function (Order $order) {
            $handoffAt = $order->served_at ?? $order->packaged_at;
            return $order->ready_at->diffInSeconds($handoffAt) / 60;
        });

        return round($minutes->avg(), 1);
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id'                => $order->id,
            'order_number'      => $order->order_number,
            'customer_name'     => $order->customer?->full_name ?? 'Walk-in',
            'order_type'        => $order->order_type,
            'order_type_label'  => $order->order_type_label,
            'table_number'      => $order->dineInOrder?->table_number,
            // Uses the food-server-facing status (kitchen's internal
            // "Completed" hand-off signal reads as "Ready" here), not the
            // raw order status.
            'status'            => $order->fulfillment_status,
            'status_label'      => $order->fulfillment_status_label,
            'status_color'      => $order->fulfillment_status_color,
            'uses_packaging'    => $order->usesPackagingFlow(),
            'fulfillment_action_label' => $order->fulfillment_action_label,
            'can_be_fulfilled'  => $order->canBeFulfilled(),
            'created_at'        => $order->created_at?->toIso8601String(),
            'ready_at'          => $order->ready_at?->toIso8601String(),
            'served_at'         => $order->served_at?->toIso8601String(),
            'packaged_at'       => $order->packaged_at?->toIso8601String(),
            'served_by_name'    => $order->servedBy ? ($order->servedBy->name ?: $order->servedBy->email) : null,
            'item_count'        => $order->item_count,
            'special_instructions' => $order->special_instructions,
            'items'             => $order->details->map(fn ($d) => [
                'name'     => $d->item_name,
                'quantity' => $d->quantity,
                'notes'    => $d->notes,
            ])->values(),
        ];
    }
}
