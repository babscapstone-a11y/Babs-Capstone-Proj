<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Cart;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\PaymentProof;
use App\Models\RestaurantDowntime;
use App\Services\PaymongoClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private PaymongoClient $paymongo)
    {
    }

    private function activeCart()
    {
        return Cart::where('customer_id', auth('customer')->id())
            ->where('status', 'active')
            ->with(['items.menuItem'])
            ->first();
    }

    public function index(): View|RedirectResponse
    {
        $cart = $this->activeCart();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Add some items before checking out.');
        }

        if ($downtime = RestaurantDowntime::current()) {
            return redirect()->route('cart.index')
                ->with('error', $downtime->blockedOrderingMessage());
        }

        $customer = auth('customer')->user();

        return view('checkout.index', [
            'cart'      => $cart,
            'customer'  => $customer,
            'cartCount' => $cart->item_count,
        ]);
    }

    /**
     * POST /checkout/paymongo — creates the order + payment record, then
     * kicks off a PayMongo GCash payment and hands the customer a redirect
     * URL to authorize it.
     */
    public function payWithGcash(StoreOrderRequest $request): JsonResponse
    {
        if ($downtime = RestaurantDowntime::current()) {
            return response()->json(['message' => $downtime->blockedOrderingMessage()], 422);
        }

        $cart = $this->activeCart();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty. Add some items before checking out.'], 422);
        }

        $customer = auth('customer')->user();

        if (! $customer) {
            return response()->json(['message' => 'We could not find your customer profile. Please contact support.'], 422);
        }

        // Re-check RTC stock right before placing the order — stock can have
        // changed since items were added to the cart. No reservation happens
        // here (this system only ever deducts stock once kitchen staff move
        // the order to Processing); this is just a courtesy re-check.
        $shortages = [];
        foreach ($cart->items as $item) {
            $menuItem = MenuItem::find($item->menu_item_id);

            if ($menuItem && $menuItem->isRtcTracked() && $item->quantity > $menuItem->available_stock) {
                $shortages[] = "{$menuItem->menu_name} (only {$menuItem->available_stock} left)";
            }
        }

        if ($shortages) {
            return response()->json(['message' => 'Some items in your cart no longer have enough stock: ' . implode(', ', $shortages) . '. Please update your cart.'], 422);
        }

        $pendingStatus = OrderStatus::where('status_name', 'Pending')->first();

        $amount = $request->payment_type === 'full'
            ? (float) $cart->total
            : round(((float) $cart->total) * (Order::HALF_PAYMENT_PERCENT / 100), 2);

        [$order, $paymentProof] = DB::transaction(function () use ($request, $cart, $customer, $pendingStatus, $amount) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'total_amount'          => $cart->total,
                'customer_id'           => $customer->id,
                'order_status_id'       => $pendingStatus?->id,
                'order_type'            => 'online',
                'payment_status'        => 'pending',
                'payment_method'        => 'cashless',
                'special_instructions'  => $request->special_instructions,
                'pickup_at'             => $request->pickup_at,
                'approval_status'       => 'pending',
            ]);

            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $item->menu_item_id,
                    'item_name'    => $item->menuItem->menu_name,
                    'quantity'     => $item->quantity,
                    'notes'        => $item->notes,
                    'price'        => $item->unit_price,
                    'subtotal'     => $item->unit_price * $item->quantity,
                ]);
            }

            $paymentProof = PaymentProof::create([
                'order_id'       => $order->id,
                'customer_id'    => $customer->id,
                'amount'         => $amount,
                'payment_type'   => $request->payment_type,
                'payment_method' => 'gcash',
                'status'         => 'awaiting_payment',
            ]);

            $cart->update(['status' => 'completed']);

            return [$order, $paymentProof];
        });

        try {
            $intent = $this->paymongo->createPaymentIntent(
                (int) round($amount * 100),
                "Order {$order->order_number} — " . ($request->payment_type === 'full' ? 'Full Payment' : 'Half Payment')
            );

            $method = $this->paymongo->createPaymentMethod('gcash', array_filter([
                'name'  => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->contact_no,
            ]));

            $attached = $this->paymongo->attachPaymentMethod(
                $intent['id'],
                $method['id'],
                route('checkout.paymongo.return', $order)
            );

            $redirectUrl = $attached['attributes']['next_action']['redirect']['url'] ?? null;

            if (! $redirectUrl) {
                throw new RuntimeException('PayMongo did not return a redirect URL.');
            }

            $paymentProof->update([
                'paymongo_payment_intent_id' => $intent['id'],
                'paymongo_payment_method_id' => $method['id'],
                'paymongo_checkout_url'      => $redirectUrl,
            ]);

            return response()->json(['redirect_url' => $redirectUrl]);
        } catch (\Throwable $e) {
            Log::error('PayMongo GCash payment setup failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            $paymentProof->update(['status' => 'failed']);

            return response()->json([
                'message' => 'We could not start your GCash payment right now. Your order has been saved — please contact us or try again.',
            ], 502);
        }
    }

    /**
     * GET /checkout/paymongo/return/{order} — PayMongo redirects the
     * customer's browser here after they authorize (or cancel) the GCash
     * payment. Since there is no webhook tunnel available yet in local
     * development, this synchronous status check against the PayMongo API
     * is the authoritative confirmation path for now; the webhook receiver
     * (PaymongoWebhookController) does the same update for when it's wired
     * up in an environment PayMongo can reach.
     */
    public function paymongoReturn(Order $order): RedirectResponse
    {
        $customer = auth('customer')->user();

        if (! $customer || $order->customer_id !== $customer->id) {
            abort(403);
        }

        $paymentProof = $order->paymentProof;

        if (! $paymentProof || ! $paymentProof->paymongo_payment_intent_id) {
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'We could not find a GCash payment attempt for this order.');
        }

        try {
            $intent = $this->paymongo->retrievePaymentIntent($paymentProof->paymongo_payment_intent_id);
            $resolved = $this->paymongo->interpretIntentStatus($intent);

            if ($resolved === 'succeeded') {
                $paymentId = $intent['attributes']['payments'][0]['id'] ?? $paymentProof->paymongo_payment_intent_id;

                $paymentProof->update([
                    'status'           => 'paid',
                    'paid_at'          => now(),
                    'reference_number' => $paymentId,
                ]);

                return redirect()->route('account.orders.show', $order)
                    ->with('success', 'Payment received! We will verify and confirm your order shortly.');
            }

            if ($resolved === 'failed') {
                $paymentProof->update(['status' => 'failed']);

                return redirect()->route('account.orders.show', $order)
                    ->with('error', 'Your GCash payment was not completed. Please contact us or try again.');
            }

            // Still awaiting the customer to act (e.g. they navigated back
            // without finishing) — leave it pending rather than failing it.
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'Your GCash payment is still processing. We will update your order once it completes.');
        } catch (\Throwable $e) {
            Log::error('PayMongo payment status check failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('account.orders.show', $order)
                ->with('error', 'We could not confirm your GCash payment status. Please contact us.');
        }
    }

    /**
     * POST /checkout/qrph — temporary stand-in for payWithGcash() while
     * GCash's e-wallet is still pending activation on our PayMongo account
     * (see PAYMONGO_CASHIER_QR_METHOD, used the same way on the cashier's
     * billing screen). Creates the order exactly like payWithGcash() does,
     * but requests a PayMongo QR Ph payment intent instead of a GCash
     * redirect — QR Ph hands back an already-rendered QR image with no
     * return-to-browser step (a *different* device scans it), so instead of
     * polling for confirmation we ask the customer to upload a screenshot
     * of their payment for the cashier to manually verify. Remove this once
     * GCash is approved and point the checkout page back at payWithGcash().
     */
    public function payWithQrph(StoreOrderRequest $request): JsonResponse
    {
        if ($downtime = RestaurantDowntime::current()) {
            return response()->json(['message' => $downtime->blockedOrderingMessage()], 422);
        }

        $cart = $this->activeCart();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty. Add some items before checking out.'], 422);
        }

        $customer = auth('customer')->user();

        if (! $customer) {
            return response()->json(['message' => 'We could not find your customer profile. Please contact support.'], 422);
        }

        $shortages = [];
        foreach ($cart->items as $item) {
            $menuItem = MenuItem::find($item->menu_item_id);

            if ($menuItem && $menuItem->isRtcTracked() && $item->quantity > $menuItem->available_stock) {
                $shortages[] = "{$menuItem->menu_name} (only {$menuItem->available_stock} left)";
            }
        }

        if ($shortages) {
            return response()->json(['message' => 'Some items in your cart no longer have enough stock: ' . implode(', ', $shortages) . '. Please update your cart.'], 422);
        }

        $pendingStatus = OrderStatus::where('status_name', 'Pending')->first();

        $amount = $request->payment_type === 'full'
            ? (float) $cart->total
            : round(((float) $cart->total) * (Order::HALF_PAYMENT_PERCENT / 100), 2);

        [$order, $paymentProof] = DB::transaction(function () use ($request, $cart, $customer, $pendingStatus, $amount) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'total_amount'          => $cart->total,
                'customer_id'           => $customer->id,
                'order_status_id'       => $pendingStatus?->id,
                'order_type'            => 'online',
                'payment_status'        => 'pending',
                'payment_method'        => 'cashless',
                'special_instructions'  => $request->special_instructions,
                'pickup_at'             => $request->pickup_at,
                'approval_status'       => 'pending',
            ]);

            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $item->menu_item_id,
                    'item_name'    => $item->menuItem->menu_name,
                    'quantity'     => $item->quantity,
                    'notes'        => $item->notes,
                    'price'        => $item->unit_price,
                    'subtotal'     => $item->unit_price * $item->quantity,
                ]);
            }

            $paymentProof = PaymentProof::create([
                'order_id'       => $order->id,
                'customer_id'    => $customer->id,
                'amount'         => $amount,
                'payment_type'   => $request->payment_type,
                'payment_method' => 'gcash',
                'status'         => 'awaiting_payment',
            ]);

            $cart->update(['status' => 'completed']);

            return [$order, $paymentProof];
        });

        try {
            $intent = $this->paymongo->createPaymentIntent(
                (int) round($amount * 100),
                "Order {$order->order_number} — " . ($request->payment_type === 'full' ? 'Full Payment' : 'Half Payment'),
                ['qrph']
            );

            $method = $this->paymongo->createPaymentMethod('qrph', array_filter([
                'name'  => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->contact_no,
            ]));

            $attached = $this->paymongo->attachPaymentMethod(
                $intent['id'],
                $method['id'],
                route('checkout.paymongo.return', $order)
            );

            $nextAction = $this->paymongo->extractNextAction($attached);

            $paymentProof->update([
                'paymongo_payment_intent_id' => $intent['id'],
                'paymongo_payment_method_id' => $method['id'],
                'paymongo_checkout_url'      => $nextAction['value'],
            ]);

            return response()->json(['redirect_url' => route('checkout.qrph.show', $order)]);
        } catch (\Throwable $e) {
            Log::error('PayMongo QR Ph payment setup failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            $paymentProof->update(['status' => 'failed']);

            return response()->json([
                'message' => 'We could not generate a QR code right now. Your order has been saved — please contact us or try again.',
            ], 502);
        }
    }

    /**
     * GET /checkout/qrph/{order} — shows the QR Ph code for the customer to
     * scan with their GCash app, plus an upload form for a screenshot of
     * the completed payment (manually reviewed by a cashier, since there is
     * no automatic confirmation for this temporary flow).
     */
    public function showQrph(Order $order): View|RedirectResponse
    {
        $customer = auth('customer')->user();

        if (! $customer || $order->customer_id !== $customer->id) {
            abort(403);
        }

        $paymentProof = $order->paymentProof;

        if (! $paymentProof || ! $paymentProof->paymongo_checkout_url) {
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'We could not find a QR code for this order.');
        }

        return view('checkout.qrph', [
            'order'        => $order,
            'paymentProof' => $paymentProof,
        ]);
    }

    /**
     * GET /checkout/qrph/{order}/status — polled by the QR page while it's
     * waiting, mirroring CashierController::gcashIntentStatus(). The moment
     * PayMongo reports the QR Ph payment as succeeded, this auto-confirms
     * it — no screenshot needed. The upload form on the same page stays
     * available as a fallback for customers who close the tab before this
     * catches it, or if the webhook/poll never resolves.
     */
    public function qrphStatus(Order $order): JsonResponse
    {
        $customer = auth('customer')->user();

        if (! $customer || $order->customer_id !== $customer->id) {
            abort(403);
        }

        $paymentProof = $order->paymentProof;

        if (! $paymentProof || ! $paymentProof->paymongo_payment_intent_id) {
            return response()->json(['status' => 'unknown'], 404);
        }

        if ($paymentProof->status !== 'awaiting_payment') {
            return response()->json(['status' => $paymentProof->status]);
        }

        if ($paymentProof->isQrExpired()) {
            return response()->json(['status' => 'expired']);
        }

        try {
            $intent = $this->paymongo->retrievePaymentIntent($paymentProof->paymongo_payment_intent_id);
            $resolved = $this->paymongo->interpretIntentStatus($intent);

            if ($resolved === 'succeeded') {
                $paymentId = $intent['attributes']['payments'][0]['id'] ?? $paymentProof->paymongo_payment_intent_id;

                $paymentProof->update([
                    'status'           => 'paid',
                    'paid_at'          => now(),
                    'reference_number' => $paymentProof->reference_number ?: $paymentId,
                ]);

                return response()->json(['status' => 'paid']);
            }

            if ($resolved === 'failed') {
                $paymentProof->update(['status' => 'failed']);

                return response()->json(['status' => 'failed']);
            }

            return response()->json(['status' => 'pending']);
        } catch (\Throwable $e) {
            Log::error('PayMongo QR Ph status check failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['status' => 'pending']);
        }
    }

    /**
     * POST /checkout/qrph/{order}/proof — stores the customer's uploaded
     * payment screenshot so a cashier can manually verify it against the
     * amount due before approving the order.
     */
    public function uploadQrphProof(UploadPaymentProofRequest $request, Order $order): RedirectResponse
    {
        $customer = auth('customer')->user();

        if (! $customer || $order->customer_id !== $customer->id) {
            abort(403);
        }

        $paymentProof = $order->paymentProof;

        if (! $paymentProof) {
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'We could not find a payment record for this order.');
        }

        if ($paymentProof->proof_image) {
            return redirect()->route('account.orders.show', $order)
                ->with('info', 'You have already submitted a payment screenshot for this order.');
        }

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        $paymentProof->update([
            'proof_image'      => $path,
            'reference_number' => $request->reference_number,
        ]);

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'Thanks! Your payment screenshot has been submitted. We will verify it and confirm your order shortly.');
    }
}
