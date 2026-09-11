<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGcashIntentRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Requests\StoreSpecialDiscountRequestRequest;
use App\Models\Discount;
use App\Models\GcashPaymentIntent;
use App\Models\Invoice;
use App\Models\ModeOfPayment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentStatus;
use App\Models\SpecialDiscountRequest;
use App\Services\PaymongoClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function __construct(private PaymongoClient $paymongo)
    {
    }

    /**
     * GET /cashier/billing — billing screen shell. Order data is fetched
     * client-side via orders()/showOrder(), same pattern as the KDS board.
     */
    public function billing(Request $request): View
    {
        return view('cashier.billing', [
            'preselectedOrderId' => $request->query('order'),
        ]);
    }

    /**
     * GET /cashier/orders — REQ096: search unpaid orders, from the moment
     * they're placed (not just once kitchen-ready — see scopeVisibleForBilling).
     */
    public function orders(Request $request): JsonResponse
    {
        $query = Order::visibleForBilling()
            ->with(['orderStatus', 'customer', 'dineInOrder', 'onlineOrder', 'details']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('dineInOrder', function ($dq) use ($search) {
                      $dq->where('table_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($type = $request->input('type')) {
            $query->where('order_type', $type);
        }

        $orders = $query->orderByDesc('created_at')->get()
            ->map(fn (Order $order) => $this->serializeOrderSummary($order));

        return response()->json(['orders' => $orders]);
    }

    /**
     * GET /cashier/orders/{order} — REQ097: full order + item breakdown for the billing screen.
     */
    public function showOrder(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['orderStatus', 'customer', 'dineInOrder', 'onlineOrder', 'details.menuItem']);

        return response()->json(['order' => $this->serializeOrderDetail($order)]);
    }

    /**
     * GET /cashier/discounts — REQ099: active discount rules the cashier may select.
     */
    public function discounts(): JsonResponse
    {
        $discounts = Discount::active()->orderBy('discount_name')->get()
            ->filter(fn (Discount $d) => $d->isCurrentlyValid())
            ->map(fn (Discount $d) => [
                'id'                    => $d->id,
                'name'                  => $d->discount_name,
                'type'                  => $d->discount_type,
                'is_special'            => $d->isSpecial(),
                'value'                 => (float) $d->discount_value,
                'formatted_value'       => $d->formatted_value,
                'eligibility_label'     => $d->eligibility_label,
                'requires_verification' => $d->requiresEligibilityVerification(),
                'minimum_purchase'      => $d->minimum_purchase !== null ? (float) $d->minimum_purchase : null,
                'maximum_discount'      => $d->maximum_discount !== null ? (float) $d->maximum_discount : null,
            ])->values();

        return response()->json(['discounts' => $discounts]);
    }

    /**
     * GET /cashier/orders/{order}/special-discount-request — the billing
     * screen polls this after submitting a Special Discount amount so it
     * knows the moment an admin approves or rejects it, without the cashier
     * having to refresh the page.
     */
    public function specialDiscountRequestStatus(Order $order): JsonResponse
    {
        $this->authorize('pay', $order);

        $latest = $order->specialDiscountRequests()->latest()->first();

        return response()->json(['request' => $this->serializeSpecialDiscountRequest($latest)]);
    }

    /**
     * POST /cashier/orders/{order}/special-discount-request — REQ: a cashier
     * types a custom amount for a "Special Discount" catalog entry; it sits
     * pending until an admin approves it in the Discounts module before
     * resolveDiscount() (and thus processPayment()/createGcashIntent()) will
     * let it be deducted from the bill.
     */
    public function requestSpecialDiscount(StoreSpecialDiscountRequestRequest $request, Order $order): JsonResponse
    {
        $this->authorize('pay', $order);

        $discount = Discount::find($request->discount_id);

        if (! $discount || ! $discount->isSpecial() || ! $discount->isCurrentlyValid()) {
            return response()->json(['message' => 'The selected special discount is not available.'], 422);
        }

        if ($order->specialDiscountRequests()->pending()->exists()) {
            return response()->json(['message' => 'A special discount request for this order is already awaiting admin approval.'], 422);
        }

        $order->load('details');
        $subtotal = (float) $order->details->sum('subtotal');
        $amount   = round((float) $request->amount, 2);

        if ($amount > $subtotal) {
            return response()->json(['message' => 'The requested amount cannot exceed the order subtotal.'], 422);
        }

        if ($discount->maximum_discount !== null && $amount > (float) $discount->maximum_discount) {
            return response()->json(['message' => 'The requested amount exceeds the maximum allowed for this discount (₱' . number_format($discount->maximum_discount, 2) . ').'], 422);
        }

        if (! $discount->meetsMinimumPurchase($subtotal)) {
            return response()->json(['message' => 'This order does not meet the minimum purchase required for this discount.'], 422);
        }

        $specialDiscountRequest = SpecialDiscountRequest::create([
            'request_number'   => SpecialDiscountRequest::generateRequestNumber(),
            'order_id'         => $order->id,
            'discount_id'      => $discount->id,
            'cashier_id'       => auth()->id(),
            'requested_amount' => $amount,
            'reason'           => $request->reason,
        ]);

        return response()->json([
            'message' => 'Special discount request submitted. Waiting for admin approval.',
            'request' => $this->serializeSpecialDiscountRequest($specialDiscountRequest),
        ]);
    }

    /**
     * DELETE /cashier/orders/{order}/special-discount-request/{specialDiscountRequest}
     * — lets the cashier withdraw their own still-pending request (e.g. they
     * mistyped the amount) instead of waiting for an admin to reject it.
     */
    public function cancelSpecialDiscountRequest(Order $order, SpecialDiscountRequest $specialDiscountRequest): JsonResponse
    {
        $this->authorize('pay', $order);

        if ($specialDiscountRequest->order_id !== $order->id) {
            abort(404);
        }

        if ($specialDiscountRequest->isPending()) {
            $specialDiscountRequest->delete();
        }

        return response()->json(['status' => 'cancelled']);
    }

    /**
     * POST /cashier/orders/{order}/payment — REQ098–REQ101: validate, apply
     * discount, verify tendered cash, and record the completed transaction.
     */
    public function processPayment(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('pay', $order);

        $order->load('details');
        $subtotal = (float) $order->details->sum('subtotal');

        ['discount' => $discount, 'discountAmount' => $discountAmount, 'error' => $error]
            = $this->resolveDiscount($request->discount_id, $subtotal, $order);

        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $serviceCharge = round((float) ($request->service_charge ?? 0), 2);
        $grandTotal    = round(max($subtotal - $discountAmount + $serviceCharge, 0), 2);

        $isCash         = $request->payment_method === 'cash';
        $amountReceived = $isCash ? round((float) $request->amount_received, 2) : $grandTotal;

        if ($isCash && $amountReceived < $grandTotal) {
            return response()->json(['message' => 'Insufficient payment amount.'], 422);
        }

        $changeAmount = round($amountReceived - $grandTotal, 2);

        $payment = $this->finalizeOrderPayment(
            $order, $discount, $subtotal, $discountAmount, $serviceCharge,
            $grandTotal, $request->payment_method, $amountReceived, $changeAmount,
            $request->reference_number
        );

        if (! $payment) {
            return response()->json(['message' => 'This order has already been paid or is not ready for billing.'], 409);
        }

        return response()->json([
            'message'     => 'Payment completed successfully.',
            'payment_id'  => $payment->id,
            'receipt_url' => route('cashier.receipts.show', $payment),
        ]);
    }

    /**
     * POST /cashier/orders/{order}/gcash-intent — cashier-initiated GCash
     * payment: quotes the bill (same discount/service-charge rules as a
     * cash sale) and starts a PayMongo GCash Payment Intent. The returned
     * checkout URL is rendered as a QR code on the billing screen for the
     * customer's own phone to scan — the billing screen never sees GCash
     * credentials, only the redirect URL.
     */
    public function createGcashIntent(CreateGcashIntentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('pay', $order);

        $order->load('details');
        $subtotal = (float) $order->details->sum('subtotal');

        ['discount' => $discount, 'discountAmount' => $discountAmount, 'error' => $error]
            = $this->resolveDiscount($request->discount_id, $subtotal, $order);

        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $serviceCharge = round((float) ($request->service_charge ?? 0), 2);
        $grandTotal    = round(max($subtotal - $discountAmount + $serviceCharge, 0), 2);

        if ($grandTotal <= 0) {
            return response()->json(['message' => 'The payable amount must be greater than zero.'], 422);
        }

        $intent = GcashPaymentIntent::create([
            'order_id'        => $order->id,
            'cashier_id'      => auth()->id(),
            'discount_id'     => $discount?->id,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'service_charge'  => $serviceCharge,
            'grand_total'     => $grandTotal,
            'status'          => 'pending',
        ]);

        $method = config('services.paymongo.cashier_qr_method', 'gcash');

        try {
            $paymongoIntent = $this->paymongo->createPaymentIntent(
                (int) round($grandTotal * 100),
                "Order {$order->order_number} — Cashier {$method} Payment",
                [$method]
            );

            // PayMongo requires a billing email on the payment method even for
            // walk-in orders with no linked customer account, so fall back to
            // a synthetic (never-emailed) address tied to the order number.
            $paymentMethod = $this->paymongo->createPaymentMethod($method, array_filter([
                'name'  => $order->customer_name,
                'email' => $order->customer?->email ?? "order-{$order->order_number}@babsresto.com",
                'phone' => $order->customer?->contact_no,
            ]));

            $attached = $this->paymongo->attachPaymentMethod(
                $paymongoIntent['id'],
                $paymentMethod['id'],
                route('cashier.gcash.return', $intent)
            );

            $nextAction = $this->paymongo->extractNextAction($attached);

            $intent->update([
                'paymongo_payment_intent_id' => $paymongoIntent['id'],
                'paymongo_payment_method_id' => $paymentMethod['id'],
                'paymongo_checkout_url'      => $nextAction['value'],
                'next_action_type'           => $nextAction['type'],
            ]);

            return response()->json([
                'intent_id'        => $intent->id,
                'checkout_url'     => $nextAction['value'],
                'next_action_type' => $nextAction['type'],
                'grand_total'      => $grandTotal,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayMongo cashier GCash intent failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            $intent->update(['status' => 'failed']);

            return response()->json([
                'message' => 'We could not start GCash payment right now. Please try again or use cash instead.',
            ], 502);
        }
    }

    /**
     * GET /cashier/gcash-intents/{gcashPaymentIntent}/status — polled by the
     * billing screen while the QR is displayed. The first time PayMongo
     * reports the payment as succeeded, this finalizes the sale (creates
     * the Invoice/Payment) exactly like a cash sale would.
     */
    public function gcashIntentStatus(GcashPaymentIntent $intent): JsonResponse
    {
        $this->authorize('pay', $intent->order);

        if ($intent->status === 'paid') {
            return response()->json([
                'status'      => 'paid',
                'receipt_url' => route('cashier.receipts.show', $intent->payment_id),
            ]);
        }

        if ($intent->status !== 'pending') {
            return response()->json(['status' => $intent->status]);
        }

        // QR Ph codes stop working after 30 minutes, but PayMongo doesn't
        // reliably reflect that on the Payment Intent's own status — this
        // is tracked off our own created_at instead. Distinct 'expired' so
        // the cashier gets a "generate a new one" prompt, not a generic
        // failure message.
        if ($intent->isExpired()) {
            $intent->update(['status' => 'failed']);

            return response()->json(['status' => 'expired']);
        }

        try {
            $paymongoIntent = $this->paymongo->retrievePaymentIntent($intent->paymongo_payment_intent_id);
            $resolved = $this->paymongo->interpretIntentStatus($paymongoIntent);

            if ($resolved === 'succeeded') {
                return response()->json($this->finalizeGcashIntent($intent, $paymongoIntent));
            }

            if ($resolved === 'failed') {
                $intent->update(['status' => 'failed']);

                return response()->json(['status' => 'failed']);
            }

            // Still awaiting the customer to act — keep polling.
            return response()->json(['status' => 'pending']);
        } catch (\Throwable $e) {
            Log::error('PayMongo cashier GCash status check failed', [
                'intent_id' => $intent->id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json(['status' => 'pending']);
        }
    }

    /**
     * POST /cashier/gcash-intents/{gcashPaymentIntent}/cancel — lets the
     * cashier abandon a still-pending QR (customer changed their mind, or
     * couldn't complete it) and fall back to another payment method.
     */
    public function cancelGcashIntent(GcashPaymentIntent $intent): JsonResponse
    {
        $this->authorize('pay', $intent->order);

        if ($intent->status === 'pending') {
            $intent->update(['status' => 'cancelled']);
        }

        return response()->json(['status' => $intent->status]);
    }

    /**
     * GET /cashier/gcash/return/{gcashPaymentIntent} — PayMongo redirects the
     * *customer's own phone* here after they authorize (or cancel) in GCash.
     * This device has no cashier session, so it just shows a friendly
     * "you may return this device" message; it also opportunistically
     * finalizes the sale immediately (instead of waiting for the next poll
     * tick on the cashier's screen) since we're already looking at the
     * payment's status right here.
     */
    public function gcashReturn(GcashPaymentIntent $intent): View
    {
        if ($intent->status === 'pending' && $intent->isExpired()) {
            $intent->update(['status' => 'failed']);
        } elseif ($intent->status === 'pending') {
            try {
                $paymongoIntent = $this->paymongo->retrievePaymentIntent($intent->paymongo_payment_intent_id);
                $resolved = $this->paymongo->interpretIntentStatus($paymongoIntent);

                if ($resolved === 'succeeded') {
                    $this->finalizeGcashIntent($intent, $paymongoIntent);
                } elseif ($resolved === 'failed') {
                    $intent->update(['status' => 'failed']);
                }
            } catch (\Throwable $e) {
                Log::error('PayMongo cashier GCash return check failed', [
                    'intent_id' => $intent->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return view('cashier.gcash-return', ['intent' => $intent->fresh()]);
    }

    /**
     * Finalizes a GcashPaymentIntent once PayMongo confirms the payment
     * succeeded: creates the Invoice/Payment (reusing the same path a cash
     * sale takes) and links it back onto the intent. Safe to call more than
     * once for the same intent — only the first call does anything.
     */
    private function finalizeGcashIntent(GcashPaymentIntent $intent, array $paymongoIntent): array
    {
        $intent = GcashPaymentIntent::whereKey($intent->id)->lockForUpdate()->firstOrFail();

        if ($intent->status === 'paid') {
            return ['status' => 'paid', 'receipt_url' => route('cashier.receipts.show', $intent->payment_id)];
        }

        $referenceNumber = $paymongoIntent['attributes']['payments'][0]['id'] ?? $intent->paymongo_payment_intent_id;

        $payment = $this->finalizeOrderPayment(
            $intent->order,
            $intent->discount,
            (float) $intent->subtotal,
            (float) $intent->discount_amount,
            (float) $intent->service_charge,
            (float) $intent->grand_total,
            'cashless',
            (float) $intent->grand_total,
            0.0,
            $referenceNumber
        );

        if (! $payment) {
            $intent->update(['status' => 'failed']);

            return ['status' => 'failed'];
        }

        $intent->update(['status' => 'paid', 'payment_id' => $payment->id]);

        return ['status' => 'paid', 'receipt_url' => route('cashier.receipts.show', $payment)];
    }

    /**
     * GET /cashier/receipts/{payment} — REQ102: printable receipt.
     */
    public function receipt(Payment $payment): View
    {
        $payment->load([
            'order.details', 'order.customer', 'order.dineInOrder', 'order.onlineOrder',
            'invoice.discount', 'cashier.staff', 'modeOfPayment',
        ]);

        return view('cashier.receipt', compact('payment'));
    }

    /* ── Shared billing helpers ── */

    /**
     * Validates a candidate discount against the order's subtotal, mirroring
     * the checks previously inlined in processPayment() — shared with
     * createGcashIntent() so both payment paths enforce the same rules.
     */
    private function resolveDiscount(?int $discountId, float $subtotal, Order $order): array
    {
        if (! $discountId) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => null];
        }

        $discount = Discount::findOrFail($discountId);

        if (! $discount->isCurrentlyValid()) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => 'The selected discount is no longer active or has expired.'];
        }

        if (! $discount->meetsMinimumPurchase($subtotal)) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => 'This order does not meet the minimum purchase required for the selected discount.'];
        }

        if ($discount->isSpecial()) {
            return $this->resolveSpecialDiscount($discount, $order, $subtotal);
        }

        return ['discount' => $discount, 'discountAmount' => $discount->computeDiscountAmount($subtotal), 'error' => null];
    }

    /**
     * A 'special' discount has no preset value, so instead of computing an
     * amount from discount_value it looks up this order's latest
     * SpecialDiscountRequest — the amount only counts once an admin has
     * approved it, which keeps a cashier from finalizing a bill with an
     * unapproved custom deduction.
     */
    private function resolveSpecialDiscount(Discount $discount, Order $order, float $subtotal): array
    {
        $latest = $order->specialDiscountRequests()
            ->where('discount_id', $discount->id)
            ->latest()
            ->first();

        if (! $latest) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => 'Please request an amount for this special discount before completing payment.'];
        }

        if ($latest->isPending()) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => 'This special discount is still awaiting admin approval.'];
        }

        if ($latest->isRejected()) {
            return ['discount' => null, 'discountAmount' => 0.0, 'error' => 'The admin rejected this special discount request. Please request a new amount.'];
        }

        $amount = min((float) $latest->requested_amount, $subtotal);

        return ['discount' => $discount, 'discountAmount' => round($amount, 2), 'error' => null];
    }

    private function serializeSpecialDiscountRequest(?SpecialDiscountRequest $request): ?array
    {
        if (! $request) {
            return null;
        }

        return [
            'id'                => $request->id,
            'discount_id'       => $request->discount_id,
            'request_number'    => $request->request_number,
            'requested_amount'  => (float) $request->requested_amount,
            'status'            => $request->review_status,
            'rejection_reason'  => $request->rejection_reason,
        ];
    }

    /**
     * Records the completed sale (Invoice + Payment) and marks the order
     * paid — the shared endpoint for both a cash sale and a confirmed GCash
     * sale. The row is re-locked and re-checked inside the transaction to
     * guard against a double submit racing this same order past the policy
     * check.
     */
    private function finalizeOrderPayment(
        Order $order,
        ?Discount $discount,
        float $subtotal,
        float $discountAmount,
        float $serviceCharge,
        float $grandTotal,
        string $paymentMethod,
        float $amountReceived,
        float $changeAmount,
        ?string $referenceNumber
    ): ?Payment {
        return DB::transaction(function () use (
            $order, $discount, $subtotal, $discountAmount,
            $serviceCharge, $grandTotal, $paymentMethod, $amountReceived, $changeAmount, $referenceNumber
        ) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isAwaitingPayment()) {
                return null;
            }

            $paidStatusId    = PaymentStatus::where('status_name', 'Paid')->value('id');
            $modeOfPaymentId = ModeOfPayment::where('method_name', $paymentMethod === 'cash' ? 'Cash' : 'Cashless')->value('id');

            $invoice = Invoice::create([
                'order_id'          => $locked->id,
                'discount_id'       => $discount?->id,
                'payment_status_id' => $paidStatusId,
                'subtotal'          => $subtotal,
                'discount_amount'   => $discountAmount,
                'service_charge'    => $serviceCharge,
                'final_total'       => $grandTotal,
            ]);

            $payment = Payment::create([
                'invoice_id'         => $invoice->id,
                'order_id'           => $locked->id,
                'cashier_id'         => auth()->id(),
                'mode_of_payment_id' => $modeOfPaymentId,
                'amount_paid'        => $grandTotal,
                'amount_received'    => $amountReceived,
                'change_amount'      => $changeAmount,
                'reference_number'   => $referenceNumber,
                'transaction_number' => Payment::generateTransactionNumber(),
                'receipt_number'     => Payment::generateReceiptNumber(),
                'payment_date'       => now(),
            ]);

            // Deliberately leaves order_status_id untouched: payment can happen
            // whenever the customer is ready to pay — before the food server
            // has served the order (still "Ready") or after (already
            // "Served"/"Packaged") — and shouldn't force either path along.
            // isFullyClosed()/isAwaitingPayment() key off payment_status
            // directly, not a status row, so the service pipeline and the
            // payment pipeline stay independent of each other.
            $locked->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
            ]);

            return $payment;
        });
    }

    /* ── Serialization ── */

    private function serializeOrderSummary(Order $order): array
    {
        return [
            'id'                => $order->id,
            'order_number'      => $order->order_number,
            'customer_name'     => $order->customer_name,
            'order_type'        => $order->order_type,
            'order_type_label'  => $order->order_type_label,
            'table_number'      => $order->dineInOrder?->table_number,
            'created_at'        => $order->created_at?->toIso8601String(),
            'status_label'      => $order->status_name,
            'status_color'      => $order->status_color,
            'is_awaiting_payment' => $order->isAwaitingPayment(),
            'payment_status_label' => $order->payment_status_label,
            'item_count'        => $order->item_count,
            'total_amount'      => (float) $order->details->sum('subtotal'),
        ];
    }

    private function serializeOrderDetail(Order $order): array
    {
        $subtotal = (float) $order->details->sum('subtotal');

        return [
            'id'                   => $order->id,
            'order_number'         => $order->order_number,
            'customer_name'        => $order->customer_name,
            'order_type'           => $order->order_type,
            'order_type_label'     => $order->order_type_label,
            'table_number'         => $order->dineInOrder?->table_number,
            'delivery_address'     => $order->onlineOrder?->delivery_address,
            'created_at'           => $order->created_at?->toIso8601String(),
            'status_label'         => $order->status_name,
            'status_color'         => $order->status_color,
            'is_awaiting_payment'  => $order->isAwaitingPayment(),
            'special_instructions' => $order->special_instructions,
            'item_count'           => $order->item_count,
            'subtotal'             => $subtotal,
            'items' => $order->details->map(fn ($d) => [
                'name'      => $d->item_name,
                'image_url' => $d->menuItem?->image_url,
                'quantity'  => $d->quantity,
                'price'     => (float) $d->price,
                'subtotal'  => (float) $d->subtotal,
            ])->values(),
        ];
    }
}
