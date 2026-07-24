<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectOnlineOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnlineOrderController extends Controller
{
    /**
     * GET /cashier/online-orders — REQ103: online pre-order review queue.
     * Returns the full page on a normal visit, or just the results partial
     * (+ refreshed summary counters) on an XHR search/filter request.
     */
    public function index(Request $request): View|JsonResponse
    {
        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected', 'cancelled'], true)
            ? $request->input('status')
            : 'pending';

        $query = Order::onlineOrders()->where('approval_status', $status)
            ->with(['customer', 'paymentProof', 'details']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('contact_no', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('cashier.online-orders._results', compact('orders', 'status'))->render(),
                'stats' => $this->summaryCounts(),
            ]);
        }

        return view('cashier.online-orders.index', array_merge(
            compact('orders', 'status'),
            $this->summaryCounts()
        ));
    }

    /**
     * GET /cashier/online-orders/{order} — REQ104/105: full detail panel.
     */
    public function show(Order $order): View
    {
        $this->authorize('viewOnline', $order);

        $order->load(['customer', 'details.menuItem', 'paymentProof', 'reviewedBy.staff', 'orderStatus']);

        return view('cashier.online-orders.show', compact('order'));
    }

    /**
     * POST /cashier/online-orders/{order}/approve — REQ106: verify the
     * down-payment and forward the order to the Kitchen Display System.
     */
    public function approve(Order $order): JsonResponse
    {
        $this->authorize('decideOnline', $order);

        $approved = DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->needsApproval()) {
                return null;
            }

            $locked->update([
                'approval_status' => 'approved',
                'reviewed_by'     => auth()->id(),
                'reviewed_at'     => now(),
            ]);

            return $locked;
        });

        if (! $approved) {
            return response()->json(['message' => 'This order has already been reviewed.'], 409);
        }

        return response()->json(['message' => 'Online Order Approved Successfully.']);
    }

    /**
     * POST /cashier/online-orders/{order}/reject — REQ105/106: reject the
     * down-payment/order with a reason visible to the customer.
     */
    public function reject(RejectOnlineOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('decideOnline', $order);

        $rejected = DB::transaction(function () use ($request, $order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->needsApproval()) {
                return null;
            }

            $locked->update([
                'approval_status'  => 'rejected',
                'reviewed_by'      => auth()->id(),
                'reviewed_at'      => now(),
                'rejection_reason' => $request->reason,
            ]);

            return $locked;
        });

        if (! $rejected) {
            return response()->json(['message' => 'This order has already been reviewed.'], 409);
        }

        return response()->json(['message' => 'Online order has been rejected.']);
    }

    private function summaryCounts(): array
    {
        return [
            'pendingCount'   => Order::onlineOrders()->where('approval_status', 'pending')->count(),
            'approvedToday'  => Order::onlineOrders()->where('approval_status', 'approved')
                                    ->whereDate('reviewed_at', today())->count(),
            'rejectedToday'  => Order::onlineOrders()->where('approval_status', 'rejected')
                                    ->whereDate('reviewed_at', today())->count(),
            'totalVerified'  => Order::onlineOrders()->where('approval_status', 'approved')->count(),
        ];
    }
}
