<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreOrderRequest extends FormRequest
{
    /** Minimum notice, in minutes, the kitchen needs before a scheduled pickup. */
    const MIN_LEAD_MINUTES = 30;

    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * Customer self-checkout only ever places online orders now (Advance
     * Order / Pick-Up are both order_type "online", identical requirements —
     * they differ only in how the customer frames the request, not in how
     * it's processed). Dine-In orders are placed by table servers, not here.
     */
    public function rules(): array
    {
        return [
            'order_type'              => ['required', 'in:online'],
            'special_instructions'    => ['nullable', 'string', 'max:500'],
            'pickup_at'               => ['required', 'date', function ($attribute, $value, $fail) {
                $pickupAt = Carbon::parse($value);

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
        ];
    }
}
