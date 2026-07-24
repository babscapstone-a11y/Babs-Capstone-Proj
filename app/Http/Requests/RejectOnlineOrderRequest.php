<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectOnlineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route-level 'decideOnline' policy check handles authorization.
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
