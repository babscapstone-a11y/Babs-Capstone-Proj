<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectCancellationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The 'decide' policy check on the route handles authorization.
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }
}
