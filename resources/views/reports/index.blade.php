@extends('layouts.admin')

@section('title', 'Report Generation')
@section('page-title', 'Report Generation')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <span>Reports</span>
@endsection

@php
    $dateRangeLabels = [
        'today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week',
        'month' => 'This Month', 'year' => 'This Year', 'custom' => 'Custom Range',
    ];
    $rangeLabel = $dateRangeLabels[$filters['date_range']] ?? 'Today';
    if ($filters['date_range'] === 'custom') {
        $rangeLabel = $data['meta']['from']->format('M d, Y').' – '.$data['meta']['to']->format('M d, Y');
    }
    $categoryLabels = ['all' => 'All Categories', 'food' => 'Food Category', 'beverage' => 'Beverage Category', 'rtc' => 'RTC Raw Meat'];
@endphp

@section('styles')
<style>
    /* Inventory "Print Report" pop-up: which sections to print */
    .print-section-options { display: flex; flex-direction: column; gap: .5rem; margin-bottom: 1.25rem; }
    .print-section-options label {
        display: flex; align-items: center; gap: .6rem; cursor: pointer;
        padding: .65rem .85rem; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: .88rem; font-weight: 600; color: var(--dark);
    }
    .print-section-options label:has(input:checked) { border-color: var(--primary); background: rgba(220,38,38,0.05); }
    .print-section-options input { width: 1rem; height: 1rem; accent-color: var(--primary); }
    .print-section-options i { color: var(--muted); width: 1rem; text-align: center; }
    .print-section-error { color: var(--primary); font-size: .8rem; text-align: center; margin: -.5rem 0 1rem; }

    /* Custom Range dates: calendar in the admin red, field sized like the other filters */
    :root { --cd-accent: var(--primary); --cd-accent-rgb: 220,38,38; }
    .cd-field.report-date-btn { width: 100%; height: auto; padding: .68rem .9rem; border-radius: 12px; font-size: .9rem; }

    /* Report type tabs */
    .report-tabs { display: flex; gap: .25rem; margin-bottom: 1.25rem; border-bottom: 1.5px solid var(--border); overflow-x: auto; overflow-y: hidden; }
    .report-tab {
        display: inline-flex; align-items: center; gap: .5rem; padding: .7rem 1.1rem;
        border-bottom: 2.5px solid transparent; border-radius: 10px 10px 0 0; white-space: nowrap;
        font-size: .9rem; font-weight: 600; color: var(--muted); text-decoration: none; transition: color .15s, background .15s;
    }
    .report-tab i { font-size: .9rem; }
    .report-tab:hover { color: var(--dark); background: rgba(var(--ink-rgb),0.04); }
    .report-tab.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 700; }

    .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; align-items: end; }

    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
    .summary-grid.three-col { grid-template-columns: repeat(3, 1fr); }
    .stat-card { background: var(--surface); border-radius: 14px; border: 1px solid var(--border); padding: 1.15rem 1.3rem; box-shadow: 0 2px 12px rgba(var(--ink-rgb),0.05); }
    .stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .4rem; }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--dark); line-height: 1.1; }
    .stat-card.accent-primary .stat-value { color: var(--primary); }
    .stat-card.accent-amber .stat-value { color: var(--accent); }
    .stat-card.accent-green .stat-value { color: var(--green-600); }

    .chart-box { position: relative; height: 300px; }

    .report-table-wrap { overflow-x: auto; }
    table.report-table { width: 100%; border-collapse: collapse; font-size: .84rem; white-space: nowrap; }
    table.report-table thead th { text-align: left; padding: .7rem .9rem; background: var(--surface-2); color: var(--muted); font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); position: sticky; top: 0; }
    table.report-table tbody td { padding: .7rem .9rem; border-bottom: 1px solid var(--border); color: var(--dark); }
    table.report-table tbody tr:hover td { background: var(--surface-2); }
    table.report-table tbody tr.row-excluded td { color: #9CA3AF; font-style: italic; }
    .table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .table-search { max-width: 320px; flex: 1; min-width: 220px; }
    .empty-row td { text-align: center; padding: 2rem; color: var(--muted); }

    .badge-instock, .badge-available { background: rgba(22,163,74,0.1); color: var(--green-600); }
    .badge-lowstock, .badge-low_stock { background: rgba(245,158,11,0.12); color: var(--amber-700); }
    .badge-outofstock, .badge-out_of_stock { background: rgba(220,38,38,0.1); color: var(--red-600); }
    .badge-paid { background: rgba(22,163,74,0.1); color: var(--green-600); }
    .badge-cancelled { background: rgba(220,38,38,0.1); color: var(--red-600); }
    .badge-failed, .badge-refunded { background: rgba(107,114,128,0.1); color: var(--muted); }
    .status-dot-badge { display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 600; padding: .22rem .65rem; border-radius: 50px; }

    .section-heading { font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin: 1.75rem 0 .75rem; }

    /* Table pagination (10 rows per page) */
    .pagination-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding-top: 1rem; }
    .pagination-info { font-size: .8rem; color: var(--muted); }
    .pagination-links { display: flex; gap: .35rem; align-items: center; }
    .pagination-links button {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 .4rem; border-radius: 8px; font-size: .8rem; font-weight: 600; font-family: inherit;
        border: 1.5px solid var(--border); background: transparent; color: var(--muted); cursor: pointer;
    }
    .pagination-links button:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); background: rgba(220,38,38,0.04); }
    .pagination-links button:disabled { opacity: .4; cursor: default; }
    .pagination-links button.active-page { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pagination-links span { color: var(--muted); font-size: .8rem; padding: 0 .15rem; }

    @media (max-width: 900px) {
        .summary-grid.three-col { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<form id="reportFilterForm" method="GET" action="{{ route('reports.index') }}">
<input type="hidden" name="type" value="{{ $type }}">

{{-- ── REQ056: Report Type Selector ─────────────────────────────── --}}
<nav class="report-tabs" aria-label="Report type">
    <a href="{{ route('reports.index', ['type' => 'sales']) }}" class="report-tab {{ $type === 'sales' ? 'active' : '' }}"
       @if($type === 'sales') aria-current="page" @endif title="Revenue and sales performance from completed transactions">
        <i class="fas fa-sack-dollar"></i> Sales Report
    </a>
    <a href="{{ route('reports.index', ['type' => 'inventory']) }}" class="report-tab {{ $type === 'inventory' ? 'active' : '' }}"
       @if($type === 'inventory') aria-current="page" @endif title="Current RTC raw meat and beverage stock information">
        <i class="fas fa-boxes-stacked"></i> Inventory Report
    </a>
    <a href="{{ route('reports.index', ['type' => 'orders']) }}" class="report-tab {{ $type === 'orders' ? 'active' : '' }}"
       @if($type === 'orders') aria-current="page" @endif title="Order volume, statuses, and fulfillment performance">
        <i class="fas fa-clipboard-list"></i> Order Performance
    </a>
</nav>

{{-- ── REQ057: Filters ──────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter" style="color:var(--primary);margin-right:.4rem"></i>Report Generation Filters</h3>
        <a href="{{ route('reports.index', ['type' => $type]) }}" class="btn btn-outline btn-sm"><i class="fas fa-rotate-left"></i> Reset Filters</a>
    </div>
    <div class="card-body">
        <div class="filter-grid">
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Date Range</label>
                <select name="date_range" id="dateRangeSelect" class="form-select" onchange="handleDateRangeChange()">
                    @foreach($dateRangeLabels as $key => $label)
                        <option value="{{ $key }}" @selected($filters['date_range'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="customStartWrap" style="margin-bottom:0;display:{{ $filters['date_range'] === 'custom' ? 'block' : 'none' }}">
                <label class="form-label">Start Date</label>
                {{-- Calendar pop-up (partials/date-picker), same as the Downtime form --}}
                <input type="hidden" name="start_date" id="startDateInput" value="{{ $filters['start_date'] }}">
                <button type="button" class="cd-field report-date-btn" data-picker="date" data-for="startDateInput" data-placeholder="Start date"
                        aria-haspopup="dialog" aria-label="Start date" onclick="DatePick.open(this)">
                    <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
                </button>
            </div>
            <div class="form-group" id="customEndWrap" style="margin-bottom:0;display:{{ $filters['date_range'] === 'custom' ? 'block' : 'none' }}">
                <label class="form-label">End Date</label>
                <input type="hidden" name="end_date" id="endDateInput" value="{{ $filters['end_date'] }}" data-min="{{ $filters['start_date'] }}">
                <button type="button" class="cd-field report-date-btn" data-picker="date" data-for="endDateInput" data-placeholder="End date"
                        aria-haspopup="dialog" aria-label="End date" onclick="DatePick.open(this)">
                    <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
                </button>
            </div>

            @if($type === 'sales')
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" onchange="autoSubmit()">
                        <option value="all" @selected($filters['category'] === 'all')>All Categories</option>
                        <option value="food" @selected($filters['category'] === 'food')>Food Category</option>
                        <option value="beverage" @selected($filters['category'] === 'beverage')>Beverage Category</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Chart Interval</label>
                    <select name="chart_interval" class="form-select" onchange="autoSubmit()">
                        <option value="daily" @selected($filters['chart_interval'] === 'daily')>Daily</option>
                        <option value="weekly" @selected($filters['chart_interval'] === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($filters['chart_interval'] === 'monthly')>Monthly</option>
                    </select>
                </div>
            @elseif($type === 'inventory')
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" onchange="autoSubmit()">
                        <option value="all" @selected($filters['category'] === 'all')>All Categories</option>
                        <option value="rtc" @selected($filters['category'] === 'rtc')>RTC Raw Meat</option>
                        <option value="beverage" @selected($filters['category'] === 'beverage')>Beverage</option>
                    </select>
                </div>
            @else
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Order Type</label>
                    <select name="order_type" class="form-select" onchange="autoSubmit()">
                        <option value="all" @selected($filters['order_type'] === 'all')>All Order Types</option>
                        <option value="dine_in" @selected($filters['order_type'] === 'dine_in')>Dine-In</option>
                        <option value="takeout" @selected($filters['order_type'] === 'takeout')>Take-Out</option>
                        <option value="online" @selected($filters['order_type'] === 'online')>Online</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Menu Category</label>
                    <select name="menu_category_id" class="form-select" onchange="autoSubmit()">
                        <option value="all" @selected($filters['menu_category_id'] === 'all')>All Menu Categories</option>
                        @foreach($menuCategories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) $filters['menu_category_id'] === (string) $cat->id)>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Report Summary ───────────────────────────────────────────── --}}
<div class="card-title" style="margin-bottom:.25rem">
    <i class="fas fa-file-lines" style="color:var(--primary)"></i>
    {{ $type === 'sales' ? 'Sales Report' : ($type === 'inventory' ? 'Inventory Report' : 'Order Performance Report') }}
    <span style="font-weight:500;color:var(--muted);font-size:.82rem;margin-left:.5rem">{{ $rangeLabel }} &middot; {{ $categoryLabels[$filters['category']] ?? 'All Categories' }}</span>
</div>

@if($type === 'sales')
    <div class="summary-grid">
        <div class="stat-card accent-primary"><div class="stat-label">Total Sales</div><div class="stat-value">₱{{ number_format($data['summary']['total_sales'], 2) }}</div></div>
        <div class="stat-card"><div class="stat-label">Total Purchases</div><div class="stat-value">₱{{ number_format($data['summary']['total_purchases'], 2) }}</div></div>
        <div class="stat-card accent-amber"><div class="stat-label">Total Discounts</div><div class="stat-value">₱{{ number_format($data['summary']['total_discounts'], 2) }}</div></div>
        <div class="stat-card accent-green"><div class="stat-label">Net Sales</div><div class="stat-value">₱{{ number_format($data['summary']['net_sales'], 2) }}</div></div>
        @php $grossProfit = $data['summary']['gross_profit']; @endphp
        <div class="stat-card {{ $grossProfit < 0 ? 'accent-primary' : 'accent-green' }}" title="Net Sales minus Total Purchases"><div class="stat-label">Gross Profit</div><div class="stat-value">{{ $grossProfit < 0 ? '−' : '' }}₱{{ number_format(abs($grossProfit), 2) }}</div></div>
    </div>

    <div class="card" style="margin-bottom:1.5rem"><div class="card-header"><h3 class="card-title">Sales Performance</h3></div>
        <div class="card-body"><div class="chart-box"><canvas id="salesChart"></canvas></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-toolbar">
                <div class="input-wrap table-search">
                    <span class="input-icon"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="searchInput" class="form-input" placeholder="Search transaction #, order #..." value="{{ $filters['search'] }}">
                </div>
                <div style="display:flex;gap:.6rem">
                    <button type="submit" formaction="{{ route('reports.print') }}" formtarget="_blank" class="btn btn-outline"><i class="fas fa-print"></i> Print Report</button>
                    <button type="submit" formaction="{{ route('reports.export') }}" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
                </div>
            </div>
            <div class="report-table-wrap">
                <table class="report-table" id="reportTable">
                    <thead><tr>
                        <th>Transaction #</th><th>Order #</th><th>Date</th><th>Time</th><th>Order Type</th>
                        <th>Cashier</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Payment Method</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse($data['rows'] as $row)
                            <tr class="{{ $row['excluded'] ? 'row-excluded' : '' }}">
                                <td>{{ $row['transaction_number'] ?? '—' }}</td>
                                <td>{{ $row['order_number'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['time'] }}</td>
                                <td>{{ $row['order_type'] }}</td>
                                <td>{{ $row['cashier'] }}</td>
                                <td>₱{{ number_format($row['subtotal'], 2) }}</td>
                                <td>₱{{ number_format($row['discount'], 2) }}</td>
                                <td><strong>₱{{ number_format($row['total'], 2) }}</strong></td>
                                <td>{{ $row['payment_method'] }}</td>
                                <td><span class="badge badge-{{ Str::slug($row['payment_status'], '') }}">{{ $row['payment_status'] }}</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="11">No sales records found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($type === 'inventory')
    <div class="summary-grid three-col">
        <div class="stat-card"><div class="stat-label">Total Inventory Items</div><div class="stat-value">{{ $data['summary']['total_items'] }}</div></div>
        <div class="stat-card accent-amber"><div class="stat-label">Low Stock Items</div><div class="stat-value">{{ $data['summary']['low_stock_items'] }}</div></div>
        <div class="stat-card accent-primary"><div class="stat-label">Out of Stock Items</div><div class="stat-value">{{ $data['summary']['out_of_stock_items'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Raw Meat Items</div><div class="stat-value">{{ $data['summary']['raw_meat_items'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Beverage Items</div><div class="stat-value">{{ $data['summary']['beverage_items'] }}</div></div>
        <div class="stat-card"><div class="stat-label">RTC Items</div><div class="stat-value">{{ $data['summary']['rtc_items'] }}</div></div>
    </div>

    <div class="card" style="margin-bottom:1.5rem"><div class="card-header"><h3 class="card-title">Stock Status Distribution</h3></div>
        <div class="card-body"><div class="chart-box"><canvas id="inventoryChart"></canvas></div></div>
    </div>

    <div class="table-toolbar">
        <div class="input-wrap table-search">
            <span class="input-icon"><i class="fas fa-magnifying-glass"></i></span>
            <input type="text" name="search" id="searchInput" class="form-input" placeholder="Search item name..." value="{{ $filters['search'] }}">
        </div>
        <div style="display:flex;gap:.6rem">
            <button type="button" onclick="openPrintSections()" class="btn btn-outline"><i class="fas fa-print"></i> Print Report</button>
            <button type="submit" formaction="{{ route('reports.export') }}" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
        </div>
    </div>

    @if($filters['category'] !== 'beverage')
        <div class="card" style="margin-bottom:1.5rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-drumstick-bite" style="color:var(--primary);margin-right:.4rem"></i>Raw Meat Inventory</h3></div>
            <div class="card-body">
                <div class="report-table-wrap">
                    <table class="report-table report-searchable">
                        <thead><tr>
                            <th>Item Name</th><th>Current Raw Stock</th><th>Unit</th><th>Stock Threshold</th>
                            <th>Status</th>
                        </tr></thead>
                        <tbody>
                            @forelse($data['rtc_rows'] as $row)
                                <tr>
                                    <td>{{ $row['item_name'] }} <span style="color:var(--muted);font-size:.76rem">({{ $row['category'] }})</span></td>
                                    <td>{{ number_format($row['current_stock'], 2) }}</td>
                                    <td>@unit($row['unit'])</td>
                                    <td>{{ number_format($row['threshold'], 2) }}</td>
                                    <td><span class="badge badge-{{ str_replace('_', '', $row['status_key']) }}">{{ $row['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="5">No RTC raw meat items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($filters['category'] !== 'rtc')
        <div class="card" style="margin-bottom:1.5rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-bottle-water" style="color:var(--primary);margin-right:.4rem"></i>Beverage Inventory</h3></div>
            <div class="card-body">
                <div class="report-table-wrap">
                    <table class="report-table report-searchable">
                        <thead><tr><th>Beverage Name</th><th>Category</th><th>Current Quantity</th><th>Stock Threshold</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($data['beverage_rows'] as $row)
                                <tr>
                                    <td>{{ $row['item_name'] }}</td>
                                    <td>{{ $row['category'] }}</td>
                                    <td>{{ number_format($row['quantity'], 2) }}</td>
                                    <td>{{ number_format($row['threshold'], 2) }}</td>
                                    <td><span class="badge badge-{{ str_replace('_', '', $row['status_key']) }}">{{ $row['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="5">No beverage items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($filters['category'] !== 'beverage')
        <div class="card" style="margin-bottom:1.5rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-utensils" style="color:var(--primary);margin-right:.4rem"></i>RTC Inventory</h3></div>
            <div class="card-body">
                <div class="report-table-wrap">
                    <table class="report-table report-searchable">
                        <thead><tr><th>Item Name</th><th>RTC Servings</th><th>Portion Size</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($data['rtc_item_rows'] as $row)
                                <tr>
                                    <td>{{ $row['item_name'] }}</td>
                                    <td>{{ number_format($row['servings'], 0) }}</td>
                                    <td>@if($row['portion_size'] !== null){{ number_format($row['portion_size'], 2) }} @unit($row['portion_unit'])/serving @else — @endif</td>
                                    <td><span class="badge badge-{{ str_replace('_', '', $row['status_key']) }}">{{ $row['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="4">No RTC items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-right-left" style="color:var(--primary);margin-right:.4rem"></i>Inventory Transaction Information</h3>
            <span style="font-size:.82rem;color:var(--muted);font-weight:500">{{ $rangeLabel }}</span>
        </div>
        <div class="card-body">
            <div class="report-table-wrap">
                <table class="report-table" id="reportTable">
                    <thead><tr><th>Date</th><th>Item</th><th>Qty Purchased</th><th>Qty Added/Adjusted</th><th>Transaction Type</th><th>Administrator</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        @forelse($data['transactions'] as $t)
                            <tr>
                                <td>{{ $t['date'] }}</td>
                                <td>{{ $t['item'] }}</td>
                                <td>{{ $t['qty_purchased'] !== null ? number_format($t['qty_purchased'], 2) : '—' }}</td>
                                <td>{{ number_format($t['qty_added'], 2) }}</td>
                                <td>{{ $t['type'] }}</td>
                                <td>{{ $t['admin'] }}</td>
                                <td>{{ $t['timestamp']?->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="7">No stock-in, adjustment, or conversion records found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else
    <div class="summary-grid">
        <div class="stat-card"><div class="stat-label">Total Orders</div><div class="stat-value">{{ $data['summary']['total_orders'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Dine-In Orders</div><div class="stat-value">{{ $data['summary']['dine_in_orders'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Take-Out Orders</div><div class="stat-value">{{ $data['summary']['takeout_orders'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Advance Orders</div><div class="stat-value">{{ $data['summary']['advance_orders'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Pick-up Orders</div><div class="stat-value">{{ $data['summary']['pickup_orders'] }}</div></div>
    </div>

    <div class="card" style="margin-bottom:1.5rem"><div class="card-header"><h3 class="card-title">Order Type Distribution</h3></div>
        <div class="card-body"><div class="chart-box"><canvas id="orderTypeChart"></canvas></div></div>
    </div>

    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-ranking-star" style="color:var(--primary);margin-right:.4rem"></i>Top Selling Items</h3></div>
        <div class="card-body">
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse($data['top_items'] as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item['item_name'] }}</td>
                                <td>{{ ucfirst($item['category']) }}</td>
                                <td>{{ number_format($item['quantity_sold']) }}</td>
                                <td>₱{{ number_format($item['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="5">No items sold for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-toolbar">
                <div class="input-wrap table-search">
                    <span class="input-icon"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="searchInput" class="form-input" placeholder="Search order #, customer..." value="{{ $filters['search'] }}">
                </div>
                <div style="display:flex;gap:.6rem">
                    <button type="submit" formaction="{{ route('reports.print') }}" formtarget="_blank" class="btn btn-outline"><i class="fas fa-print"></i> Print Report</button>
                    <button type="submit" formaction="{{ route('reports.export') }}" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
                </div>
            </div>
            <div class="report-table-wrap">
                <table class="report-table" id="reportTable">
                    <thead><tr>
                        <th>Order #</th><th>Date</th><th>Order Type</th><th>Customer</th><th>Table #</th>
                        <th>Order Status</th><th>Payment Status</th><th>Total</th><th>Created</th><th>Completion</th>
                    </tr></thead>
                    <tbody>
                        @forelse($data['rows'] as $row)
                            <tr>
                                <td>{{ $row['order_number'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['order_type'] }}</td>
                                <td>{{ $row['customer'] }}</td>
                                <td>{{ $row['table_number'] }}</td>
                                <td><span class="status-dot-badge" style="background:{{ $row['status_color'] }}22;color:{{ $row['status_color'] }}"><span class="badge-dot" style="background:{{ $row['status_color'] }}"></span>{{ $row['order_status'] }}</span></td>
                                <td><span class="badge badge-{{ Str::slug($row['payment_status'], '') }}">{{ $row['payment_status'] }}</span></td>
                                <td><strong>₱{{ number_format($row['total_amount'], 2) }}</strong></td>
                                <td>{{ $row['created_time'] }}</td>
                                <td>{{ $row['completion_time'] }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="10">No orders found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

</form>

@if($type === 'inventory')
    {{-- Inventory "Print Report": choose which sections go on the printout --}}
    <div class="modal-overlay" id="printSectionsModal" role="dialog" aria-modal="true" aria-labelledby="printSectionsTitle">
        <div class="modal-box">
            <div class="modal-icon warn"><i class="fas fa-print"></i></div>
            <h3 class="modal-title" id="printSectionsTitle">Print Inventory Report</h3>
            <p class="modal-desc" style="margin-bottom:1rem">Choose what to include in the printout.</p>
            <div class="print-section-options">
                <label><input type="checkbox" class="print-section" value="raw" @checked($filters['category'] !== 'beverage')> <i class="fas fa-drumstick-bite"></i> Raw Meat</label>
                <label><input type="checkbox" class="print-section" value="beverage" @checked($filters['category'] !== 'rtc')> <i class="fas fa-bottle-water"></i> Beverages</label>
                <label><input type="checkbox" class="print-section" value="rtc" @checked($filters['category'] !== 'beverage')> <i class="fas fa-utensils"></i> RTC Items</label>
            </div>
            <div class="print-section-error" id="printSectionsError" style="display:none">Choose at least one section to print.</div>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closePrintSections()">Cancel</button>
                <button type="button" class="btn-modal-confirm" onclick="printInventorySections()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
@include('partials.chart-theme')
@include('partials.clock-dial')
@include('partials.date-picker')
<script>
// Every filter field auto-applies on change — no "Apply Filters" button needed.
function autoSubmit() {
    document.getElementById('reportFilterForm').submit();
}

// Inventory "Print Report": pick the sections, then open the printout in a new tab
function openPrintSections() {
    document.getElementById('printSectionsError').style.display = 'none';
    document.getElementById('printSectionsModal').classList.add('open');
}
function closePrintSections() {
    document.getElementById('printSectionsModal').classList.remove('open');
}
function printInventorySections() {
    const chosen = [...document.querySelectorAll('.print-section:checked')].map(c => c.value);
    if (chosen.length === 0) {
        document.getElementById('printSectionsError').style.display = 'block';
        return;
    }

    // Send the current filters plus the chosen sections, without changing the page's own form
    const form = document.getElementById('reportFilterForm');
    const added = chosen.map(value => {
        const input = Object.assign(document.createElement('input'), { type: 'hidden', name: 'sections[]', value });
        form.appendChild(input);
        return input;
    });
    const [action, target] = [form.action, form.target];
    form.action = @json(route('reports.print'));
    form.target = '_blank';
    form.submit();
    form.action = action;
    form.target = target;
    added.forEach(input => input.remove());
    closePrintSections();
}
document.getElementById('printSectionsModal')?.addEventListener('click', e => {
    if (e.target.id === 'printSectionsModal') closePrintSections();
});

function handleDateRangeChange() {
    var isCustom = document.getElementById('dateRangeSelect').value === 'custom';
    document.getElementById('customStartWrap').style.display = isCustom ? 'block' : 'none';
    document.getElementById('customEndWrap').style.display = isCustom ? 'block' : 'none';

    // Every other range (Today, This Week, ...) is a complete selection by
    // itself, so it can submit immediately. Custom Range needs both dates
    // picked first — wait for submitIfCustomRangeComplete() instead.
    if (!isCustom) {
        autoSubmit();
    }
}

function submitIfCustomRangeComplete() {
    var start = document.getElementById('startDateInput').value;
    var end = document.getElementById('endDateInput').value;
    if (start && end) {
        autoSubmit();
    }
}

// Picking a day in the calendar fires "change" on the hidden input
document.getElementById('startDateInput').addEventListener('change', function () {
    // Keep End Date on or after Start Date
    var end = document.getElementById('endDateInput');
    end.dataset.min = this.value;
    if (this.value && end.value && end.value < this.value) DatePick.set('endDateInput', '');
    submitIfCustomRangeComplete();
});
document.getElementById('endDateInput').addEventListener('change', submitIfCustomRangeComplete);

// ── REQ057/live search + pagination: every report table shows 10 rows per page;
//    the 300ms-debounced search narrows the rows first, then they are paged. ──
(function () {
    var PER_PAGE = 10;
    var input = document.getElementById('searchInput');
    var pagers = [];

    document.querySelectorAll('table.report-table').forEach(function (table) {
        var rows = Array.prototype.filter.call(table.querySelectorAll('tbody tr'), function (row) {
            return !row.classList.contains('empty-row');
        });
        if (!rows.length) return;

        var bar = document.createElement('div');
        bar.className = 'pagination-bar';
        bar.innerHTML = '<div class="pagination-info"></div><div class="pagination-links"></div>';
        (table.closest('.report-table-wrap') || table).insertAdjacentElement('afterend', bar);

        var page = 1;

        function pageButton(label, target, opts) {
            var btn = document.createElement('button');
            btn.type = 'button';   // the page sits inside the filter form — never submit it
            btn.innerHTML = label;
            if (opts && opts.active) btn.className = 'active-page';
            if (opts && opts.disabled) btn.disabled = true;
            btn.addEventListener('click', function () { page = target; render(); });
            return btn;
        }

        function render() {
            var term = input ? input.value.trim().toLowerCase() : '';
            var matched = rows.filter(function (row) {
                return term === '' || row.textContent.toLowerCase().indexOf(term) !== -1;
            });
            var pages = Math.max(1, Math.ceil(matched.length / PER_PAGE));
            page = Math.min(Math.max(1, page), pages);
            var start = (page - 1) * PER_PAGE;

            rows.forEach(function (row) { row.style.display = 'none'; });
            matched.slice(start, start + PER_PAGE).forEach(function (row) { row.style.display = ''; });

            bar.querySelector('.pagination-info').textContent = matched.length
                ? 'Showing ' + (start + 1) + '–' + Math.min(start + PER_PAGE, matched.length) + ' of ' + matched.length
                : 'No matching rows';

            var links = bar.querySelector('.pagination-links');
            links.innerHTML = '';
            if (pages > 1) {
                links.appendChild(pageButton('<i class="fas fa-chevron-left"></i>', page - 1, { disabled: page === 1 }));
                for (var p = 1; p <= pages; p++) {
                    if (p === 1 || p === pages || Math.abs(p - page) <= 1) {
                        links.appendChild(pageButton(p, p, { active: p === page }));
                    } else if (p === page - 2 || p === page + 2) {
                        var gap = document.createElement('span');
                        gap.textContent = '…';
                        links.appendChild(gap);
                    }
                }
                links.appendChild(pageButton('<i class="fas fa-chevron-right"></i>', page + 1, { disabled: page === pages }));
            }
        }

        pagers.push(function () { page = 1; render(); });
        render();
    });

    if (!input) return;
    var timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { pagers.forEach(function (reset) { reset(); }); }, 300);
    });
})();

@if($type === 'sales')
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: @json($data['chart']['labels']),
        datasets: [{
            label: 'Net Sales (₱)',
            data: @json($data['chart']['data']),
            borderColor: '#DC2626',
            backgroundColor: 'rgba(220,38,38,0.08)',
            fill: true, tension: 0.35, pointBackgroundColor: '#DC2626', pointRadius: 3,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
@elseif($type === 'inventory')
new Chart(document.getElementById('inventoryChart'), {
    type: 'doughnut',
    data: {
        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
            data: [
                {{ max($data['summary']['raw_meat_items'] + $data['summary']['beverage_items'] - $data['summary']['low_stock_items'] - $data['summary']['out_of_stock_items'], 0) }},
                {{ $data['summary']['low_stock_items'] }},
                {{ $data['summary']['out_of_stock_items'] }}
            ],
            backgroundColor: ['#16A34A', '#F59E0B', '#DC2626'],
            borderWidth: 0,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
@else
new Chart(document.getElementById('orderTypeChart'), {
    type: 'doughnut',
    data: {
        labels: @json($data['order_type_chart']['labels']),
        datasets: [{ data: @json($data['order_type_chart']['data']), backgroundColor: ['#DC2626', '#F59E0B', '#3B82F6', '#16A34A'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
@endif
</script>
@endsection
