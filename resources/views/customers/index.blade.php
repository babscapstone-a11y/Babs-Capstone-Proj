@extends('layouts.admin')

@section('title', 'Customer Accounts')
@section('page-title', 'Customer Accounts')

@section('breadcrumb')
    <span>All Customers</span>
@endsection

@section('styles')
<style>
    /* ── Stats row ──────────────────────────────────────────── */
    .cust-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .9rem;
        margin-bottom: 1.5rem;
    }
    .cust-stat {
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        display: flex; align-items: center; gap: .85rem;
        box-shadow: 0 2px 8px rgba(var(--ink-rgb),0.04);
        position: relative; overflow: hidden;
    }
    .cust-stat::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: 14px 14px 0 0;
        background: var(--stat-bar, linear-gradient(90deg, var(--primary), #F97316));
    }
    .cust-stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem; flex-shrink: 0;
    }
    .cust-stat-val { font-size: 1.65rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .cust-stat-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin-top: .25rem; }

    /* ── Filter bar ─────────────────────────────────────────── */
    .filter-bar {
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: .9rem 1.1rem;
        margin-bottom: 1.25rem;
        display: flex; align-items: center; gap: .65rem; flex-wrap: wrap;
    }
    .filter-bar input, .filter-bar select {
        height: 38px; padding: 0 .75rem;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: .83rem; font-family: inherit; color: var(--dark);
        background: var(--bg); outline: none; transition: border-color .18s;
    }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--primary); }
    .filter-bar input[type=text] { flex: 1; min-width: 180px; }

    /* ── Live search ── */
    .search-wrap { position:relative; flex:1; min-width:220px; }
    .search-input { width:100%; height:38px; padding:0 2.3rem 0 .85rem; border:1.5px solid var(--border); border-radius:9px; font-size:.83rem; font-family:inherit; color:var(--dark); background:var(--bg); outline:none; transition:border-color .18s; }
    .search-input:focus { border-color:var(--primary); }
    .search-clear { position:absolute; right:.6rem; top:50%; transform:translateY(-50%); border:none; background:transparent; color:var(--muted); cursor:pointer; padding:.25rem; display:none; }
    .search-wrap.has-value .search-clear { display:block; }
    .search-wrap.has-value .search-clear:hover { color:var(--primary); }
    .results-count { font-size:.8rem; color:var(--muted); padding:.85rem 1.25rem 0; }
    #results.is-loading { opacity:.5; transition:opacity .15s; }

    .btn-reset {
        height: 38px; padding: 0 .85rem;
        background: transparent; color: var(--muted);
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: .83rem; font-family: inherit; cursor: pointer;
        display: flex; align-items: center; gap: .4rem;
    }
    .btn-reset:hover { border-color: var(--primary); color: var(--primary); }

    /* ── Table ──────────────────────────────────────────────── */
    .table-card {
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(var(--ink-rgb),0.05);
    }
    .table-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    }
    .table-header h2 { font-size: .95rem; font-weight: 700; color: var(--dark); margin: 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    thead th {
        background: var(--bg); padding: .65rem .9rem;
        text-align: left; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: var(--muted);
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--surface-2); }
    td { padding: .7rem .9rem; color: var(--dark); vertical-align: middle; }

    /* Avatar */
    .avatar-cell { display: flex; align-items: center; gap: .65rem; }
    .customer-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .82rem; font-weight: 800; color: #fff; flex-shrink: 0;
        background: linear-gradient(135deg, #7C3AED, #2563EB);
    }
    .cust-name { font-weight: 600; color: var(--dark); font-size: .84rem; }
    .cust-email { font-size: .74rem; color: var(--muted); margin-top: .08rem; }

    /* Badges */
    .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem; border-radius: 50px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .badge-active   { background: rgba(22,163,74,0.10); color: var(--green-700); border: 1px solid rgba(22,163,74,0.2); }
    .badge-inactive { background: rgba(220,38,38,0.10); color: var(--red-700); border: 1px solid rgba(220,38,38,0.2); }

    /* Sort arrows */
    .sort-link { color: inherit; text-decoration: none; display: flex; align-items: center; gap: .3rem; }
    .sort-link:hover { color: var(--primary); }

    /* Action buttons */
    .action-group { display: flex; align-items: center; gap: .35rem; justify-content: flex-end; }
    .btn-action {
        display: inline-flex; align-items: center; gap: .28rem;
        padding: .3rem .65rem; border-radius: 8px; font-size: .74rem; font-weight: 600;
        border: 1.5px solid; cursor: pointer; font-family: inherit;
        text-decoration: none; white-space: nowrap; transition: all .18s;
    }
    .btn-view     { color: var(--blue-600); border-color: rgba(37,99,235,0.3); background: rgba(37,99,235,0.06); }
    .btn-view:hover { background: rgba(37,99,235,0.12); }
    .btn-deact    { color: var(--red-700); border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .btn-deact:hover { background: rgba(220,38,38,0.12); }
    .btn-activ    { color: var(--green-700); border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
    .btn-activ:hover { background: rgba(22,163,74,0.12); }

    /* Empty state */
    .empty-state { text-align: center; padding: 3.5rem 2rem; color: var(--muted); }
    .empty-state i { font-size: 2.5rem; margin-bottom: .85rem; display: block; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0 0 .35rem; }

    /* Registration info banner */
    .reg-notice {
        background: rgba(37,99,235,0.05);
        border: 1.5px solid rgba(37,99,235,0.15);
        border-radius: 12px; padding: .7rem 1rem;
        display: flex; align-items: flex-start; gap: .55rem;
        font-size: .8rem; color: var(--blue-700); margin-bottom: 1.25rem;
    }
    .reg-notice i { margin-top: .1rem; flex-shrink: 0; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    .anim-1 { animation: fadeUp .45s ease both; }
    .anim-2 { animation: fadeUp .45s .07s ease both; }
    .anim-3 { animation: fadeUp .45s .14s ease both; }

    @media (max-width: 700px) {
        .cust-stats { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

{{-- Stats --}}
<div class="cust-stats anim-1">
    <div class="cust-stat" style="--stat-bar: linear-gradient(90deg,#7C3AED,#2563EB)">
        <div class="cust-stat-icon" style="background:rgba(124,58,237,0.10);color:var(--violet-600)">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="cust-stat-val">{{ $totalCustomers }}</div>
            <div class="cust-stat-lbl">Total Customers</div>
        </div>
    </div>
    <div class="cust-stat" style="--stat-bar: linear-gradient(90deg,#16A34A,#059669)">
        <div class="cust-stat-icon" style="background:rgba(22,163,74,0.10);color:var(--green-600)">
            <i class="fas fa-circle-check"></i>
        </div>
        <div>
            <div class="cust-stat-val" style="color:var(--green-600)">{{ $activeCustomers }}</div>
            <div class="cust-stat-lbl">Active</div>
        </div>
    </div>
    <div class="cust-stat" style="--stat-bar: linear-gradient(90deg,#DC2626,#F97316)">
        <div class="cust-stat-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)">
            <i class="fas fa-circle-xmark"></i>
        </div>
        <div>
            <div class="cust-stat-val" style="color:var(--primary)">{{ $inactiveCustomers }}</div>
            <div class="cust-stat-lbl">Inactive</div>
        </div>
    </div>
</div>

{{-- Registration notice --}}
<div class="reg-notice anim-2">
    <i class="fas fa-circle-info"></i>
    <div>
        <strong>Self-Registration Module</strong> — Customer accounts are created when visitors register through the public
        <a href="{{ route('register') }}" target="_blank" style="font-weight:700;color:var(--blue-700)">Registration Page</a>.
        Every new registration is automatically assigned the Customer role and appears in this list.
        Administrators cannot create customer accounts directly.
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('customers.index') }}" class="filter-bar anim-2" id="liveFilterForm">
    <div class="search-wrap">
        <input type="text" id="search" name="search" class="search-input"
               placeholder="Search by name, email, ID, or contact number…"
               value="{{ request('search') }}" autocomplete="off">
        <button type="button" class="search-clear" aria-label="Clear search">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <select name="status">
        <option value="">All Statuses</option>
        <option value="active"   @selected(request('status') === 'active')>Active</option>
        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
    </select>

    <select name="sort">
        <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Sort: Registration Date</option>
        <option value="full_name"  @selected(request('sort') === 'full_name')>Sort: Name</option>
    </select>

    <select name="dir">
        <option value="desc" @selected(request('dir', 'desc') === 'desc')>Newest First</option>
        <option value="asc"  @selected(request('dir') === 'asc')>Oldest First</option>
    </select>

    <a href="{{ route('customers.index') }}" class="btn-reset"><i class="fas fa-rotate-left"></i> Reset</a>
</form>

{{-- Table --}}
<div class="table-card anim-3" id="results">
    @include('customers._results', ['customers' => $customers])
</div>

@include('customers.partials.toggle-modal')

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('customers.index') }}',
        searchFieldName: 'search',
        debounceMs: 300,
    });
});
</script>
@endsection
