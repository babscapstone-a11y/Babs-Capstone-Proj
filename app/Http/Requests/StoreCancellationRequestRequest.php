<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCancellationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership of the order is checked in the controller.
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
