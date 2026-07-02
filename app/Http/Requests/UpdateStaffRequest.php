<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        return $this->user()->can('update', $target);
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'   => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'   => ['nullable', 'digits:11'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status'  => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Please select a role for this staff member.',
            'role_id.exists'   => 'The selected role is invalid.',
            'phone.digits'     => 'Phone number must be exactly 11 digits.',
        ];
    }
}
