<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
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
            'pickup_at'               => ['required', 'date', 'after:now'],
            'payment_type'            => ['required', 'in:half,full'],
        ];
    }
}
