<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'menu_name'   => ['required', 'string', 'max:255', Rule::unique('menu_items', 'menu_name')],
            'item_type'   => ['required', Rule::in(['food', 'beverage'])],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['required', 'numeric', 'min:0.01'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active'   => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_name.unique' => 'A menu item with this name already exists.',
            'price.min'        => 'Price must be greater than zero.',
        ];
    }
}
