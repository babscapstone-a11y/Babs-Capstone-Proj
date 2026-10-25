<?php

namespace App\Services;

use App\Models\Conversion;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\StockIn;
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
            ->with(['order.details.menuItem', 'order.walkInOrder', 'cashier.staff', 'modeOfPayment', 'invoice.discount']);

        if ($category !== 'all') {
            $paymentsQuery->whereHas('order.details.menuItem', fn ($q) => $q->ofType($category));
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

        // Total Purchases: what was spent stocking in inventory over the same period.
        // Menu categories map onto stock types (food is made from RTC raw meat).
        $totalPurchases = (float) StockIn::query()
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->when($category !== 'all', fn ($q) => $q->ofType($category === 'beverage' ? 'beverage' : 'rtc'))
            ->sum('total_cost');

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
                ->with(['orderStatus', 'details', 'walkInOrder']);

            if ($category !== 'all') {
                $excludedQuery->whereHas('details.menuItem', fn ($q) => $q->ofType($category));
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
                'total_purchases'     => $totalPurchases,
                'total_discounts'     => $totalDiscounts,
                'net_sales'           => $netSales,
                'gross_profit'        => $netSales - $totalPurchases,
            ],
            'rows'      => $allRows,
            'chart'     => $this->buildSalesChart($payments, $from, $to, $interval),
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

    /**
     * Ranks menu items by units sold across the paid orders already resolved
     * for the Order Performance Report — same date/order type/category/search
     * filters backing the summary and table, so the ranking never drifts from
     * what the Administrator is already looking at.
     */
    private function buildTopSellingItems(Collection $orders, string $menuCategoryId, int $limit = 10): array
    {
        $lines = $orders->where('payment_status', 'paid')->flatMap(fn (Order $o) => $o->details);

        if ($menuCategoryId !== 'all') {
            $lines = $lines->filter(fn (OrderDetail $d) => (string) $d->menuItem?->category_id === $menuCategoryId);
        }

        return $lines->groupBy(fn (OrderDetail $d) => $d->menu_item_id ?? $d->item_name)
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'menu_item_id'  => $first->menu_item_id,
                    'item_name'     => $first->menuItem?->menu_name ?? $first->item_name,
                    'category'      => $first->menuItem?->item_type ?? '—',
                    'quantity_sold' => (int) $group->sum('quantity'),
                    'revenue'       => (float) $group->sum('subtotal'),
                ];
            })
            ->sortByDesc('quantity_sold')
            ->take($limit)
            ->values()
            ->all();
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

        $rtcRows = $rtcItems->map(fn (InventoryItem $i) => [
            'item_name'     => $i->item_name,
            'current_stock' => (float) $i->quantity,
            'unit'          => $i->unit,
            'threshold'     => (float) $i->reorder_level,
            'status'        => $i->stock_status_label,
            'status_key'    => $i->stock_status,
        ])->values();

        $beverageRows = $beverageItems->map(fn (InventoryItem $i) => [
            'item_name'  => $i->item_name,
            'quantity'   => (float) $i->quantity,
            'threshold'  => (float) $i->reorder_level,
            'status'     => $i->stock_status_label,
            'status_key' => $i->stock_status,
        ])->values();

        // RTC items: menu dishes tracked by ready-to-cook servings (same list as the RTC Inventory page).
        $rtcItemRows = MenuItem::rtcTracked()->with('rtcStock.inventoryItem')->orderBy('menu_name')->get()
            ->when($search !== '', fn (Collection $c) => $c->filter(fn (MenuItem $m) => stripos($m->menu_name, $search) !== false))
            ->map(fn (MenuItem $m) => [
                'item_name'    => $m->menu_name,
                'servings'     => (float) $m->rtc_servings,
                'portion_size' => $m->rtc_quantity !== null ? (float) $m->rtc_quantity : null,
                'portion_unit' => $m->rtc_unit,
                'status'       => match ($m->rtc_servings_status) {
                    'low_stock'    => 'Low Servings',
                    'out_of_stock' => 'Out of Servings',
                    default        => 'Available',
                },
                'status_key'   => $m->rtc_servings_status,
            ])->values();

        // Same counts as the Dashboard's Inventory Items card: the total is raw meat +
        // beverages + RTC items; low/out of stock cover raw meat and beverages only.
        $rawMeatCount  = InventoryItem::rtc()->count();
        $beverageCount = InventoryItem::beverage()->count();
        $rtcItemCount  = MenuItem::rtcTracked()->count();

        $summary = [
            'total_items'        => $rawMeatCount + $beverageCount + $rtcItemCount,
            'raw_meat_items'     => $rawMeatCount,
            'beverage_items'     => $beverageCount,
            'rtc_items'          => $rtcItemCount,
            'low_stock_items'    => InventoryItem::lowStock()->count(),
            'out_of_stock_items' => InventoryItem::outOfStock()->count(),
        ];

        $stockIns = StockIn::with(['inventoryItem', 'recorder.staff'])
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->when($category !== 'all', fn ($q) => $q->ofType($category))
            ->get()
            ->map(fn (StockIn $po) => [
                'date'          => $po->purchase_date?->format('Y-m-d'),
                'item'          => $po->inventoryItem?->item_name ?? '—',
                'qty_purchased' => (float) $po->quantity_purchased,
                'qty_added'     => (float) ($po->new_quantity - $po->previous_quantity),
                'type'          => 'Stock-In',
                'admin'         => $po->recorder?->name ?: '—',
                'timestamp'     => $po->created_at,
            ]);

        $conversions = Conversion::with(['lines.inventoryItem', 'converter.staff'])
            ->whereBetween('created_at', [$from, $to])
            ->when($category !== 'all', fn ($q) => $q->whereHas('lines.inventoryItem', fn ($iq) => $iq->where('item_type', $category)))
            ->get()
            ->map(fn (Conversion $c) => [
                'date'          => $c->created_at?->format('Y-m-d'),
                'item'          => $c->meat_names,
                'qty_purchased' => null,
                'qty_added'     => (float) ($c->new_servings - $c->previous_servings),
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
            'rtc_item_rows' => $rtcItemRows,
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
            ->with(['orderStatus', 'customer', 'walkInOrder', 'onlineOrder', 'invoice.payment', 'details.menuItem']);

        if ($orderType !== 'all') {
            $query->ofServiceType($orderType);
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

        // Online orders are either Advance Orders or same-day Pick-Ups, but checkout saves
        // both as order_type "online". An Advance Order is the one with a headcount
        // (party_size) or a pick-up day later than the day it was placed.
        $online  = $orders->where('order_type', 'online');
        $advance = $online->filter(fn (Order $o) => $o->party_size !== null
            || ($o->pickup_at && ! $o->pickup_at->isSameDay($o->created_at)));

        $summary = [
            'total_orders'   => $orders->count(),
            'dine_in_orders' => $orders->filter(fn (Order $o) => $o->service_type === 'dine_in')->count(),
            'takeout_orders' => $orders->filter(fn (Order $o) => $o->service_type === 'takeout')->count(),
            'advance_orders' => $advance->count(),
            'pickup_orders'  => $online->count() - $advance->count(),
        ];

        $rows = $orders->map(function (Order $o) {
            $completion = $o->invoice?->payment?->payment_date ?? $o->handed_off_at;

            return [
                'order_number'    => $o->order_number,
                'date'            => $o->created_at?->format('Y-m-d'),
                'order_type'      => $o->order_type_label,
                'customer'        => $o->customer_name,
                'table_number'    => $o->walkInOrder?->table_number ?? '—',
                'order_status'    => $o->kitchen_status_label,
                'status_color'    => $o->status_color,
                'payment_status'  => $o->payment_status_label,
                'total_amount'    => (float) $o->total_amount,
                'created_time'    => $o->created_at?->format('h:i A'),
                'completion_time' => $completion?->format('M d, h:i A') ?? '—',
            ];
        })->values();

        $orderTypeChart = [
            'labels' => ['Dine-In', 'Take-Out', 'Advance', 'Pick-up'],
            'data'   => [
                $summary['dine_in_orders'],
                $summary['takeout_orders'],
                $summary['advance_orders'],
                $summary['pickup_orders'],
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
        $handoff = $orders->filter(fn (Order $o) => $o->ready_at && $o->handed_off_at)
            ->map(fn (Order $o) => $o->ready_at->diffInMinutes($o->handed_off_at));
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
            'top_items'          => $this->buildTopSellingItems($orders, (string) $menuCategoryId),
            'order_status_chart' => $orderStatusChart,
            'timing'             => $timing,
            'meta'               => [
                'from' => $from, 'to' => $to,
                'order_type' => $orderType, 'menu_category_id' => $menuCategoryId,
            ],
        ];
    }
}
