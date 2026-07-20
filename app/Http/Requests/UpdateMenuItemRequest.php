<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('menu')?->id;

        return [
            'menu_name'   => ['required', 'string', 'max:255', Rule::unique('menu_items', 'menu_name')->ignore($id)],
            'item_type'   => ['required', Rule::in(['food', 'beverage'])],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('item_type', $this->input('item_type'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['required', 'numeric', 'min:0.01'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active'   => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_name.unique'   => 'A menu item with this name already exists.',
            'price.min'          => 'Price must be greater than zero.',
            'category_id.exists' => 'Please select a category that matches the chosen item type.',
        ];
    }
}
