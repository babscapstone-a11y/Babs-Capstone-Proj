<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'username'   => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'phone'      => ['nullable', 'digits:11'],
            'role_id'    => ['required', 'integer', 'exists:roles,id'],
            'status'     => ['required', 'in:active,inactive'],
            'password'   => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required'  => 'Please select a role for this staff member.',
            'role_id.exists'    => 'The selected role is invalid.',
            'phone.digits'      => 'Phone number must be exactly 11 digits.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
