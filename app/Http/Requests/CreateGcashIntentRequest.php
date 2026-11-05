<?php

namespace App\Http\Requests;

use App\Models\Discount;
use Illuminate\Foundation\Http\FormRequest;

class CreateGcashIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route-level 'pay' policy check handles authorization.
    }

    public function rules(): array
    {
        return [
            'discount_id'           => ['nullable', 'exists:discounts,id'],
            'service_charge'        => ['nullable', 'numeric', 'min:0'],
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
            }
        });
    }
}
