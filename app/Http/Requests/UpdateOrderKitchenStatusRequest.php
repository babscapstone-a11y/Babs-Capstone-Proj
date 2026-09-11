<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderKitchenStatusRequest extends FormRequest
{
    /**
     * Sequential, no-skip transition whitelist: current status_name => allowed next status_name.
     * The controller checks the loaded Order's current status against this map; this FormRequest
     * only validates that the requested target is a legal value in general.
     *
     * The kitchen's chain stops at "Ready" — from there the food server takes
     * over (Module 20's serve()/package()), so "Completed" is deliberately
     * not a kitchen-reachable target here.
     */
    public const ALLOWED_TRANSITIONS = [
        'Pending'    => 'Processing',
        'Processing' => 'Ready',
    ];

    public function authorize(): bool
    {
        return auth('staff')->user()?->isKitchenStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Processing,Ready'],
        ];
    }
}
