<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
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

        $customer = auth('customer')->user();

        return view('checkout.index', [
            'cart'           => $cart,
            'customer'       => $customer,
            'cartCount'      => $cart->item_count,
            'activeDowntime' => RestaurantDowntime::current(),
        ]);
    }

    /**
     * POST /checkout/paymongo — creates the order + payment record, then
     * kicks off a PayMongo GCash payment and hands the customer a redirect
     * URL to authorize it.
     */
    public function payWithGcash(StoreOrderRequest $request): JsonResponse
    {
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
}
