<?php

namespace App\Services;

use App\Models\ConversionLog;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the three Report Generation Module reports (Sales, Inventory,
 * Order Performance) straight from existing operational tables — nothing
 * here is precomputed/cached or hardcoded; every figure is derived from
 * the current database state for the requested filters.
 */
class ReportService
{
    /**
     * Resolve a date-range filter key into a concrete [from, to] Carbon pair.
     */
    public function resolveDateRange(string $range, ?string $start, ?string $end): array
    {
        $now = Carbon::now();

        return match ($range) {
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week'      => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month'     => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year'      => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom'    => [
                $start ? Carbon::parse($start)->startOfDay() : $now->copy()->startOfMonth(),
                $end ? Carbon::parse($end)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default     => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    /* ── Sales Report (REQ058) ────────────────────────────────────── */

    public function salesReport(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters['date_range'] ?? 'today', $filters['start_date'] ?? null, $filters['end_date'] ?? null);
        $category      = $filters['category'] ?? 'all';
        $includeUnpaid = (bool) ($filters['include_unpaid'] ?? false);
        $search        = trim((string) ($filters['search'] ?? ''));
        $interval      = $filters['chart_interval'] ?? 'daily';

        // A Payment row only ever exists once cashier billing has recorded a
        // completed transaction, so this is inherently "paid" — no separate
        // paid/unpaid flag to check.
        $paymentsQuery = Payment::query()
            ->whereBetween('payment_date', [$from, $to])
            ->with(['order', 'cashier.staff', 'modeOfPayment', 'invoice.discount']);

        if ($category !== 'all') {
            $paymentsQuery->whereHas('order.details.menuItem', fn ($q) => $q->where('item_type', $category));
        }

        if ($search !== '') {
            $paymentsQuery->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        $payments = $paymentsQuery->orderByDesc('payment_date')->get();

        $totalSales       = (float) $payments->sum(fn (Payment $p) => (float) ($p->invoice->subtotal ?? 0));
        $totalDiscounts   = (float) $payments->sum(fn (Payment $p) => (float) ($p->invoice->discount_amount ?? 0));
        $netSales         = (float) $payments->sum(fn (Payment $p) => (float) ($p->invoice->final_total ?? $p->amount_paid));
        $transactionCount = $payments->count();
        $avgTransaction   = $transactionCount > 0 ? $netSales / $transactionCount : 0.0;

        $rows = $payments->map(fn (Payment $p) => [
            'transaction_number' => $p->transaction_number,
            'order_number'       => $p->order?->order_number,
            'date'               => $p->payment_date?->format('Y-m-d'),
            'time'               => $p->payment_date?->format('h:i A'),
            'order_type'         => $p->order?->order_type_label,
            'cashier'            => $p->cashier?->name ?: ($p->cashier?->username ?? '—'),
            'subtotal'           => (float) ($p->invoice->subtotal ?? 0),
            'discount'           => (float) ($p->invoice->discount_amount ?? 0),
            'total'              => (float) ($p->invoice->final_total ?? $p->amount_paid),
            'payment_method'     => $p->modeOfPayment?->method_name ?? $p->order?->payment_method_label,
            'payment_status'     => 'Paid',
            'excluded'           => false,
        ])->values();

        // Cancelled/unpaid orders are excluded from every sales figure above
        // by construction (they never produced a Payment row). Only when the
        // Administrator explicitly opts in do we surface them in the table,
        // purely for visibility/audit — never counted into the totals.
        $excludedRows = collect();
        if ($includeUnpaid) {
            $excludedQuery = Order::whereBetween('created_at', [$from, $to])
                ->where(function ($q) {
                    $q->where('payment_status', '!=', 'paid')
                      ->orWhereHas('orderStatus', fn ($sq) => $sq->where('status_name', 'Cancelled'));
                })
                ->with(['orderStatus', 'details']);

            if ($category !== 'all') {
                $excludedQuery->whereHas('details.menuItem', fn ($q) => $q->where('item_type', $category));
            }
            if ($search !== '') {
                $excludedQuery->where('order_number', 'like', "%{$search}%");
            }

            $excludedRows = $excludedQuery->orderByDesc('created_at')->get()->map(fn (Order $o) => [
                'transaction_number' => null,
                'order_number'       => $o->order_number,
                'date'               => $o->created_at?->format('Y-m-d'),
                'time'               => $o->created_at?->format('h:i A'),
                'order_type'         => $o->order_type_label,
                'cashier'            => '—',
                'subtotal'           => (float) $o->details->sum('subtotal'),
                'discount'           => 0.0,
                'total'              => (float) $o->total_amount,
                'payment_method'     => $o->payment_method_label,
                'payment_status'     => $o->isCancelled() ? 'Cancelled' : $o->payment_status_label,
                'excluded'           => true,
            ])->values();
        }

        $allRows = $rows->concat($excludedRows)
            ->sortByDesc(fn ($r) => $r['date'].' '.$r['time'])
            ->values();

        return [
            'summary' => [
                'total_sales'         => $totalSales,
                'transaction_count'   => $transactionCount,
                'average_transaction' => $avgTransaction,
                'total_discounts'     => $totalDiscounts,
                'net_sales'           => $netSales,
            ],
            'rows'  => $allRows,
            'chart' => $this->buildSalesChart($payments, $from, $to, $interval),
            'meta'  => [
                'from' => $from, 'to' => $to, 'category' => $category,
                'include_unpaid' => $includeUnpaid, 'interval' => $interval,
            ],
        ];
    }

    private function buildSalesChart(Collection $payments, Carbon $from, Carbon $to, string $interval): array
    {
        $format = match ($interval) {
            'monthly' => 'Y-m',
            'weekly'  => 'Y-\WW',
            default   => 'Y-m-d',
        };

        $grouped = $payments->groupBy(fn (Payment $p) => $p->payment_date->format($format))
            ->map(fn ($g) => (float) $g->sum(fn (Payment $p) => (float) ($p->invoice->final_total ?? $p->amount_paid)));

        $period = match ($interval) {
            'monthly' => CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to),
            'weekly'  => CarbonPeriod::create($from->copy()->startOfWeek(), '1 week', $to),
            default   => CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to),
        };

        $labels = [];
        $data   = [];
        $count  = 0;

        foreach ($period as $date) {
            // Safety cap so an unusually wide custom range can't blow up the
            // chart payload — does not affect the totals above, only the chart.
            if (++$count > 366) {
                break;
            }

            $labels[] = match ($interval) {
                'monthly' => $date->format('M Y'),
                'weekly'  => 'Wk of '.$date->format('M d'),
                default   => $date->format('M d'),
            };
            $data[] = round($grouped->get($date->format($format), 0.0), 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /* ── Inventory Report (REQ059) ────────────────────────────────── */

    public function inventoryReport(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters['date_range'] ?? 'month', $filters['start_date'] ?? null, $filters['end_date'] ?? null);
        $category = $filters['category'] ?? 'all'; // all | rtc | beverage
        $search   = trim((string) ($filters['search'] ?? ''));

        $rtcItems      = InventoryItem::rtc()->orderBy('item_name')->get();
        $beverageItems = InventoryItem::beverage()->orderBy('item_name')->get();

        if ($search !== '') {
            $rtcItems      = $rtcItems->filter(fn (InventoryItem $i) => stripos($i->item_name, $search) !== false)->values();
            $beverageItems = $beverageItems->filter(fn (InventoryItem $i) => stripos($i->item_name, $search) !== false)->values();
        }

        $rtcRows = $rtcItems->map(function (InventoryItem $i) {
            $portionSize = (float) $i->portion_size;

            return [
                'item_name'           => $i->item_name,
                'category'            => $i->category,
                'current_stock'       => (float) $i->quantity,
                'unit'                => $i->unit,
                'threshold'           => (float) $i->reorder_level,
                'rtc_units_available' => (float) $i->rtc_servings,
                'equivalent_servings' => $portionSize > 0 ? round(((float) $i->quantity) / $portionSize, 2) : null,
                'status'              => $i->stock_status_label,
                'status_key'          => $i->stock_status,
            ];
        })->values();

        $beverageRows = $beverageItems->map(fn (InventoryItem $i) => [
            'item_name'  => $i->item_name,
            'category'   => $i->category,
            'quantity'   => (float) $i->quantity,
            'threshold'  => (float) $i->reorder_level,
            'status'     => $i->stock_status_label,
            'status_key' => $i->stock_status,
        ])->values();

        $summary = [
            'total_items'        => InventoryItem::count(),
            'low_stock_items'    => InventoryItem::lowStock()->count(),
            'out_of_stock_items' => InventoryItem::outOfStock()->count(),
            'rtc_items'          => InventoryItem::rtc()->count(),
            'beverage_items'     => InventoryItem::beverage()->count(),
        ];

        $stockIns = PurchaseOrder::with(['inventoryItem', 'recorder.staff'])
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->when($category !== 'all', fn ($q) => $q->where('po_type', $category))
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'date'          => $po->purchase_date?->format('Y-m-d'),
                'item'          => $po->inventoryItem?->item_name ?? '—',
                'qty_purchased' => (float) $po->quantity_purchased,
                'qty_added'     => (float) ($po->new_quantity - $po->previous_quantity),
                'type'          => 'Stock-In',
                'admin'         => $po->recorder?->name ?: '—',
                'timestamp'     => $po->created_at,
            ]);

        $conversions = ConversionLog::with(['inventoryItem', 'converter.staff'])
            ->whereBetween('created_at', [$from, $to])
            ->when($category !== 'all', fn ($q) => $q->whereHas('inventoryItem', fn ($iq) => $iq->where('item_type', $category)))
            ->get()
            ->map(fn (ConversionLog $c) => [
                'date'          => $c->created_at?->format('Y-m-d'),
                'item'          => $c->inventoryItem?->item_name ?? '—',
                'qty_purchased' => null,
                'qty_added'     => (float) ($c->new_rtc_servings - $c->previous_rtc_servings),
                'type'          => 'RTC Conversion',
                'admin'         => $c->converter?->name ?: '—',
                'timestamp'     => $c->created_at,
            ]);

        $adjustments = InventoryAdjustment::with(['inventoryItem', 'adjuster.staff'])
            ->whereBetween('created_at', [$from, $to])
            ->when($category !== 'all', fn ($q) => $q->whereHas('inventoryItem', fn ($iq) => $iq->where('item_type', $category)))
            ->get()
            ->map(fn (InventoryAdjustment $a) => [
                'date'          => $a->created_at?->format('Y-m-d'),
                'item'          => $a->inventoryItem?->item_name ?? '—',
                'qty_purchased' => null,
                'qty_added'     => (float) $a->quantity_adjusted,
                'type'          => 'Adjustment ('.$a->getTypeLabel().')',
                'admin'         => $a->adjuster?->name ?: '—',
                'timestamp'     => $a->created_at,
            ]);

        $transactions = $stockIns->concat($conversions)->concat($adjustments);

        if ($search !== '') {
            $transactions = $transactions->filter(fn ($t) => stripos($t['item'], $search) !== false);
        }

        $transactions = $transactions->sortByDesc('timestamp')->values();

        return [
            'summary'       => $summary,
            'rtc_rows'      => $rtcRows,
            'beverage_rows' => $beverageRows,
            'transactions'  => $transactions,
            'meta'          => ['from' => $from, 'to' => $to, 'category' => $category],
        ];
    }

    /* ── Order Performance Report (REQ060) ────────────────────────── */

    public function orderPerformanceReport(array $filters): array
    {
        [$from, $to] = $this->resolveDateRange($filters['date_range'] ?? 'today', $filters['start_date'] ?? null, $filters['end_date'] ?? null);
        $orderType      = $filters['order_type'] ?? 'all';
        $menuCategoryId = $filters['menu_category_id'] ?? 'all';
        $search         = trim((string) ($filters['search'] ?? ''));

        $query = Order::whereBetween('created_at', [$from, $to])
            ->with(['orderStatus', 'customer', 'dineInOrder', 'onlineOrder', 'invoice.payment']);

        if ($orderType !== 'all') {
            $query->where('order_type', $orderType);
        }
        if ($menuCategoryId !== 'all') {
            $query->whereHas('details.menuItem', fn ($q) => $q->where('category_id', $menuCategoryId));
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->get();

        $statusCounts = $orders->groupBy(fn (Order $o) => $o->status_name)->map->count();

        $summary = [
            'total_orders'     => $orders->count(),
            'pending_orders'   => $statusCounts->get('Pending', 0),
            'preparing_orders' => $statusCounts->get('Processing', 0),
            'ready_orders'     => $statusCounts->get('Ready', 0),
            'completed_orders' => $statusCounts->get('Completed', 0),
            'cancelled_orders' => $statusCounts->get('Cancelled', 0),
        ];

        $rows = $orders->map(function (Order $o) {
            $completion = $o->invoice?->payment?->payment_date ?? $o->served_at ?? $o->packaged_at;

            return [
                'order_number'    => $o->order_number,
                'date'            => $o->created_at?->format('Y-m-d'),
                'order_type'      => $o->order_type_label,
                'customer'        => $o->customer_name,
                'table_number'    => $o->dineInOrder?->table_number ?? '—',
                'order_status'    => $o->kitchen_status_label,
                'status_color'    => $o->status_color,
                'payment_status'  => $o->payment_status_label,
                'total_amount'    => (float) $o->total_amount,
                'created_time'    => $o->created_at?->format('h:i A'),
                'completion_time' => $completion?->format('M d, h:i A') ?? '—',
            ];
        })->values();

        $orderTypeChart = [
            'labels' => ['Online', 'Dine-In', 'Take-Out'],
            'data'   => [
                $orders->where('order_type', 'online')->count(),
                $orders->where('order_type', 'dine_in')->count(),
                $orders->where('order_type', 'takeout')->count(),
            ],
        ];

        $statusOrder  = ['Pending', 'Processing', 'Ready', 'Served', 'Packaged', 'Completed', 'Cancelled'];
        $statusColors = OrderStatus::pluck('color', 'status_name');
        $orderStatusChart = [
            'labels' => $statusOrder,
            'data'   => array_map(fn ($s) => $statusCounts->get($s, 0), $statusOrder),
            'colors' => array_map(fn ($s) => $statusColors->get($s, '#6B7280'), $statusOrder),
        ];

        // Timing metrics only draw on orders that actually have both
        // timestamps for that leg of the journey — never fabricated.
        $processing = $orders->filter(fn (Order $o) => $o->ready_at)
            ->map(fn (Order $o) => $o->created_at->diffInMinutes($o->ready_at));
        $handoff = $orders->filter(fn (Order $o) => $o->ready_at && ($o->served_at || $o->packaged_at))
            ->map(fn (Order $o) => $o->ready_at->diffInMinutes($o->served_at ?? $o->packaged_at));
        $completionDurations = $orders->filter(fn (Order $o) => $o->invoice?->payment)
            ->map(fn (Order $o) => $o->created_at->diffInMinutes($o->invoice->payment->payment_date));

        $timing = [
            'avg_processing_minutes'  => $processing->isNotEmpty() ? round($processing->avg(), 1) : null,
            'avg_preparation_minutes' => $handoff->isNotEmpty() ? round($handoff->avg(), 1) : null,
            'avg_completion_minutes'  => $completionDurations->isNotEmpty() ? round($completionDurations->avg(), 1) : null,
        ];

        return [
            'summary'            => $summary,
            'rows'               => $rows,
            'order_type_chart'   => $orderTypeChart,
            'order_status_chart' => $orderStatusChart,
            'timing'             => $timing,
            'meta'               => [
                'from' => $from, 'to' => $to,
                'order_type' => $orderType, 'menu_category_id' => $menuCategoryId,
            ],
        ];
    }
}
