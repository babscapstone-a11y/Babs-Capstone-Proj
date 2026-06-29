<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountId = $this->route('discount')?->id;

        $rules = [
            'discount_name'    => [
                'required', 'string', 'max:150',
                Rule::unique('discounts', 'discount_name')->ignore($discountId),
            ],
            'discount_type'    => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value'   => ['required', 'numeric', 'min:0.01'],
            'eligibility_type' => ['required', Rule::in([
                'senior_citizen', 'pwd', 'promotional', 'employee',
                'minimum_purchase', 'date_range', 'all_customers',
            ])],
            'description'      => ['nullable', 'string', 'max:1000'],
            'status'           => ['required', Rule::in(['active', 'inactive'])],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ];

        if ($this->input('discount_type') === 'percentage') {
            $rules['discount_value'][] = 'max:100';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'discount_name.required'    => 'Please enter a discount name.',
            'discount_name.unique'      => 'A discount with this name already exists.',
            'discount_type.required'    => 'Please select a discount type.',
            'discount_value.required'   => 'Please enter a discount value.',
            'discount_value.min'        => 'Discount value must be greater than 0.',
            'discount_value.max'        => 'Percentage discount cannot exceed 100%.',
            'eligibility_type.required' => 'Please select an eligibility condition.',
            'end_date.after_or_equal'   => 'End date must be on or after the start date.',
        ];
    }
}
