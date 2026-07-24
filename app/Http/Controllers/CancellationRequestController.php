<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectCancellationRequestRequest;
use App\Models\CancellationRequest;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CancellationRequestController extends Controller
{
    /**
     * GET /cancellations — REQ047: review queue for all customer
     * cancellation requests. Returns the full page on a normal visit, or
     * just the results partial (+ refreshed summary counters) on an XHR
     * search/filter request.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', CancellationRequest::class);

        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->input('status')
            : null;

        $query = CancellationRequest::with(['order.orderStatus', 'customer']);

        if ($status) {
            $query->where('review_status', $status);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $cancellationRequests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('cancellations._results', compact('cancellationRequests'))->render(),
                'stats' => $this->summaryCounts(),
            ]);
        }

        return view('cancellations.index', array_merge(
            compact('cancellationRequests', 'status'),
            $this->summaryCounts()
        ));
    }

    /**
     * GET /cancellations/{cancellationRequest} — REQ048: full detail review page.
     */
    public function show(CancellationRequest $cancellationRequest): View
    {
        $this->authorize('view', $cancellationRequest);

        $cancellationRequest->load(['customer', 'reviewedBy', 'order.details.menuItem', 'order.orderStatus']);

        $order = $cancellationRequest->order;

        return view('cancellations.show', compact('cancellationRequest', 'order'));
    }

    /**
     * PUT /cancellations/{cancellationRequest}/approve — REQ049.
     */
    public function approve(CancellationRequest $cancellationRequest): RedirectResponse
    {
        $this->authorize('decide', $cancellationRequest);

        $cancelledStatus = OrderStatus::where('status_name', 'Cancelled')->first();

        $approved = DB::transaction(function () use ($cancellationRequest, $cancelledStatus) {
            $lockedRequest = CancellationRequest::whereKey($cancellationRequest->id)->lockForUpdate()->firstOrFail();
            $lockedOrder   = Order::whereKey($lockedRequest->order_id)->lockForUpdate()->firstOrFail();

            if (! $lockedRequest->isPending() || ! $lockedOrder->isCancellationEligible()) {
                return null;
            }

            // Inventory note: this system does not auto-reserve/deduct stock
            // when an order is placed — stock only moves through the manual
            // Stock-In, Conversion, and Adjustment modules — so there is no
            // reserved quantity to restore here for an order that never left
            // the "Pending" intake queue.
            $lockedOrder->update([
                'order_status_id'     => $cancelledStatus?->id ?? $lockedOrder->order_status_id,
                'cancelled_at'        => now(),
                'cancellation_reason' => $lockedRequest->cancellation_reason,
            ]);

            $lockedRequest->update([
                'review_status' => 'approved',
                'reviewed_by'   => auth()->id(),
                'review_date'   => now(),
            ]);

            return $lockedRequest;
        });

        if (! $approved) {
            return back()->with('error', 'This request can no longer be approved — the order has already progressed past the Order Received stage or was already reviewed.');
        }

        return back()->with('success', 'Cancellation request approved successfully.');
    }

    /**
     * PUT /cancellations/{cancellationRequest}/reject — REQ050.
     */
    public function reject(RejectCancellationRequestRequest $request, CancellationRequest $cancellationRequest): RedirectResponse
    {
        $this->authorize('decide', $cancellationRequest);

        $rejected = DB::transaction(function () use ($request, $cancellationRequest) {
            $lockedRequest = CancellationRequest::whereKey($cancellationRequest->id)->lockForUpdate()->firstOrFail();

            if (! $lockedRequest->isPending()) {
                return null;
            }

            $lockedRequest->update([
                'review_status'     => 'rejected',
                'reviewed_by'       => auth()->id(),
                'review_date'       => now(),
                'rejection_reason'  => $request->rejection_reason,
            ]);

            return $lockedRequest;
        });

        if (! $rejected) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        return back()->with('success', 'Cancellation request rejected.');
    }

    private function summaryCounts(): array
    {
        return [
            'pendingCount'  => CancellationRequest::pending()->count(),
            'approvedCount' => CancellationRequest::approved()->count(),
            'rejectedCount' => CancellationRequest::rejected()->count(),
        ];
    }
}
