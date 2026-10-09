<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateCustomerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            // Strong password rule is defined once in AppServiceProvider (Password::defaults)
            'new_password'     => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    public function attributes(): array
    {
        return ['new_password' => 'new password'];
    }
}
