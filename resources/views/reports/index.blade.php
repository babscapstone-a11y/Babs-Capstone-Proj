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
    .type-select-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .type-card {
        display: block; background: var(--white); border: 2px solid var(--border); border-radius: 16px;
        padding: 1.4rem 1.5rem; text-decoration: none; transition: all .18s ease; cursor: pointer;
        box-shadow: 0 2px 12px rgba(17,24,39,0.05);
    }
    .type-card:hover { border-color: rgba(220,38,38,0.35); transform: translateY(-2px); }
    .type-card.active { border-color: var(--primary); background: linear-gradient(180deg, rgba(220,38,38,0.05), transparent); box-shadow: 0 8px 20px rgba(220,38,38,0.12); }
    .type-card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: .85rem; color: var(--white); }
    .type-card-title { font-size: .98rem; font-weight: 700; color: var(--dark); margin-bottom: .25rem; }
    .type-card-desc { font-size: .8rem; color: var(--muted); line-height: 1.5; }

    .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; align-items: end; }
    .filter-actions { display: flex; gap: .6rem; margin-top: 1.1rem; flex-wrap: wrap; }
    .filter-check { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: var(--dark); font-weight: 500; padding: .68rem 0; }

    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
    .stat-card { background: var(--white); border-radius: 14px; border: 1px solid var(--border); padding: 1.15rem 1.3rem; box-shadow: 0 2px 12px rgba(17,24,39,0.05); }
    .stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .4rem; }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--dark); line-height: 1.1; }
    .stat-card.accent-primary .stat-value { color: var(--primary); }
    .stat-card.accent-amber .stat-value { color: var(--accent); }
    .stat-card.accent-green .stat-value { color: #16A34A; }

    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
    .charts-grid.two-col { grid-template-columns: 1fr 1fr; }
    .chart-box { position: relative; height: 300px; }
    .timing-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }

    .report-table-wrap { overflow-x: auto; }
    table.report-table { width: 100%; border-collapse: collapse; font-size: .84rem; white-space: nowrap; }
    table.report-table thead th { text-align: left; padding: .7rem .9rem; background: #F8FAFC; color: var(--muted); font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); position: sticky; top: 0; }
    table.report-table tbody td { padding: .7rem .9rem; border-bottom: 1px solid var(--border); color: var(--dark); }
    table.report-table tbody tr:hover td { background: #FAFAFA; }
    table.report-table tbody tr.row-excluded td { color: #9CA3AF; font-style: italic; }
    .table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .table-search { max-width: 320px; flex: 1; min-width: 220px; }
    .empty-row td { text-align: center; padding: 2rem; color: var(--muted); }

    .badge-instock, .badge-available { background: rgba(22,163,74,0.1); color: #16A34A; }
    .badge-lowstock, .badge-low_stock { background: rgba(245,158,11,0.12); color: #B45309; }
    .badge-outofstock, .badge-out_of_stock { background: rgba(220,38,38,0.1); color: #DC2626; }
    .badge-paid { background: rgba(22,163,74,0.1); color: #16A34A; }
    .badge-cancelled { background: rgba(220,38,38,0.1); color: #DC2626; }
    .badge-failed, .badge-refunded { background: rgba(107,114,128,0.1); color: #6B7280; }
    .status-dot-badge { display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 600; padding: .22rem .65rem; border-radius: 50px; }

    .section-heading { font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin: 1.75rem 0 .75rem; }

    @media (max-width: 900px) {
        .type-select-grid { grid-template-columns: 1fr; }
        .charts-grid, .charts-grid.two-col { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<form id="reportFilterForm" method="GET" action="{{ route('reports.index') }}">
<input type="hidden" name="type" value="{{ $type }}">

{{-- ── REQ056: Report Type Selector ─────────────────────────────── --}}
<div class="type-select-grid">
    <a href="{{ route('reports.index', ['type' => 'sales']) }}" class="type-card {{ $type === 'sales' ? 'active' : '' }}">
        <div class="type-card-icon" style="background:linear-gradient(135deg,#DC2626,#F97316)"><i class="fas fa-sack-dollar"></i></div>
        <div class="type-card-title">Sales Report</div>
        <div class="type-card-desc">View revenue and sales performance from completed transactions.</div>
    </a>
    <a href="{{ route('reports.index', ['type' => 'inventory']) }}" class="type-card {{ $type === 'inventory' ? 'active' : '' }}">
        <div class="type-card-icon" style="background:linear-gradient(135deg,#F59E0B,#F97316)"><i class="fas fa-boxes-stacked"></i></div>
        <div class="type-card-title">Inventory Report</div>
        <div class="type-card-desc">View current RTC raw meat and beverage stock information.</div>
    </a>
    <a href="{{ route('reports.index', ['type' => 'orders']) }}" class="type-card {{ $type === 'orders' ? 'active' : '' }}">
        <div class="type-card-icon" style="background:linear-gradient(135deg,#111827,#374151)"><i class="fas fa-clipboard-list"></i></div>
        <div class="type-card-title">Order Performance</div>
        <div class="type-card-desc">View order volume, statuses, and fulfillment performance.</div>
    </a>
</div>

{{-- ── REQ057: Filters ──────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter" style="color:var(--primary);margin-right:.4rem"></i>Report Generation Filters</h3>
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
                <input type="date" name="start_date" id="startDateInput" class="form-select" value="{{ $filters['start_date'] }}" onchange="submitIfCustomRangeComplete()">
            </div>
            <div class="form-group" id="customEndWrap" style="margin-bottom:0;display:{{ $filters['date_range'] === 'custom' ? 'block' : 'none' }}">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" id="endDateInput" class="form-select" value="{{ $filters['end_date'] }}" onchange="submitIfCustomRangeComplete()">
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
                <label class="filter-check">
                    <input type="checkbox" name="include_unpaid" value="1" @checked($filters['include_unpaid']) onchange="autoSubmit()">
                    Include Cancelled / Unpaid Orders
                </label>
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

        <div class="filter-actions">
            <a href="{{ route('reports.index', ['type' => $type]) }}" class="btn btn-outline"><i class="fas fa-rotate-left"></i> Reset Filters</a>
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
        <div class="stat-card"><div class="stat-label">Transactions</div><div class="stat-value">{{ number_format($data['summary']['transaction_count']) }}</div></div>
        <div class="stat-card"><div class="stat-label">Avg. Transaction Value</div><div class="stat-value">₱{{ number_format($data['summary']['average_transaction'], 2) }}</div></div>
        <div class="stat-card accent-amber"><div class="stat-label">Total Discounts</div><div class="stat-value">₱{{ number_format($data['summary']['total_discounts'], 2) }}</div></div>
        <div class="stat-card accent-green"><div class="stat-label">Net Sales</div><div class="stat-value">₱{{ number_format($data['summary']['net_sales'], 2) }}</div></div>
    </div>

    <div class="charts-grid">
        <div class="card"><div class="card-header"><h3 class="card-title">Sales Performance</h3></div>
            <div class="card-body"><div class="chart-box"><canvas id="salesChart"></canvas></div></div>
        </div>
        <div class="card"><div class="card-header"><h3 class="card-title">At a Glance</h3></div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--muted);line-height:1.8">
                    Showing <strong>{{ $data['summary']['transaction_count'] }}</strong> completed transaction(s) for
                    <strong>{{ $rangeLabel }}</strong>.<br>
                    Net sales reflect paid transactions only, after discounts.
                    @if($filters['include_unpaid'])
                        <br><br><span style="color:#B45309"><i class="fas fa-triangle-exclamation"></i> Cancelled/unpaid orders are shown in the table below for visibility, but are excluded from all totals.</span>
                    @endif
                </p>
            </div>
        </div>
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
    <div class="summary-grid">
        <div class="stat-card"><div class="stat-label">Total Inventory Items</div><div class="stat-value">{{ $data['summary']['total_items'] }}</div></div>
        <div class="stat-card accent-amber"><div class="stat-label">Low Stock Items</div><div class="stat-value">{{ $data['summary']['low_stock_items'] }}</div></div>
        <div class="stat-card accent-primary"><div class="stat-label">Out of Stock Items</div><div class="stat-value">{{ $data['summary']['out_of_stock_items'] }}</div></div>
        <div class="stat-card"><div class="stat-label">RTC Items</div><div class="stat-value">{{ $data['summary']['rtc_items'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Beverage Items</div><div class="stat-value">{{ $data['summary']['beverage_items'] }}</div></div>
    </div>

    <div class="charts-grid two-col">
        <div class="card"><div class="card-header"><h3 class="card-title">Stock Status Distribution</h3></div>
            <div class="card-body"><div class="chart-box"><canvas id="inventoryChart"></canvas></div></div>
        </div>
        <div class="card"><div class="card-header"><h3 class="card-title">At a Glance</h3></div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--muted);line-height:1.8">
                    <strong>{{ $data['summary']['total_items'] }}</strong> tracked items —
                    <strong style="color:#B45309">{{ $data['summary']['low_stock_items'] }}</strong> running low,
                    <strong style="color:#DC2626">{{ $data['summary']['out_of_stock_items'] }}</strong> out of stock.<br><br>
                    Only Pork, Beef, Chicken, Fish (RTC) and beverages are tracked — no ingredient-level inventory.
                </p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-toolbar">
                <div class="input-wrap table-search">
                    <span class="input-icon"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="searchInput" class="form-input" placeholder="Search item name..." value="{{ $filters['search'] }}">
                </div>
                <div style="display:flex;gap:.6rem">
                    <button type="submit" formaction="{{ route('reports.print') }}" formtarget="_blank" class="btn btn-outline"><i class="fas fa-print"></i> Print Report</button>
                    <button type="submit" formaction="{{ route('reports.export') }}" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
                </div>
            </div>

            @if($filters['category'] !== 'beverage')
                <div class="section-heading">RTC Raw Meat</div>
                <div class="report-table-wrap">
                    <table class="report-table report-searchable">
                        <thead><tr>
                            <th>Item Name</th><th>Current Raw Stock</th><th>Unit</th><th>Stock Threshold</th>
                            <th>RTC Units Available</th><th>Equivalent Servings</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                            @forelse($data['rtc_rows'] as $row)
                                <tr>
                                    <td>{{ $row['item_name'] }} <span style="color:var(--muted);font-size:.76rem">({{ $row['category'] }})</span></td>
                                    <td>{{ number_format($row['current_stock'], 2) }}</td>
                                    <td>{{ $row['unit'] }}</td>
                                    <td>{{ number_format($row['threshold'], 2) }}</td>
                                    <td>{{ number_format($row['rtc_units_available'], 2) }}</td>
                                    <td>{{ $row['equivalent_servings'] !== null ? number_format($row['equivalent_servings'], 2) : '—' }}</td>
                                    <td><span class="badge badge-{{ str_replace('_', '', $row['status_key']) }}">{{ $row['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="7">No RTC raw meat items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if($filters['category'] !== 'rtc')
                <div class="section-heading">Beverage Inventory</div>
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
            @endif

            <div class="section-heading">Inventory Transaction Information ({{ $rangeLabel }})</div>
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
        <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value">{{ $data['summary']['pending_orders'] }}</div></div>
        <div class="stat-card accent-amber"><div class="stat-label">Preparing</div><div class="stat-value">{{ $data['summary']['preparing_orders'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Ready</div><div class="stat-value">{{ $data['summary']['ready_orders'] }}</div></div>
        <div class="stat-card accent-green"><div class="stat-label">Completed</div><div class="stat-value">{{ $data['summary']['completed_orders'] }}</div></div>
        <div class="stat-card accent-primary"><div class="stat-label">Cancelled</div><div class="stat-value">{{ $data['summary']['cancelled_orders'] }}</div></div>
    </div>

    <div class="charts-grid two-col">
        <div class="card"><div class="card-header"><h3 class="card-title">Order Type Distribution</h3></div>
            <div class="card-body"><div class="chart-box"><canvas id="orderTypeChart"></canvas></div></div>
        </div>
        <div class="card"><div class="card-header"><h3 class="card-title">Order Status Distribution</h3></div>
            <div class="card-body"><div class="chart-box"><canvas id="orderStatusChart"></canvas></div></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><h3 class="card-title">Fulfillment Timing</h3></div>
        <div class="card-body">
            <div class="timing-row">
                <div class="stat-card"><div class="stat-label">Avg. Order Processing Time</div><div class="stat-value" style="font-size:1.2rem">{{ $data['timing']['avg_processing_minutes'] !== null ? $data['timing']['avg_processing_minutes'].' min' : 'N/A' }}</div></div>
                <div class="stat-card"><div class="stat-label">Avg. Preparation → Handoff Time</div><div class="stat-value" style="font-size:1.2rem">{{ $data['timing']['avg_preparation_minutes'] !== null ? $data['timing']['avg_preparation_minutes'].' min' : 'N/A' }}</div></div>
                <div class="stat-card"><div class="stat-label">Avg. Completion Time</div><div class="stat-value" style="font-size:1.2rem">{{ $data['timing']['avg_completion_minutes'] !== null ? $data['timing']['avg_completion_minutes'].' min' : 'N/A' }}</div></div>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
// Every filter field auto-applies on change — no "Apply Filters" button needed.
function autoSubmit() {
    document.getElementById('reportFilterForm').submit();
}

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

// ── REQ057/live search: 300ms-debounced client-side row filter ──
(function () {
    var input = document.getElementById('searchInput');
    if (!input) return;
    var timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        var term = input.value.trim().toLowerCase();
        timer = setTimeout(function () {
            document.querySelectorAll('table.report-table').forEach(function (table) {
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    if (row.classList.contains('empty-row')) return;
                    var text = row.textContent.toLowerCase();
                    row.style.display = (term === '' || text.indexOf(term) !== -1) ? '' : 'none';
                });
            });
        }, 300);
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
                {{ max($data['summary']['total_items'] - $data['summary']['low_stock_items'] - $data['summary']['out_of_stock_items'], 0) }},
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
        datasets: [{ data: @json($data['order_type_chart']['data']), backgroundColor: ['#3B82F6', '#DC2626', '#F59E0B'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('orderStatusChart'), {
    type: 'bar',
    data: {
        labels: @json($data['order_status_chart']['labels']),
        datasets: [{ label: 'Orders', data: @json($data['order_status_chart']['data']), backgroundColor: @json($data['order_status_chart']['colors']) }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
@endif
</script>
@endsection
