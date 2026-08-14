<?php

namespace App\Http\Requests;

use App\Models\DineInOrder;
use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTableServerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isTableServer() ?? false;
    }

    /**
     * This endpoint is JSON-only (no HTML form posts to it), but the app's global
     * exception handler only renders JSON for /api/* paths (see bootstrap/app.php's
     * shouldRenderJsonWhen). Force a clean JSON error response here regardless, so the
     * order-builder JS always gets a parseable error instead of a followed HTML redirect.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Access denied. Table server privileges required.',
        ], 403));
    }

    public function rules(): array
    {
        return [
            'order_type'            => ['required', 'in:dine_in,takeout'],
            'table_number'          => ['required_if:order_type,dine_in', 'nullable', 'integer', 'min:1', 'max:999'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.menu_item_id'  => [
                'required',
                Rule::exists('menu_items', 'id')->where(fn ($q) => $q->where('is_active', true)->where('is_available', true)),
            ],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes'         => ['nullable', 'string', 'max:255'],
            'special_instructions'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('table_number')) {
                return;
            }

            // "Completed" needs special handling here: the kitchen sets it as
            // their hand-off signal to the food server (Module 20), well
            // before the table's actually been served — but the same status
            // gets reused again once the cashier takes payment. served_at/
            // packaged_at (only ever set once a food server acts) are what
            // distinguish "still sitting there, not yet served" from
            // "already served and since paid" for that second case.
            //
            // payment_status is checked directly, on top of that, as a
            // second line of defense: a paid order must never keep blocking
            // a table even if order_status_id somehow didn't make it to
            // Completed (e.g. drifted out of sync with payment_status via a
            // direct DB edit) — the transaction being closed is what
            // actually matters here, not which status row it's pointing at.
            $hasActiveOrder = DineInOrder::where('table_number', $this->table_number)
                ->whereHas('order', function ($oq) {
                    $oq->where('payment_status', '!=', 'paid')
                        ->where(function ($statusQ) {
                            $statusQ->whereHas('orderStatus', fn ($sq) => $sq->whereIn('status_name', ['Pending', 'Processing', 'Ready', 'Served']))
                                ->orWhere(function ($completedQ) {
                                    $completedQ->whereHas('orderStatus', fn ($sq) => $sq->where('status_name', 'Completed'))
                                        ->whereNull('served_at')
                                        ->whereNull('packaged_at');
                                });
                        });
                })
                ->exists();

            if ($hasActiveOrder) {
                $validator->errors()->add(
                    'table_number',
                    "Table {$this->table_number} already has an active order in progress. Please choose another table or wait until it is completed."
                );
            }
        });

        $validator->after(function (Validator $validator) {
            $items = collect($this->input('items', []))->filter(fn ($line) => isset($line['menu_item_id'], $line['quantity']));

            if ($items->isEmpty()) {
                return;
            }

            $menuItems = MenuItem::whereIn('id', $items->pluck('menu_item_id'))->get()->keyBy('id');

            $items->groupBy('menu_item_id')->each(function ($group, $menuItemId) use ($validator, $menuItems) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem || ! $menuItem->isRtcTracked()) {
                    return;
                }

                $needed = $group->sum('quantity');

                if ($needed > $menuItem->available_stock) {
                    $validator->errors()->add(
                        'items',
                        "Only {$menuItem->available_stock} serving(s) of {$menuItem->menu_name} left."
                    );
                }
            });
        });
    }
}
