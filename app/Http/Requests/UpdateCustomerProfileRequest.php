<?php

namespace App\Http\Requests;

use App\Rules\PersonName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'max:100', new PersonName],
            'last_name'       => ['nullable', 'string', 'max:100', new PersonName],
            'contact_no'      => ['nullable', 'string', 'max:20'],
            'street'          => ['nullable', 'string', 'max:150'],
            // Address dropdowns: once a province is picked, the city and barangay must be picked too
            'province'          => ['nullable', 'string', 'max:100'],
            'municipality'      => ['nullable', 'required_with:province', 'string', 'max:100'],
            'barangay'          => ['nullable', 'required_with:municipality', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'digits:4'],
            'province_code'     => ['nullable', 'regex:/^\d{9,10}$/'],
            'municipality_code' => ['nullable', 'regex:/^\d{9,10}$/'],
            'barangay_code'     => ['nullable', 'regex:/^\d{9,10}$/'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'profile_picture.max' => 'Profile picture must not exceed 2 MB.',
            'municipality.required_with' => 'Please select your city / municipality.',
            'barangay.required_with'     => 'Please select your barangay.',
            'postal_code.digits'         => 'Postal code must be exactly 4 digits.',
        ];
    }
}
