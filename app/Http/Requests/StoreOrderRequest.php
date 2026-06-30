<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isCustomer();
    }

    public function rules(): array
    {
        return [
            'order_type'           => ['required', 'in:dine_in,takeout'],
            'payment_method'       => ['required', 'in:cash,cashless'],
            'table_number'         => ['nullable', 'integer', 'min:1', 'max:999'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
