<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ucfirst($type) }} Report – BAB'S RESTO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', system-ui, sans-serif; background: #F8FAFC; color: #111827; font-size: 13px; }

    .screen-only { display: block; }
    .print-only { display: none; }

    .screen-toolbar { background: #111827; color: #fff; padding: .75rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
    .screen-toolbar .brand { font-size: .9rem; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
    .screen-toolbar .brand-badge { width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg,#DC2626,#F97316); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .75rem; }
    .screen-toolbar .actions { display: flex; gap: .65rem; }
    .tbtn { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem 1rem; border-radius: 8px; font-size: .8rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all .18s; text-decoration: none; }
    .tbtn-primary { background: #DC2626; color: #fff; }
    .tbtn-primary:hover { background: #B91C1C; }
    .tbtn-outline { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; }
    .tbtn-outline:hover { background: rgba(255,255,255,.18); }

    .doc-wrapper { max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem; }
    .document { background: #fff; border-radius: 12px; border: 1px solid rgba(17,24,39,0.1); box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 48px 56px; margin-bottom: 2rem; }

    .doc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 2px solid #DC2626; }
    .resto-brand { display: flex; align-items: center; gap: .85rem; }
    .brand-logo { width: 54px; height: 54px; border-radius: 12px; background: linear-gradient(135deg,#DC2626,#F97316); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 1.2rem; flex-shrink: 0; }
    .brand-info .name { font-size: 1.25rem; font-weight: 900; color: #111827; letter-spacing: -.01em; }
    .brand-info .address { font-size: .75rem; color: #6B7280; margin-top: .15rem; line-height: 1.5; }
    .doc-meta { text-align: right; }
    .doc-meta .doc-type { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #DC2626; margin-bottom: .35rem; }
    .doc-meta .doc-name { font-size: 1.3rem; font-weight: 900; color: #111827; letter-spacing: -.01em; }
    .doc-meta .doc-date { font-size: .78rem; color: #6B7280; margin-top: .25rem; }

    .details-row { display: flex; gap: 2rem; margin-bottom: 1.75rem; padding: 1rem 1.25rem; background: #F8FAFC; border-radius: 10px; border: 1px solid rgba(17,24,39,0.07); flex-wrap: wrap; }
    .detail-item .dl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9CA3AF; margin-bottom: .2rem; }
    .detail-item .dv { font-size: .88rem; font-weight: 700; color: #111827; }

    .section-title { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #6B7280; margin: 1.5rem 0 .65rem; display: flex; align-items: center; gap: .5rem; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: rgba(17,24,39,0.08); }

    .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .85rem; margin-bottom: .5rem; }
    .sum-card { background: #F8FAFC; border: 1px solid rgba(17,24,39,0.07); border-radius: 10px; padding: .75rem .9rem; }
    .sum-card .sl { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9CA3AF; margin-bottom: .25rem; }
    .sum-card .sv { font-size: 1.15rem; font-weight: 800; color: #111827; }

    .rpt-tbl { width: 100%; border-collapse: collapse; margin-bottom: .5rem; font-size: .76rem; }
    .rpt-tbl thead tr { background: #111827; }
    .rpt-tbl th { padding: .5rem .7rem; text-align: left; font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,0.75); }
    .rpt-tbl td { padding: .55rem .7rem; border-bottom: 1px solid rgba(17,24,39,0.07); color: #111827; vertical-align: middle; }

    .doc-footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px dashed rgba(17,24,39,0.12); text-align: center; font-size: .68rem; color: #9CA3AF; line-height: 1.7; }

    @media print {
        .screen-only, .screen-toolbar { display: none !important; }
        .print-only { display: block !important; }
        body { background: #fff; font-size: 11px; }
        .doc-wrapper { max-width: 100%; margin: 0; padding: 0; }
        .document { border-radius: 0; border: none; box-shadow: none; padding: 24px 32px; margin: 0; }
        @page { margin: 1cm; size: A4 landscape; }
        .rpt-tbl { font-size: 9.5px; }
    }
    </style>
</head>
<body>

<div class="screen-only screen-toolbar">
    <div class="brand">
        <div class="brand-badge">BR</div>
        <span>BAB'S RESTO &nbsp;/ {{ ucfirst($type) }} Report</span>
    </div>
    <div class="actions">
        <a href="{{ route('reports.index', array_merge(['type' => $type], request()->query())) }}" class="tbtn tbtn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="tbtn tbtn-primary"><i class="fas fa-print"></i> Print / Save as PDF</button>
    </div>
</div>

<div class="doc-wrapper">
    <div class="document">

        <div class="doc-header">
            <div class="resto-brand">
                <div class="brand-logo">BR</div>
                <div class="brand-info">
                    <div class="name">BAB'S RESTO</div>
                    <div class="address">Restaurant & Catering Services<br>Contact: (032) 123-4567</div>
                </div>
            </div>
            <div class="doc-meta">
                <div class="doc-type">System Report</div>
                <div class="doc-name">
                    @if($type === 'sales') Sales Report
                    @elseif($type === 'inventory') Inventory Report
                    @else Order Performance Report
                    @endif
                </div>
                <div class="doc-date">Generated {{ now()->format('F d, Y h:i A') }}</div>
            </div>
        </div>

        @php
            $dateRangeLabels = ['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'custom' => 'Custom Range'];
            $rangeLabel = $dateRangeLabels[$filters['date_range']] ?? 'Today';
            $categoryLabels = ['all' => 'All Categories', 'food' => 'Food Category', 'beverage' => 'Beverage Category', 'rtc' => 'RTC Raw Meat'];
            $categoryLabel = $categoryLabels[$filters['category']] ?? 'All Categories';
        @endphp

        <div class="details-row">
            <div class="detail-item"><div class="dl">Date Range</div><div class="dv">{{ $rangeLabel }} ({{ $data['meta']['from']->format('M d, Y') }} – {{ $data['meta']['to']->format('M d, Y') }})</div></div>
            @if($type === 'orders')
                <div class="detail-item"><div class="dl">Order Type</div><div class="dv">{{ $filters['order_type'] === 'all' ? 'All Types' : ucfirst(str_replace('_', '-', $filters['order_type'])) }}</div></div>
            @else
                <div class="detail-item"><div class="dl">Category</div><div class="dv">{{ $categoryLabel }}</div></div>
            @endif
            <div class="detail-item"><div class="dl">Generated By</div><div class="dv">{{ auth()->user()->name ?: auth()->user()->username }} (Administrator)</div></div>
            <div class="detail-item"><div class="dl">Generated On</div><div class="dv">{{ now()->format('M d, Y h:i A') }}</div></div>
        </div>

        <div class="section-title">Summary</div>

        @if($type === 'sales')
            <div class="summary-cards">
                <div class="sum-card"><div class="sl">Total Sales</div><div class="sv">₱{{ number_format($data['summary']['total_sales'], 2) }}</div></div>
                <div class="sum-card"><div class="sl">Transactions</div><div class="sv">{{ number_format($data['summary']['transaction_count']) }}</div></div>
                <div class="sum-card"><div class="sl">Avg. Transaction</div><div class="sv">₱{{ number_format($data['summary']['average_transaction'], 2) }}</div></div>
                <div class="sum-card"><div class="sl">Total Discounts</div><div class="sv">₱{{ number_format($data['summary']['total_discounts'], 2) }}</div></div>
                <div class="sum-card"><div class="sl">Net Sales</div><div class="sv">₱{{ number_format($data['summary']['net_sales'], 2) }}</div></div>
            </div>

            <div class="section-title">Detailed Report — Sales Transactions</div>
            <table class="rpt-tbl">
                <thead><tr><th>Txn #</th><th>Order #</th><th>Date</th><th>Time</th><th>Type</th><th>Cashier</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Method</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td>{{ $row['transaction_number'] ?? '—' }}</td><td>{{ $row['order_number'] }}</td><td>{{ $row['date'] }}</td><td>{{ $row['time'] }}</td>
                            <td>{{ $row['order_type'] }}</td><td>{{ $row['cashier'] }}</td><td>₱{{ number_format($row['subtotal'], 2) }}</td>
                            <td>₱{{ number_format($row['discount'], 2) }}</td><td>₱{{ number_format($row['total'], 2) }}</td><td>{{ $row['payment_method'] }}</td><td>{{ $row['payment_status'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" style="text-align:center;color:#9CA3AF;padding:1.25rem">No records found for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($type === 'inventory')
            <div class="summary-cards">
                <div class="sum-card"><div class="sl">Total Items</div><div class="sv">{{ $data['summary']['total_items'] }}</div></div>
                <div class="sum-card"><div class="sl">Low Stock</div><div class="sv">{{ $data['summary']['low_stock_items'] }}</div></div>
                <div class="sum-card"><div class="sl">Out of Stock</div><div class="sv">{{ $data['summary']['out_of_stock_items'] }}</div></div>
                <div class="sum-card"><div class="sl">RTC Items</div><div class="sv">{{ $data['summary']['rtc_items'] }}</div></div>
                <div class="sum-card"><div class="sl">Beverage Items</div><div class="sv">{{ $data['summary']['beverage_items'] }}</div></div>
            </div>

            @if($filters['category'] !== 'beverage')
                <div class="section-title">Detailed Report — RTC Raw Meat</div>
                <table class="rpt-tbl">
                    <thead><tr><th>Item</th><th>Current Stock</th><th>Unit</th><th>Threshold</th><th>RTC Units</th><th>Equiv. Servings</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['rtc_rows'] as $row)
                            <tr><td>{{ $row['item_name'] }}</td><td>{{ number_format($row['current_stock'], 2) }}</td><td>{{ $row['unit'] }}</td><td>{{ number_format($row['threshold'], 2) }}</td><td>{{ number_format($row['rtc_units_available'], 2) }}</td><td>{{ $row['equivalent_servings'] !== null ? number_format($row['equivalent_servings'], 2) : '—' }}</td><td>{{ $row['status'] }}</td></tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;color:#9CA3AF;padding:1rem">No RTC items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            @if($filters['category'] !== 'rtc')
                <div class="section-title">Detailed Report — Beverages</div>
                <table class="rpt-tbl">
                    <thead><tr><th>Beverage</th><th>Category</th><th>Quantity</th><th>Threshold</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['beverage_rows'] as $row)
                            <tr><td>{{ $row['item_name'] }}</td><td>{{ $row['category'] }}</td><td>{{ number_format($row['quantity'], 2) }}</td><td>{{ number_format($row['threshold'], 2) }}</td><td>{{ $row['status'] }}</td></tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:#9CA3AF;padding:1rem">No beverage items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif

            <div class="section-title">Inventory Transactions</div>
            <table class="rpt-tbl">
                <thead><tr><th>Date</th><th>Item</th><th>Qty Purchased</th><th>Qty Added/Adjusted</th><th>Type</th><th>Administrator</th></tr></thead>
                <tbody>
                    @forelse($data['transactions'] as $t)
                        <tr><td>{{ $t['date'] }}</td><td>{{ $t['item'] }}</td><td>{{ $t['qty_purchased'] !== null ? number_format($t['qty_purchased'], 2) : '—' }}</td><td>{{ number_format($t['qty_added'], 2) }}</td><td>{{ $t['type'] }}</td><td>{{ $t['admin'] }}</td></tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:#9CA3AF;padding:1rem">No transactions found for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @else
            <div class="summary-cards">
                <div class="sum-card"><div class="sl">Total Orders</div><div class="sv">{{ $data['summary']['total_orders'] }}</div></div>
                <div class="sum-card"><div class="sl">Pending</div><div class="sv">{{ $data['summary']['pending_orders'] }}</div></div>
                <div class="sum-card"><div class="sl">Preparing</div><div class="sv">{{ $data['summary']['preparing_orders'] }}</div></div>
                <div class="sum-card"><div class="sl">Ready</div><div class="sv">{{ $data['summary']['ready_orders'] }}</div></div>
                <div class="sum-card"><div class="sl">Completed</div><div class="sv">{{ $data['summary']['completed_orders'] }}</div></div>
                <div class="sum-card"><div class="sl">Cancelled</div><div class="sv">{{ $data['summary']['cancelled_orders'] }}</div></div>
            </div>

            <div class="section-title">Detailed Report — Orders</div>
            <table class="rpt-tbl">
                <thead><tr><th>Order #</th><th>Date</th><th>Type</th><th>Customer</th><th>Table #</th><th>Status</th><th>Payment</th><th>Total</th><th>Created</th><th>Completion</th></tr></thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td>{{ $row['order_number'] }}</td><td>{{ $row['date'] }}</td><td>{{ $row['order_type'] }}</td><td>{{ $row['customer'] }}</td>
                            <td>{{ $row['table_number'] }}</td><td>{{ $row['order_status'] }}</td><td>{{ $row['payment_status'] }}</td>
                            <td>₱{{ number_format($row['total_amount'], 2) }}</td><td>{{ $row['created_time'] }}</td><td>{{ $row['completion_time'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align:center;color:#9CA3AF;padding:1.25rem">No orders found for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="doc-footer">
            <strong>BAB'S RESTO</strong> — {{ ucfirst($type) }} Report<br>
            This document was generated on {{ now()->format('F d, Y h:i A') }} by {{ auth()->user()->name ?: auth()->user()->username }}.<br>
            Figures are computed directly from live system records at the time of generation.
        </div>
    </div>
</div>

</body>
</html>
