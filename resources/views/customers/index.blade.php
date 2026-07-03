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
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        display: flex; align-items: center; gap: .85rem;
        box-shadow: 0 2px 8px rgba(17,24,39,0.04);
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
        background: #fff;
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
    .btn-filter {
        height: 38px; padding: 0 1rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: 9px; font-size: .83rem; font-weight: 600;
        font-family: inherit; cursor: pointer;
        display: flex; align-items: center; gap: .4rem;
    }
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
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(17,24,39,0.05);
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
    tbody tr:hover { background: #FAFAFA; }
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
    .badge-active   { background: rgba(22,163,74,0.10); color: #15803D; border: 1px solid rgba(22,163,74,0.2); }
    .badge-inactive { background: rgba(220,38,38,0.10); color: #B91C1C; border: 1px solid rgba(220,38,38,0.2); }

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
    .btn-view     { color: #2563EB; border-color: rgba(37,99,235,0.3); background: rgba(37,99,235,0.06); }
    .btn-view:hover { background: rgba(37,99,235,0.12); }
    .btn-deact    { color: #B91C1C; border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .btn-deact:hover { background: rgba(220,38,38,0.12); }
    .btn-activ    { color: #15803D; border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
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
        font-size: .8rem; color: #1D4ED8; margin-bottom: 1.25rem;
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
        <div class="cust-stat-icon" style="background:rgba(124,58,237,0.10);color:#7C3AED">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="cust-stat-val">{{ $totalCustomers }}</div>
            <div class="cust-stat-lbl">Total Customers</div>
        </div>
    </div>
    <div class="cust-stat" style="--stat-bar: linear-gradient(90deg,#16A34A,#059669)">
        <div class="cust-stat-icon" style="background:rgba(22,163,74,0.10);color:#16A34A">
            <i class="fas fa-circle-check"></i>
        </div>
        <div>
            <div class="cust-stat-val" style="color:#16A34A">{{ $activeCustomers }}</div>
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
        <a href="{{ route('register') }}" target="_blank" style="font-weight:700;color:#1D4ED8">Registration Page</a>.
        Every new registration is automatically assigned the Customer role and appears in this list.
        Administrators cannot create customer accounts directly.
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('customers.index') }}" class="filter-bar anim-2">
    <input type="text" name="search" placeholder="Search by name, email, or ID…" value="{{ request('search') }}">

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

    <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
    <a href="{{ route('customers.index') }}" class="btn-reset"><i class="fas fa-rotate-left"></i> Reset</a>
</form>

{{-- Table --}}
<div class="table-card anim-3">
    <div class="table-header">
        <h2>
            <i class="fas fa-users" style="color:var(--primary);margin-right:.4rem"></i>
            Customer Accounts
            @if($customers->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $customers->total() }})</span>
            @endif
        </h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>Customer</th>
                    <th>Contact Number</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => request('dir', 'desc') === 'desc' ? 'asc' : 'desc']) }}" class="sort-link">
                            Registered
                            @if(request('sort', 'created_at') === 'created_at')
                                <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}" style="color:var(--primary)"></i>
                            @endif
                        </a>
                    </th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td style="font-size:.75rem;color:var(--muted);font-weight:600">
                        #{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div class="avatar-cell">
                            <div class="customer-avatar">{{ $customer->initials }}</div>
                            <div>
                                <div class="cust-name">{{ $customer->full_name }}</div>
                                <div class="cust-email">{{ $customer->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.82rem;color:var(--muted)">
                        {{ $customer->contact_no ?: '—' }}
                    </td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        {{ $customer->created_at->format('M d, Y') }}<br>
                        <span style="font-size:.7rem">{{ $customer->created_at->format('h:i A') }}</span>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted)">
                        <span style="font-style:italic">—</span>
                    </td>
                    <td>
                        @if($customer->status === 'active')
                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Active</span>
                        @else
                            <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.4rem"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-group">
                            @can('view', $customer)
                            <a href="{{ route('customers.show', $customer) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                            @can('toggleStatus', $customer)
                            <button type="button"
                                class="btn-action {{ $customer->status === 'active' ? 'btn-deact' : 'btn-activ' }}"
                                onclick="openToggleModal({{ $customer->id }}, '{{ addslashes($customer->full_name) }}', {{ $customer->status === 'active' ? 'true' : 'false' }})">
                                <i class="fas fa-{{ $customer->status === 'active' ? 'ban' : 'check' }}"></i>
                                {{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-users" style="color:var(--muted);opacity:.4"></i>
                            <h3>No Customers Found</h3>
                            <p style="margin:0">
                                {{ request()->hasAny(['search','status']) ? 'Try adjusting your filters.' : 'No customers have registered yet. Customers appear here automatically after they register.' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers
        </span>
        {{ $customers->links() }}
    </div>
    @endif
</div>

{{-- Toggle Status Modal --}}
<div id="toggleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:18px;padding:2rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="text-align:center;margin-bottom:1.25rem">
            <div id="toggleIcon" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto .9rem"></div>
            <h3 id="toggleTitle" style="font-size:1.05rem;font-weight:700;color:var(--dark);margin:0 0 .5rem"></h3>
            <p id="toggleBody" style="font-size:.85rem;color:var(--muted);margin:0;line-height:1.55"></p>
        </div>
        <form id="toggleForm" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;gap:.75rem;justify-content:center">
                <button type="button" onclick="closeToggleModal()" style="flex:1;padding:.65rem;border:1.5px solid var(--border);border-radius:10px;background:transparent;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="toggleSubmit" style="flex:1;padding:.65rem;border:none;border-radius:10px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer">Confirm</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openToggleModal(id, name, isActive) {
    var icon   = document.getElementById('toggleIcon');
    var title  = document.getElementById('toggleTitle');
    var body   = document.getElementById('toggleBody');
    var form   = document.getElementById('toggleForm');
    var submit = document.getElementById('toggleSubmit');
    if (isActive) {
        icon.style.background = 'rgba(220,38,38,0.10)'; icon.style.color = '#DC2626';
        icon.innerHTML = '<i class="fas fa-ban"></i>';
        title.textContent = 'Deactivate "' + name + '"?';
        body.innerHTML  = 'This customer will be <strong>logged out</strong> and unable to log in or place orders. Their account and order history remain stored in the database.';
        submit.style.background = 'linear-gradient(90deg,#DC2626,#F97316)';
        submit.textContent = 'Deactivate Account';
    } else {
        icon.style.background = 'rgba(22,163,74,0.10)'; icon.style.color = '#16A34A';
        icon.innerHTML = '<i class="fas fa-check"></i>';
        title.textContent = 'Activate "' + name + '"?';
        body.innerHTML  = 'This customer will be able to <strong>log in</strong> and place orders again.';
        submit.style.background = 'linear-gradient(90deg,#16A34A,#059669)';
        submit.textContent = 'Activate Account';
    }
    form.action = '/customers/' + id + '/toggle-status';
    document.getElementById('toggleModal').style.display = 'flex';
}
function closeToggleModal() {
    document.getElementById('toggleModal').style.display = 'none';
}
document.getElementById('toggleModal').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
