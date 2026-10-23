<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectSpecialDiscountRequestRequest;
use App\Models\SpecialDiscountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialDiscountRequestController extends Controller
{
    /**
     * GET /special-discount-requests — admin review queue for cashier
     * special-discount amount requests. Returns the full page on a normal
     * visit, or just the results partial (+ refreshed summary counters) on
     * an XHR search/filter request.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', SpecialDiscountRequest::class);

        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->input('status')
            : null;

        $query = SpecialDiscountRequest::with(['order', 'discount', 'cashier']);

        if ($status) {
            $query->where('review_status', $status);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('cashier', function ($cq) use ($search) {
                      $cq->where('username', 'like', "%{$search}%");
                  });
            });
        }

        $specialDiscountRequests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('special-discount-requests._results', compact('specialDiscountRequests'))->render(),
                'stats' => $this->summaryCounts(),
            ]);
        }

        return view('special-discount-requests.index', array_merge(
            compact('specialDiscountRequests', 'status'),
            $this->summaryCounts()
        ));
    }

    /**
     * GET /special-discount-requests/pending-summary — polled sitewide from
     * the admin layout so a new request surfaces as a toast/badge no matter
     * which admin page is open, without waiting for a full page reload.
     */
    public function pendingSummary(): JsonResponse
    {
        $this->authorize('viewAny', SpecialDiscountRequest::class);

        $pending = SpecialDiscountRequest::pending()
            ->with(['order', 'discount'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (SpecialDiscountRequest $r) => [
                'id'               => $r->id,
                'request_number'   => $r->request_number ?? ('#' . $r->id),
                'order_number'     => $r->order?->order_number,
                'discount_name'    => $r->discount?->discount_name,
                'requested_amount' => (float) $r->requested_amount,
                'show_url'         => route('special-discount-requests.show', $r),
            ])->values();

        return response()->json([
            'count'   => SpecialDiscountRequest::pending()->count(),
            'pending' => $pending,
        ]);
    }

    /**
     * GET /special-discount-requests/{specialDiscountRequest} — full detail review page.
     */
    public function show(SpecialDiscountRequest $specialDiscountRequest): View
    {
        $this->authorize('view', $specialDiscountRequest);

        $specialDiscountRequest->load(['order.details', 'discount', 'cashier', 'reviewedBy']);

        return view('special-discount-requests.show', compact('specialDiscountRequest'));
    }

    /**
     * PUT /special-discount-requests/{specialDiscountRequest}/approve — lets the
     * cashier's billing screen deduct the requested amount from this order.
     */
    public function approve(SpecialDiscountRequest $specialDiscountRequest): RedirectResponse
    {
        $this->authorize('decide', $specialDiscountRequest);

        $specialDiscountRequest->update([
            'review_status' => 'approved',
            'reviewed_by'   => auth()->id(),
            'review_date'   => now(),
        ]);

        return back()->with('success', "Special discount request \"{$specialDiscountRequest->request_number}\" approved.");
    }

    /**
     * PUT /special-discount-requests/{specialDiscountRequest}/reject
     */
    public function reject(RejectSpecialDiscountRequestRequest $request, SpecialDiscountRequest $specialDiscountRequest): RedirectResponse
    {
        $this->authorize('decide', $specialDiscountRequest);

        $specialDiscountRequest->update([
            'review_status'    => 'rejected',
            'reviewed_by'      => auth()->id(),
            'review_date'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Special discount request rejected.');
    }

    private function summaryCounts(): array
    {
        return [
            'pendingCount'  => SpecialDiscountRequest::pending()->count(),
            'approvedCount' => SpecialDiscountRequest::approved()->count(),
            'rejectedCount' => SpecialDiscountRequest::rejected()->count(),
        ];
    }
}
