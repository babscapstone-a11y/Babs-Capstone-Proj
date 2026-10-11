<?php

namespace App\Http\Requests;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /** Minimum notice, in minutes, the kitchen needs before a scheduled pickup. */
    const MIN_LEAD_MINUTES = 30;

    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * Customer self-checkout only ever places online orders (Dine-In orders are
     * placed by table servers). Both choices are stored as order_type "online";
     * "schedule" says which one the customer picked:
     *   advance — pick-up from today up to Order::ADVANCE_MAX_DAYS days ahead
     *   pickup  — same-day pick-up (today only)
     */
    public function rules(): array
    {
        return [
            'order_type'              => ['required', 'in:online'],
            'schedule'                => ['required', 'in:advance,pickup'],
            'special_instructions'    => ['nullable', 'string', 'max:500'],
            'pickup_at'               => ['required', 'date', function ($attribute, $value, $fail) {
                $pickupAt = Carbon::parse($value);

                if ($this->input('schedule') === 'pickup' && ! $pickupAt->isSameDay(now())) {
                    $fail('Pick-Up orders are for today only. To schedule another day, choose Advance Order.');

                    return;
                }

                if ($this->input('schedule') === 'advance'
                    && ($pickupAt->lt(now()->startOfDay()) || $pickupAt->gt(now()->addDays(Order::ADVANCE_MAX_DAYS)->endOfDay()))) {
                    $fail('Advance orders can be scheduled from today up to ' . Order::ADVANCE_MAX_DAYS . ' days ahead.');

                    return;
                }

                if ($pickupAt->lt(now()->addMinutes(self::MIN_LEAD_MINUTES))) {
                    $fail('Pick-up time must be at least ' . self::MIN_LEAD_MINUTES . ' minutes from now, so the kitchen has time to prepare your order.');

                    return;
                }

                $minutes = $pickupAt->hour * 60 + $pickupAt->minute;

                if ($minutes < Order::OPEN_HOUR * 60 || $minutes > Order::CLOSE_HOUR * 60) {
                    $fail('Pick-up time must be between 11:00 AM and 9:00 PM, our restaurant hours.');
                }
            }],
            'payment_type'            => ['required', 'in:half,full'],
            // Advance Orders can be for dining in, so they need a headcount; Pick-Up ignores it
            'party_size'              => ['required_if:schedule,advance', 'nullable', 'integer', 'min:1', 'max:' . Order::PARTY_SIZE_MAX],
        ];
    }

    public function messages(): array
    {
        return [
            'party_size.required_if' => 'Please enter the number of persons for your Advance Order.',
            'party_size.integer'     => 'The number of persons must be a whole number.',
            'party_size.min'         => 'The number of persons must be at least 1.',
            'party_size.max'         => 'For groups larger than ' . Order::PARTY_SIZE_MAX . ', please contact the restaurant directly.',
        ];
    }

    /** "Pay half now" is only offered for orders of ₱500 and up. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('payment_type') !== 'half') {
                    return;
                }

                $cart = Cart::where('customer_id', auth('customer')->id())->where('status', 'active')->with('items')->first();

                if ($cart && $cart->total < Order::HALF_PAYMENT_MIN_TOTAL) {
                    $validator->errors()->add('payment_type', 'Pay Half Now is only available for orders of ₱' . number_format(Order::HALF_PAYMENT_MIN_TOTAL) . ' and up. Please choose Pay in Full.');
                }
            },
        ];
    }
}
