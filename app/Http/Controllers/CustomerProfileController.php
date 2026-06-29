<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCustomerPasswordRequest;
use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    private function cartCount(): int
    {
        $cart = Cart::where('user_id', auth()->id())->with('items')->first();
        return $cart ? $cart->item_count : 0;
    }

    public function index(): View
    {
        $user     = auth()->user();
        $customer = $user->customer()->with('address')->firstOrFail();

        $orders = Order::where('customer_id', $customer->id)
            ->with(['orderStatus', 'details'])
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'page');

        return view('customer.profile.index', [
            'user'      => $user,
            'customer'  => $customer,
            'orders'    => $orders,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function updateProfile(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $user     = auth()->user();
        $customer = $user->customer()->with('address')->firstOrFail();

        // Handle profile picture upload
        $picturePath = $customer->profile_picture;
        if ($request->hasFile('profile_picture')) {
            if ($picturePath) {
                Storage::disk('public')->delete($picturePath);
            }
            $picturePath = $request->file('profile_picture')
                ->store('profile-pictures', 'public');
        }

        // Update or create address record
        $addrFields = array_filter(
            $request->only(['street', 'barangay', 'municipality', 'province']),
            fn($v) => $v !== null && $v !== '',
        );

        if ($customer->address_id && $customer->address) {
            $customer->address->update($addrFields);
        } elseif (! empty($addrFields)) {
            $address             = Address::create($addrFields);
            $customer->address_id = $address->id;
        }

        $customer->fill([
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'contact_no'      => $request->contact_no,
            'profile_picture' => $picturePath,
        ])->save();

        return redirect()->route('account.index', ['#profile'])
            ->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(UpdateCustomerPasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withFragment('password');
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('account.index', ['#password'])
            ->with('success', 'Password changed successfully!');
    }

    public function showOrder(Order $order): View
    {
        $user     = auth()->user();
        $customer = $user->customer;

        if ($order->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this order.');
        }

        $order->load(['orderStatus', 'details.menuItem', 'onlineOrder']);

        return view('customer.profile.order-show', [
            'order'     => $order,
            'customer'  => $customer,
            'cartCount' => $this->cartCount(),
        ]);
    }
}
