<?php

namespace App\Http\Requests;

use App\Models\Discount;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route-level 'pay' policy check handles authorization.
    }

    public function rules(): array
    {
        return [
            'payment_method'        => ['required', 'in:cash,cashless'],
            'discount_id'           => ['nullable', 'exists:discounts,id'],
            'eligibility_confirmed' => ['sometimes', 'boolean'],
            // Typed by the cashier when the selected discount is a Special Discount
            'special_discount_amount' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'service_charge'        => ['nullable', 'numeric', 'min:0'],
            // Same cap as the billing screen: 6 digits and 2 decimals
            'amount_received'       => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'reference_number'      => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->discount_id) {
                return;
            }

            $discount = Discount::find($this->discount_id);

            if (! $discount || ! $discount->isCurrentlyValid()) {
                $validator->errors()->add('discount_id', 'The selected discount is no longer active or has expired.');
                return;
            }

            if ($discount->requiresEligibilityVerification() && ! $this->boolean('eligibility_confirmed')) {
                $validator->errors()->add(
                    'eligibility_confirmed',
                    'Please verify the customer\'s ID before applying this discount.'
                );
            }
        });
    }
}
