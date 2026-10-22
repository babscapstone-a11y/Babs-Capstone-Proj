<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use App\Rules\PersonName;
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
            'first_name' => ['required', 'string', 'max:100', new PersonName],
            'last_name'  => ['required', 'string', 'max:100', new PersonName],
            'email'   => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'phone'   => ['nullable', 'digits:11'],
            // Optional so staff created before birthdays were recorded can still be edited;
            // needed before their password can be reset to the default
            'birthday' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            // Staff roles only: the customer role is given at customer registration
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where(fn ($q) => $q->where('role_name', '!=', Role::CUSTOMER))],
            'status'  => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required'    => 'Please select a role for this staff member.',
            'role_id.exists'      => 'The selected role is invalid.',
            'phone.digits'        => 'Phone number must be exactly 11 digits.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'birthday.before'     => 'Birthday must be a date in the past.',
        ];
    }
}
