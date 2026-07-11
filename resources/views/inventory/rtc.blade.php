@extends('layouts.admin')
@section('title', 'Raw Meats')

@section('styles')
<style>
.inv-page{padding:2rem;max-width:1400px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:1.1rem;margin-bottom:2rem}
.stat-card{background:#fff;border-radius:14px;padding:1.1rem 1.25rem;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07)}
.stat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.75rem}
.stat-value{font-size:1.6rem;font-weight:800;color:var(--dark);line-height:1}
.stat-label{font-size:.75rem;font-weight:600;color:var(--muted);margin-top:.25rem;text-transform:uppercase;letter-spacing:.04em}
.stat-icon.blue{background:#EFF6FF;color:#2563EB}.stat-icon.green{background:#F0FDF4;color:#16A34A}.stat-icon.amber{background:#FFFBEB;color:#D97706}.stat-icon.red{background:#FEF2F2;color:#DC2626}

.filter-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem}
.search-wrap{position:relative;flex:1;min-width:220px}
.search-input{width:100%;padding:.55rem 2.3rem .55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.search-input:focus{border-color:var(--primary)}
.search-clear{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--muted);cursor:pointer;padding:.25rem;display:none}
.search-wrap.has-value .search-clear{display:block}
.search-wrap.has-value .search-clear:hover{color:var(--primary)}
.results-count{font-size:.8rem;color:var(--muted);padding:.85rem 1.2rem 0}
#results.is-loading{opacity:.5;transition:opacity .15s}
.filter-select{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;cursor:pointer}
.filter-select:focus{border-color:var(--primary)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}

.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.badge-available{background:#DCFCE7;color:#15803D}.badge-low{background:#FEF3C7;color:#B45309}.badge-out{background:#FEE2E2;color:#B91C1C}
.progress-bar{height:6px;border-radius:3px;background:#F3F4F6;overflow:hidden;width:80px;margin-top:.3rem}
.progress-fill{height:100%;border-radius:3px;transition:width .3s}
.progress-green{background:#16A34A}.progress-amber{background:#F59E0B}.progress-red{background:#DC2626}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
</style>
@endsection

@section('content')
<div class="inv-page">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-drumstick-bite"></i> Raw Meats</div>
            <div class="page-sub">Track raw meat stock levels and thresholds</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.restocking') }}" class="btn btn-outline"><i class="fas fa-cart-shopping"></i> Repurchase List</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-list"></i></div><div class="stat-value">{{ $totalRtc }}</div><div class="stat-label">Total RTC Items</div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $lowStock }}</div><div class="stat-label">Low Stock</div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $outOfStock }}</div><div class="stat-label">Out of Stock</div></div>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('inventory.rtc') }}" class="filter-bar" id="liveFilterForm">
        <div class="search-wrap">
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search item, category…" class="search-input" autocomplete="off">
            <button type="button" class="search-clear" aria-label="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('inventory.rtc') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card" id="results">
        @include('inventory._rtc-results', ['items' => $items])
    </div>

    {{-- Links --}}
    <div style="margin-top:1rem;display:flex;gap:1rem;font-size:.82rem;">
        <a href="{{ route('inventory.stock-in.index') }}?type=rtc" style="color:var(--primary);font-weight:600"><i class="fas fa-history"></i> Stock-In History</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('inventory.rtc') }}',
        searchFieldName: 'search',
        debounceMs: 300,
    });
});
</script>
@endsection
