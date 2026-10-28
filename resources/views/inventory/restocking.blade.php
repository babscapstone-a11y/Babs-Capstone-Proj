@extends('layouts.admin')
@section('title', $statusFilter === 'out_of_stock' ? 'Out of Stock Items' : ($statusFilter === 'low_stock' ? 'Low Stock Items' : 'Restocking / Repurchase List'))

@section('styles')
<style>
@media print {
    .no-print{display:none!important}
    body,.inv-page{background:var(--surface)!important}
    .card{box-shadow:none!important;border:1px solid var(--line-2)!important}
    .page-header{margin-bottom:1rem!important}
    .print-header{display:block!important}
}
.inv-page{padding:2rem;max-width:1400px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.print-header{display:none;text-align:center;margin-bottom:1.5rem}
.print-header h2{font-size:1.2rem;font-weight:800;margin:0}
.print-header p{font-size:.8rem;color:var(--muted);margin:.25rem 0 0}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:var(--surface);border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-low{background:var(--amber-100);color:var(--amber-700)}.badge-out{background:var(--red-100);color:var(--red-700)}
.badge-rtc{background:var(--blue-50);color:var(--blue-700)}.badge-bev{background:var(--violet-50);color:var(--violet-700)}
.section-hd{font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--dark);padding:.7rem 1.25rem;background:var(--surface-2);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.5rem}
.card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1.5rem}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.6rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:var(--surface-2);border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid var(--line);color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr.out td{background:var(--red-25)}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
/* Cards, filter bar and table match the Beverages page */
.summary-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:1.1rem;margin-bottom:2rem}
.stat-card{background:var(--surface);border-radius:14px;padding:1.1rem 1.25rem;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07)}
.stat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.75rem}
.stat-value{font-size:1.6rem;font-weight:800;color:var(--dark);line-height:1}
.stat-label{font-size:.75rem;font-weight:600;color:var(--muted);margin-top:.25rem;text-transform:uppercase;letter-spacing:.04em}
.stat-icon.amber{background:var(--amber-50);color:var(--amber-600)}.stat-icon.red{background:var(--red-50);color:var(--red-600)}.stat-icon.blue{background:var(--blue-50);color:var(--blue-600)}.stat-icon.violet{background:var(--violet-50);color:var(--violet-700)}
.reorder-qty{font-weight:700;color:var(--primary)}
.filter-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem}
.search-wrap{position:relative;flex:1;min-width:220px}
.search-input{width:100%;padding:.55rem 2.3rem .55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.search-input:focus{border-color:var(--primary)}
.search-clear{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--muted);cursor:pointer;padding:.25rem;display:none}
.search-wrap.has-value .search-clear{display:block}
.search-wrap.has-value .search-clear:hover{color:var(--primary)}
.filter-select{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface);cursor:pointer}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.results-count{font-size:.8rem;color:var(--muted);padding:.85rem 1.2rem 0}
#results.is-loading{opacity:.5;transition:opacity .15s}
.inv-table th{padding:.65rem 1rem}
.inv-table tr:hover td{background:var(--surface-2)}
.badge{white-space:nowrap}
.progress-bar{height:6px;border-radius:3px;background:var(--surface-3);overflow:hidden;width:80px;margin-top:.3rem}
.progress-fill{height:100%;border-radius:3px}
.progress-amber{background:#F59E0B}.progress-red{background:#DC2626}
</style>
@endsection

@section('content')
<div class="inv-page">

    <div class="page-header no-print">
        <div>
            <div class="page-title">
                <i class="fas fa-cart-shopping"></i>
                @if($statusFilter === 'out_of_stock')
                    Out of Stock Items
                @elseif($statusFilter === 'low_stock')
                    Low Stock Items
                @else
                    Restocking / Repurchase List
                @endif
            </div>
            <div class="page-sub">
                @if($statusFilter === 'out_of_stock')
                    Items with zero quantity remaining
                @elseif($statusFilter === 'low_stock')
                    Items below the reorder threshold, but not yet out of stock
                @else
                    Items below the reorder threshold requiring repurchase
                @endif
            </div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            @if($statusFilter)
            <a href="{{ route('inventory.restocking') }}" class="btn btn-outline"><i class="fas fa-list"></i> View All</a>
            @endif
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @include('inventory.partials.tabs', ['active' => in_array($statusFilter, ['low_stock', 'out_of_stock'], true) ? $statusFilter : null])

    @if($statusFilter)
    {{-- ── Low Stock / Out of Stock tab: same layout as the Beverages page ── --}}
    <div class="summary-grid">
        @if($statusFilter === 'low_stock')
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $totalCount }}</div><div class="stat-label">Total Low Stock</div></div>
        @else
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $totalCount }}</div><div class="stat-label">Total Out of Stock</div></div>
        @endif
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-drumstick-bite"></i></div><div class="stat-value">{{ $rawMeatCount }}</div><div class="stat-label">Raw Meat</div></div>
        <div class="stat-card"><div class="stat-icon violet"><i class="fas fa-bottle-water"></i></div><div class="stat-value">{{ $beverageCount }}</div><div class="stat-label">Beverages</div></div>
    </div>

    <form method="GET" action="{{ route('inventory.restocking') }}" class="filter-bar" id="liveFilterForm">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
        <div class="search-wrap">
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search item name…" class="search-input" autocomplete="off">
            <button type="button" class="search-clear" aria-label="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <select name="type" class="filter-select">
            <option value="">All Types</option>
            <option value="rtc" {{ request('type') === 'rtc' ? 'selected' : '' }}>Raw Meat</option>
            <option value="beverage" {{ request('type') === 'beverage' ? 'selected' : '' }}>Beverage</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['search','type']))
        <a href="{{ route('inventory.restocking', ['status' => $statusFilter]) }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card" id="results">
        @include('inventory._restocking-results', ['items' => $items])
    </div>
    @else

    <div class="print-header">
        <h2>Babs Resto — Restocking List</h2>
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="summary-grid no-print">
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $lowStockItems->count() + $outOfStockItems->count() }}</div><div class="stat-label">Total Items Needed</div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $outOfStockItems->count() }}</div><div class="stat-label">Out of Stock</div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-battery-half"></i></div><div class="stat-value">{{ $lowStockItems->count() }}</div><div class="stat-label">Low Stock</div></div>
    </div>

    @if((!$statusFilter || $statusFilter === 'out_of_stock') && $outOfStockItems->isNotEmpty())
    <div class="card">
        <div class="section-hd" style="background:var(--red-25);border-bottom-color:var(--red-200);color:var(--red-700)"><i class="fas fa-circle-xmark"></i> Out of Stock — Urgent Repurchase Needed</div>
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Current Qty</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Suggested Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outOfStockItems as $i => $item)
                    <tr class="out">
                        <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                        <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                        <td><span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($item->item_type) }}</span></td>
                        <td><span class="badge badge-out">0 / {{ number_format($item->quantity,0) }}</span></td>
                        <td>@unit($item->unit)</td>
                        <td>{{ number_format($item->reorder_level,0) }}</td>
                        <td><span class="reorder-qty">{{ number_format($item->suggested_restock, 0) }} @unit($item->unit)</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if((!$statusFilter || $statusFilter === 'low_stock') && $lowStockItems->isNotEmpty())
    <div class="card">
        <div class="section-hd" style="background:var(--amber-50);border-bottom-color:var(--amber-200);color:var(--amber-700)"><i class="fas fa-triangle-exclamation"></i> Low Stock — Replenish Soon</div>
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Current Qty</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Suggested Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $i => $item)
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                        <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                        <td><span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($item->item_type) }}</span></td>
                        <td>
                            <span class="badge badge-low">{{ number_format($item->quantity,0) }}</span>
                        </td>
                        <td>@unit($item->unit)</td>
                        <td>{{ number_format($item->reorder_level,0) }}</td>
                        <td><span class="reorder-qty">{{ number_format($item->suggested_restock, 0) }} @unit($item->unit)</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @php
        $showEmpty = match($statusFilter) {
            'out_of_stock' => $outOfStockItems->isEmpty(),
            'low_stock'    => $lowStockItems->isEmpty(),
            default        => $outOfStockItems->isEmpty() && $lowStockItems->isEmpty(),
        };
    @endphp
    @if($showEmpty)
    <div class="card" style="padding:3rem;text-align:center">
        <i class="fas fa-circle-check" style="font-size:2.5rem;color:var(--green-600);margin-bottom:1rem;display:block"></i>
        <div style="font-size:1.1rem;font-weight:700;color:var(--dark)">
            @if($statusFilter === 'out_of_stock')
                No items are out of stock!
            @elseif($statusFilter === 'low_stock')
                No items are low on stock!
            @else
                All items are well-stocked!
            @endif
        </div>
        <div style="font-size:.85rem;color:var(--muted);margin-top:.35rem">No items are currently below the reorder threshold.</div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline" style="margin-top:1.25rem"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
    </div>
    @endif
    @endif
</div>
@endsection

@section('scripts')
@if($statusFilter)
<script>
document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('inventory.restocking') }}',
        searchFieldName: 'search',
        debounceMs: 300,
    });
});
</script>
@endif
@endsection
