<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderKitchenStatusRequest extends FormRequest
{
    /**
     * Sequential, no-skip transition whitelist: current status_name => allowed next status_name.
     * The controller checks the loaded Order's current status against this map; this FormRequest
     * only validates that the requested target is a legal value in general.
     */
    public const ALLOWED_TRANSITIONS = [
        'Pending'    => 'Processing',
        'Processing' => 'Ready',
        'Ready'      => 'Completed',
    ];

    public function authorize(): bool
    {
        return auth('staff')->user()?->isKitchenStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Processing,Ready,Completed'],
        ];
    }
}
