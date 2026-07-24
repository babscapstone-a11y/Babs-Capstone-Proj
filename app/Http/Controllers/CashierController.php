<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\ModeOfPayment;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashierController extends Controller
{
    /**
     * GET /cashier — Payment Dashboard: KPI cards, recent transactions, quick search.
     */
    public function dashboard(): View
    {
        $pendingPayments = Order::awaitingPayment()->count();

        $completedToday = Payment::whereDate('payment_date', today())->count();
        $dailySales     = (float) Payment::whereDate('payment_date', today())->sum('amount_paid');

        $recentTransactions = Payment::with(['order.customer', 'order.dineInOrder', 'cashier.staff'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        return view('cashier.dashboard', [
            'pendingPayments'     => $pendingPayments,
            'completedToday'      => $completedToday,
            'dailySales'          => $dailySales,
            'totalRevenueToday'   => $dailySales,
            'recentTransactions'  => $recentTransactions,
        ]);
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
     * GET /cashier/orders — REQ096: search unpaid, kitchen-ready orders.
     */
    public function orders(Request $request): JsonResponse
    {
        $query = Order::awaitingPayment()
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
        $this->authorize('pay', $order);

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
     * POST /cashier/orders/{order}/payment — REQ098–REQ101: validate, apply
     * discount, verify tendered cash, and record the completed transaction.
     */
    public function processPayment(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        $this->authorize('pay', $order);

        $order->load('details');
        $subtotal = (float) $order->details->sum('subtotal');

        $discount       = null;
        $discountAmount = 0.0;

        if ($request->discount_id) {
            $discount = Discount::findOrFail($request->discount_id);

            if (! $discount->isCurrentlyValid()) {
                return response()->json(['message' => 'The selected discount is no longer active or has expired.'], 422);
            }
            if (! $discount->meetsMinimumPurchase($subtotal)) {
                return response()->json(['message' => 'This order does not meet the minimum purchase required for the selected discount.'], 422);
            }

            $discountAmount = $discount->computeDiscountAmount($subtotal);
        }

        $serviceCharge = round((float) ($request->service_charge ?? 0), 2);
        $grandTotal    = round(max($subtotal - $discountAmount + $serviceCharge, 0), 2);

        $isCash         = $request->payment_method === 'cash';
        $amountReceived = $isCash ? round((float) $request->amount_received, 2) : $grandTotal;

        if ($isCash && $amountReceived < $grandTotal) {
            return response()->json(['message' => 'Insufficient payment amount.'], 422);
        }

        $changeAmount = round($amountReceived - $grandTotal, 2);

        // The row is re-locked and re-checked inside the transaction to guard
        // against a double submit racing this same order past the policy check.
        $payment = DB::transaction(function () use (
            $order, $request, $discount, $subtotal, $discountAmount,
            $serviceCharge, $grandTotal, $isCash, $amountReceived, $changeAmount
        ) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isAwaitingPayment()) {
                return null;
            }

            $paidStatusId      = PaymentStatus::where('status_name', 'Paid')->value('id');
            $modeOfPaymentId   = ModeOfPayment::where('method_name', $isCash ? 'Cash' : 'Cashless')->value('id');
            $completedStatusId = OrderStatus::where('status_name', 'Completed')->value('id');

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
                'reference_number'   => $request->reference_number,
                'transaction_number' => Payment::generateTransactionNumber(),
                'receipt_number'     => Payment::generateReceiptNumber(),
                'payment_date'       => now(),
            ]);

            $locked->update([
                'payment_status'  => 'paid',
                'payment_method'  => $request->payment_method,
                'order_status_id' => $completedStatusId ?? $locked->order_status_id,
            ]);

            return $payment;
        });

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
