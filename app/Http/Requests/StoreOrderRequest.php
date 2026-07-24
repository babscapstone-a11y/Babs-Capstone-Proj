<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        $rules = [
            'order_type'           => ['required', 'in:dine_in,takeout,online'],
            'payment_method'       => ['required_unless:order_type,online', 'nullable', 'in:cash,cashless'],
            'table_number'         => ['nullable', 'integer', 'min:1', 'max:999'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->input('order_type') === 'online') {
            $rules['pickup_at']              = ['required', 'date', 'after:now'];
            $rules['down_payment_method']    = ['required', 'in:gcash,maya,bank_transfer,other'];
            $rules['down_payment_reference'] = ['required', 'string', 'max:100'];
            $rules['down_payment_amount']    = ['required', 'numeric', 'min:1'];
            $rules['proof_image']            = ['required', 'image', 'max:5120'];
        }

        return $rules;
    }
}
