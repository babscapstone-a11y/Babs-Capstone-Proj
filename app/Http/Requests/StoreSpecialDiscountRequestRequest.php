<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialDiscountRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership/eligibility of the order is checked in the controller.
    }

    public function rules(): array
    {
        return [
            'discount_id' => ['required', 'exists:discounts,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
