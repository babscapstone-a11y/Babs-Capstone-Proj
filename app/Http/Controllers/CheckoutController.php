<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Cart;
use App\Models\DineInOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\PaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CheckoutController extends Controller
{
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
            'cart'      => $cart,
            'customer'  => $customer,
            'cartCount' => $cart->item_count,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->activeCart();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Add some items before checking out.');
        }

        $customer = auth('customer')->user();

        if (! $customer) {
            return redirect()->route('cart.index')
                ->with('error', 'We could not find your customer profile. Please contact support.');
        }

        $pendingStatus = OrderStatus::where('status_name', 'Pending')->first();
        $isOnline      = $request->order_type === 'online';

        // The upload must happen outside the DB transaction — storing a file
        // isn't transactional, so we resolve the path first and only touch
        // the disk once we know validation already passed.
        $proofImagePath = $isOnline
            ? $request->file('proof_image')->store('payment-proofs', 'public')
            : null;

        $order = DB::transaction(function () use ($request, $cart, $customer, $pendingStatus, $isOnline, $proofImagePath) {
            $order = Order::create([
                'order_number'          => Order::generateOrderNumber(),
                'total_amount'          => $cart->total,
                'customer_id'           => $customer->id,
                'order_status_id'       => $pendingStatus?->id,
                'order_type'            => $request->order_type,
                'payment_status'        => 'pending',
                'payment_method'        => $isOnline ? 'cashless' : $request->payment_method,
                'special_instructions'  => $request->special_instructions,
                'pickup_at'             => $isOnline ? $request->pickup_at : null,
                'approval_status'       => $isOnline ? 'pending' : null,
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

            if ($request->order_type === 'dine_in') {
                DineInOrder::create([
                    'order_id'     => $order->id,
                    'table_number' => $request->table_number,
                ]);
            }

            if ($isOnline) {
                PaymentProof::create([
                    'order_id'          => $order->id,
                    'customer_id'       => $customer->id,
                    'amount'            => $request->down_payment_amount,
                    'payment_method'    => $request->down_payment_method,
                    'reference_number'  => $request->down_payment_reference,
                    'proof_image'       => $proofImagePath,
                    'paid_at'           => now(),
                ]);
            }

            $cart->update(['status' => 'completed']);

            return $order;
        });

        $message = $isOnline
            ? 'Your order has been submitted! We will verify your down-payment and notify you once approved.'
            : 'Your order has been placed successfully!';

        return redirect()->route('account.orders.show', $order)->with('success', $message);
    }
}
