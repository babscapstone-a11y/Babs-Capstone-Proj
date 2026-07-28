<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderKitchenStatusRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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

        // Starting to prepare an order is the moment RTC stock actually gets
        // consumed — mirrors the manual raw→RTC conversion pattern, just
        // triggered automatically by this status move instead of a staff form.
        if ($currentStatus === 'Pending' && $requestedStatus === 'Processing') {
            $error = $this->deductRtcStockForOrder($order, $newStatus);

            if ($error) {
                return response()->json(['message' => $error], 422);
            }
        } elseif ($requestedStatus === 'Completed') {
            // "Completed" is the kitchen's hand-off signal to the food server
            // (Module 20) — "Ready" is treated as an internal in-progress
            // step. Stamped separately from created_at so the food-server
            // board can measure "waiting since ready" without including
            // kitchen prep time.
            $order->update(['order_status_id' => $newStatus->id, 'ready_at' => now()]);
        } else {
            $order->update(['order_status_id' => $newStatus->id]);
        }

        $order->refresh()->load(['orderStatus', 'customer', 'dineInOrder', 'details']);

        return response()->json([
            'message' => "Order #{$order->order_number} has been marked as {$order->kitchen_status_label}.",
            'order'   => $this->serializeOrder($order),
        ]);
    }

    /**
     * Sequential reverse-transition whitelist for undoing a misclicked status
     * change — the mirror image of UpdateOrderKitchenStatusRequest::ALLOWED_TRANSITIONS.
     */
    private const REVERT_TRANSITIONS = [
        'Processing' => 'Pending',
        'Ready'      => 'Processing',
        'Completed'  => 'Ready',
    ];

    /**
     * PATCH /kitchen/orders/{order}/revert — steps a misclicked order back one
     * status. Blocked once the food server has already served/packaged it or
     * the cashier has taken payment, since those hand-offs shouldn't be undone
     * from the kitchen board.
     */
    public function revertStatus(Order $order): JsonResponse
    {
        $order->load('orderStatus');
        $currentStatus = $order->status_name;
        $previousStatus = self::REVERT_TRANSITIONS[$currentStatus] ?? null;

        if (! $previousStatus) {
            return response()->json([
                'message' => "Order #{$order->order_number} has no earlier status to revert to.",
            ], 422);
        }

        if ($order->served_at || $order->packaged_at || $order->payment_status === 'paid') {
            return response()->json([
                'message' => "Order #{$order->order_number} has already been handed off / paid and can no longer be reverted.",
            ], 422);
        }

        $previous = OrderStatus::where('status_name', $previousStatus)->firstOrFail();

        if ($currentStatus === 'Processing') {
            // Undoing "Start Preparing" means the RTC stock deducted for it
            // never should have left inventory — give it back.
            $this->restoreRtcStockForOrder($order);
            $order->update(['order_status_id' => $previous->id]);
        } elseif ($currentStatus === 'Completed') {
            // Mirrors the ready_at stamp set when moving into Completed.
            $order->update(['order_status_id' => $previous->id, 'ready_at' => null]);
        } else {
            $order->update(['order_status_id' => $previous->id]);
        }

        $order->refresh()->load(['orderStatus', 'customer', 'dineInOrder', 'details']);

        return response()->json([
            'message' => "Order #{$order->order_number} reverted to {$order->kitchen_status_label}.",
            'order'   => $this->serializeOrder($order),
        ]);
    }

    /**
     * Reverses deductRtcStockForOrder(): gives back rtc_servings for every
     * order detail that was actually deducted (rtc_deducted_at set) and
     * clears that stamp, inside a lock so it can't race a concurrent
     * "start preparing" on the same menu items.
     */
    private function restoreRtcStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $details = $lockedOrder->details()->whereNotNull('rtc_deducted_at')->get();

            if ($details->isEmpty()) {
                return;
            }

            $quantityByMenuItem = $details->groupBy('menu_item_id')
                ->map(fn ($group) => $group->sum('quantity'));

            $menuItems = MenuItem::whereIn('id', $quantityByMenuItem->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantityByMenuItem as $menuItemId => $qty) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem) {
                    continue;
                }

                $menuItem->update(['rtc_servings' => $menuItem->rtc_servings + $qty]);
            }

            $details->each(fn ($detail) => $detail->update(['rtc_deducted_at' => null]));
        });
    }

    /**
     * Locks the order and every RTC-tracked menu item it references, checks
     * each has enough rtc_servings to cover the order's quantities, then
     * deducts them and advances the order status — all inside one
     * transaction so concurrent "start preparing" clicks can't oversell the
     * same servings. Returns an error message on insufficient stock, or
     * null on success.
     */
    private function deductRtcStockForOrder(Order $order, OrderStatus $newStatus): ?string
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $details     = $lockedOrder->details()->get();

            $quantityByMenuItem = $details->groupBy('menu_item_id')
                ->map(fn ($group) => $group->sum('quantity'));

            $menuItems = MenuItem::whereIn('id', $quantityByMenuItem->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantityByMenuItem as $menuItemId => $neededQty) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem || ! $menuItem->isRtcTracked()) {
                    continue;
                }

                if ($neededQty > $menuItem->available_stock) {
                    return "Not enough RTC stock for {$menuItem->menu_name} (need {$neededQty}, have {$menuItem->available_stock}). Convert more stock before starting this order.";
                }
            }

            $now = now();

            foreach ($quantityByMenuItem as $menuItemId => $neededQty) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem || ! $menuItem->isRtcTracked()) {
                    continue;
                }

                $menuItem->update(['rtc_servings' => $menuItem->rtc_servings - $neededQty]);

                $details->where('menu_item_id', $menuItemId)
                    ->each(fn ($detail) => $detail->update(['rtc_deducted_at' => $now]));
            }

            $lockedOrder->update(['order_status_id' => $newStatus->id]);

            return null;
        });
    }

    private function serializeOrder(Order $order): array
    {
        $canRevert = isset(self::REVERT_TRANSITIONS[$order->status_name])
            && ! $order->served_at && ! $order->packaged_at && $order->payment_status !== 'paid';

        $previousStatusLabels = ['Processing' => 'Order Received', 'Ready' => 'Preparing', 'Completed' => 'Ready'];

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
            'pickup_at'             => $order->pickup_at?->toIso8601String(),
            'item_count'            => $order->item_count,
            'estimated_completion'  => $order->estimated_completion?->toIso8601String(),
            'special_instructions'  => $order->special_instructions,
            'can_revert'            => $canRevert,
            'previous_status_label' => $previousStatusLabels[$order->status_name] ?? null,
            'items'                 => $order->details->map(fn ($d) => [
                'name'     => $d->item_name,
                'quantity' => $d->quantity,
                'notes'    => $d->notes,
            ])->values(),
        ];
    }
}
