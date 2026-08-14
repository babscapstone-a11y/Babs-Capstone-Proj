<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Category;
use App\Services\ReportService;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    /**
     * Report Generation Module home — type selector, filters, summary
     * cards, charts, and the detailed table, all driven by query params
     * so Apply Filters / Reset Filters / Print / Export can all share the
     * exact same URL shape.
     */
    public function index(ReportFilterRequest $request): View
    {
        $type = $request->validated('type') ?? 'sales';
        $data = $this->buildReportData($type, $request->filters());

        return view('reports.index', [
            'type'           => $type,
            'filters'        => $request->filters(),
            'data'           => $data,
            'menuCategories' => Category::where('is_active', true)->orderBy('category_name')->get(),
        ]);
    }

    /**
     * Standalone print-friendly rendition of the currently filtered report
     * (opened in a new tab from the "Print Report" button).
     */
    public function print(ReportFilterRequest $request): View
    {
        $type = $request->validated('type') ?? 'sales';
        $data = $this->buildReportData($type, $request->filters());

        return view('reports.print', [
            'type'    => $type,
            'filters' => $request->filters(),
            'data'    => $data,
        ]);
    }

    /**
     * CSV export of the detailed report table for the currently applied
     * filters (date range, category, search) — never the unfiltered set.
     */
    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $type = $request->validated('type') ?? 'sales';
        $data = $this->buildReportData($type, $request->filters());

        $filename = sprintf('babs-resto-%s-report-%s.csv', Str::slug($type), now()->format('Ymd-His'));

        [$header, $rows] = match ($type) {
            'inventory' => [
                ['Section', 'Item Name', 'Category', 'Current Stock / Qty', 'Unit', 'Threshold', 'RTC Units Available', 'Equivalent Servings', 'Status'],
                collect($data['rtc_rows'])->map(fn ($r) => ['RTC Raw Meat', $r['item_name'], $r['category'], $r['current_stock'], $r['unit'], $r['threshold'], $r['rtc_units_available'], $r['equivalent_servings'], $r['status']])
                    ->concat(collect($data['beverage_rows'])->map(fn ($r) => ['Beverage', $r['item_name'], $r['category'], $r['quantity'], '', $r['threshold'], '', '', $r['status']])),
            ],
            'orders' => [
                ['Order Number', 'Date', 'Order Type', 'Customer', 'Table #', 'Order Status', 'Payment Status', 'Total Amount', 'Created Time', 'Completion Time'],
                collect($data['rows'])->map(fn ($r) => [$r['order_number'], $r['date'], $r['order_type'], $r['customer'], $r['table_number'], $r['order_status'], $r['payment_status'], $r['total_amount'], $r['created_time'], $r['completion_time']]),
            ],
            default => [
                ['Transaction Number', 'Order Number', 'Date', 'Time', 'Order Type', 'Cashier', 'Subtotal', 'Discount', 'Total Amount', 'Payment Method', 'Payment Status'],
                collect($data['rows'])->map(fn ($r) => [$r['transaction_number'] ?? '—', $r['order_number'], $r['date'], $r['time'], $r['order_type'], $r['cashier'], $r['subtotal'], $r['discount'], $r['total'], $r['payment_method'], $r['payment_status']]),
            ],
        };

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReportData(string $type, array $filters): array
    {
        return match ($type) {
            'inventory' => $this->reports->inventoryReport($filters),
            'orders'    => $this->reports->orderPerformanceReport($filters),
            default     => $this->reports->salesReport($filters),
        };
    }
}
