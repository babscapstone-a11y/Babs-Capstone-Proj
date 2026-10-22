<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check(); // Ownership of the order is checked in the controller.
    }

    /** GCash shows the reference number in groups ("1234 567 890123"), so spaces are dropped first */
    protected function prepareForValidation(): void
    {
        if (is_string($this->reference_number)) {
            $this->merge(['reference_number' => preg_replace('/\s+/', '', $this->reference_number)]);
        }
    }

    public function rules(): array
    {
        return [
            'reference_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:100'],
            'proof_image'       => ['required', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference_number.required' => 'Please enter your GCash reference number.',
            'reference_number.regex'    => 'The GCash reference number must contain numbers only.',
        ];
    }
}
