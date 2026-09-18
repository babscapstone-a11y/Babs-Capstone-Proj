<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check(); // Ownership of the order is checked in the controller.
    }

    public function rules(): array
    {
        return [
            'reference_number' => ['required', 'string', 'max:100'],
            'proof_image'       => ['required', 'image', 'max:5120'],
        ];
    }
}
