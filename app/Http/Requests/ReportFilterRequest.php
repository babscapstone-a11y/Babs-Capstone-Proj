<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level 'admin' middleware already gates this module.
        return true;
    }

    public function rules(): array
    {
        return [
            'type'              => ['nullable', 'in:sales,inventory,orders'],
            'date_range'        => ['nullable', 'in:today,yesterday,week,month,year,custom'],
            'start_date'        => ['nullable', 'required_if:date_range,custom', 'date'],
            'end_date'          => ['nullable', 'required_if:date_range,custom', 'date', 'after_or_equal:start_date'],
            'category'          => ['nullable', 'string', 'in:all,food,beverage,rtc'],
            'order_type'        => ['nullable', 'in:all,dine_in,takeout,online'],
            'menu_category_id'  => ['nullable', 'string'],
            'include_unpaid'    => ['nullable', 'boolean'],
            'chart_interval'    => ['nullable', 'in:daily,weekly,monthly'],
            'search'            => ['nullable', 'string', 'max:100'],
            'format'            => ['nullable', 'in:csv'],
        ];
    }

    /**
     * Normalized filter payload consumed by ReportService — trims the
     * request down to the keys the service methods actually expect.
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'date_range'       => $validated['date_range'] ?? 'today',
            'start_date'       => $validated['start_date'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'category'         => $validated['category'] ?? 'all',
            'order_type'       => $validated['order_type'] ?? 'all',
            'menu_category_id' => $validated['menu_category_id'] ?? 'all',
            'include_unpaid'   => $this->boolean('include_unpaid'),
            'chart_interval'   => $validated['chart_interval'] ?? 'daily',
            'search'           => $validated['search'] ?? '',
        ];
    }
}
