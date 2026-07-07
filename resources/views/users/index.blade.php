@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <span>User Management</span>
@endsection

@section('styles')
<style>
    .stats-row  { display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
    .stat-card  { flex:1; min-width:140px; background:#fff; border-radius:14px; padding:1.1rem 1.3rem; border:1px solid var(--border); box-shadow:0 2px 10px rgba(0,0,0,0.05); }
    .stat-label { font-size:.72rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
    .stat-value { font-size:1.6rem; font-weight:800; color:var(--dark); margin-top:.15rem; line-height:1; }
    .stat-icon  { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:.6rem; font-size:.95rem; }

    .filter-row { display:flex; gap:.75rem; flex-wrap:wrap; align-items:flex-end; }
    .filter-row .filter-item { display:flex; flex-direction:column; gap:.35rem; }
    .filter-row .filter-item label { font-size:.78rem; font-weight:600; color:var(--dark); }
    .filter-row input, .filter-row select {
        border:1.5px solid rgba(17,24,39,0.1); border-radius:10px;
        padding:.52rem .85rem; font-size:.855rem; color:var(--dark);
        font-family:inherit; background:#fff; outline:none;
        transition:border-color .2s, box-shadow .2s;
    }
    .filter-row input:focus, .filter-row select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(220,38,38,0.08); }
    .filter-row .filter-select { min-width:150px; }

    /* ── Live search ── */
    .search-wrap { position:relative; min-width:240px; }
    .search-input { width:100%; padding:.55rem 2.3rem .55rem .85rem; border:1.5px solid rgba(17,24,39,0.1); border-radius:10px; font-size:.855rem; font-family:inherit; color:var(--dark); outline:none; transition:border-color .2s, box-shadow .2s; }
    .search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(220,38,38,0.08); }
    .search-clear { position:absolute; right:.6rem; top:50%; transform:translateY(-50%); border:none; background:transparent; color:var(--muted); cursor:pointer; padding:.25rem; display:none; }
    .search-wrap.has-value .search-clear { display:block; }
    .search-wrap.has-value .search-clear:hover { color:var(--primary); }
    .results-count { font-size:.8rem; color:var(--muted); padding:.85rem 1.25rem 0; }
    #results.is-loading { opacity:.5; transition:opacity .15s; }

    .data-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.855rem; }
    .data-table thead th {
        padding:.85rem 1rem; text-align:left;
        font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
        color:var(--muted); background:#F8FAFC;
        border-bottom:1px solid var(--border);
    }
    .data-table tbody tr { transition:background .15s; }
    .data-table tbody tr:hover { background:rgba(220,38,38,0.02); }
    .data-table td { padding:.9rem 1rem; border-bottom:1px solid rgba(17,24,39,0.05); vertical-align:middle; }
    .data-table tbody tr:last-child td { border-bottom:none; }

    .staff-cell { display:flex; align-items:center; gap:.65rem; }
    .staff-avatar {
        width:38px; height:38px; border-radius:10px; flex-shrink:0;
        background:linear-gradient(135deg, var(--primary), #F97316);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:700; font-size:.78rem;
    }
    .staff-name  { font-weight:600; color:var(--dark); font-size:.875rem; }
    .staff-email { font-size:.75rem; color:var(--muted); margin-top:.05rem; }

    .actions { display:flex; gap:.35rem; align-items:center; }

    .empty-state { text-align:center; padding:3.5rem 1rem; }
    .empty-state i { font-size:2.5rem; color:rgba(17,24,39,0.15); margin-bottom:.75rem; display:block; }
    .empty-state p { color:var(--muted); font-size:.88rem; margin:0; }

    .pagination-bar { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-top:1px solid var(--border); gap:1rem; flex-wrap:wrap; }
    .pagination-info { font-size:.8rem; color:var(--muted); }
    .pagination-links { display:flex; gap:.35rem; }
    .pagination-links a, .pagination-links span {
        display:inline-flex; align-items:center; justify-content:center;
        width:32px; height:32px; border-radius:8px; font-size:.8rem; font-weight:600;
        border:1.5px solid var(--border); color:var(--muted);
        transition:all .18s;
    }
    .pagination-links a:hover { border-color:var(--primary); color:var(--primary); background:rgba(220,38,38,0.04); }
    .pagination-links .active-page { background:var(--primary); color:#fff; border-color:var(--primary); }

    .alert-banner {
        display:flex; align-items:center; gap:.65rem;
        padding:.8rem 1.1rem; border-radius:12px; margin-bottom:1.25rem;
        font-size:.855rem; font-weight:500;
    }
    .alert-success { background:#ECFDF5; border:1.5px solid #86EFAC; color:#166534; }
    .alert-error   { background:#FEF2F2; border:1.5px solid #FCA5A5; color:#991B1B; }
</style>
@endsection

@section('content')

{{-- Stats row --}}
@php
    use App\Models\User;
    use App\Models\Role;
    $total    = $users->total();
    $active   = $users->getCollection()->where('status','active')->count();
    $inactive = $users->getCollection()->where('status','inactive')->count();
@endphp

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(220,38,38,0.1);color:var(--primary)">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-label">Total Staff</div>
        <div class="stat-value" id="statTotal">{{ $users->total() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(22,163,74,0.1);color:#16A34A">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="stat-label">Active</div>
        <div class="stat-value" style="color:#16A34A" id="statActive">{{ $users->getCollection()->where('status','active')->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(107,114,128,0.1);color:var(--muted)">
            <i class="fas fa-circle-xmark"></i>
        </div>
        <div class="stat-label">Inactive</div>
        <div class="stat-value" style="color:var(--muted)" id="statInactive">{{ $users->getCollection()->where('status','inactive')->count() }}</div>
    </div>
    @if($pendingResetCount > 0)
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-label">Pending Resets</div>
        <div class="stat-value" style="color:#D97706" id="statPendingResets">{{ $pendingResetCount }}</div>
    </div>
    @endif
</div>

{{-- Main card --}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-users" style="color:var(--primary);margin-right:.4rem"></i> Staff Management</h2>
        <div style="display:flex;gap:.6rem">
            @if($pendingResetCount > 0)
                <a href="{{ route('password-reset-requests.index') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-key"></i> Resets
                    <span class="badge badge-pending" style="margin-left:.2rem">{{ $pendingResetCount }}</span>
                </a>
            @endif
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Staff
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border)">
        <form method="GET" action="{{ route('users.index') }}" id="liveFilterForm">
            <div class="filter-row">
                <div class="filter-item">
                    <label for="search">Search</label>
                    <div class="search-wrap">
                        <input id="search" name="search" type="text" class="search-input"
                               placeholder="Search by name, email, username, or role…"
                               value="{{ request('search') }}" autocomplete="off">
                        <button type="button" class="search-clear" aria-label="Clear search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="filter-item">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="filter-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ request('role') == $r->id ? 'selected' : '' }}>
                                {{ $r->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                @if(request()->hasAny(['search','role','status']))
                <div class="filter-item" style="flex-direction:row;gap:.5rem;padding-bottom:.05rem">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm" style="height:36px">
                        <i class="fas fa-xmark"></i> Clear
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>

    <div id="results">
        @include('users._results', ['users' => $users])
    </div>
</div>

{{-- Reset-Password modal (POST form) --}}
<div class="modal-overlay" id="resetModal" role="dialog" aria-modal="true" aria-labelledby="resetModalTitle">
    <div class="modal-box">
        <div class="modal-icon warn"><i class="fas fa-key"></i></div>
        <h3 class="modal-title" id="resetModalTitle">Request Password Reset</h3>
        <p class="modal-desc" id="resetModalDesc">Create a reset request for <strong id="resetUserName"></strong>. You will need to approve it before the email is sent.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
            <form id="resetForm" method="POST" style="flex:1;display:flex">
                @csrf
                <button type="submit" class="btn-modal-confirm" style="flex:1">
                    <i class="fas fa-paper-plane"></i> Create Request
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Toggle status modal --}}
<div class="modal-overlay" id="toggleModal" role="dialog" aria-modal="true" aria-labelledby="toggleModalTitle">
    <div class="modal-box">
        <div class="modal-icon" id="toggleModalIcon"><i class="fas fa-ban"></i></div>
        <h3 class="modal-title" id="toggleModalTitle"></h3>
        <p class="modal-desc"  id="toggleModalDesc"></p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeToggleModal()">Cancel</button>
            <form id="toggleForm" method="POST" style="flex:1;display:flex">
                @csrf @method('PUT')
                <button type="submit" class="btn-modal-confirm" id="toggleConfirmBtn" style="flex:1">Confirm</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openResetModal(action, name) {
    document.getElementById('resetUserName').textContent = name;
    document.getElementById('resetForm').action = action;
    document.getElementById('resetModal').classList.add('open');
}
function closeResetModal() {
    document.getElementById('resetModal').classList.remove('open');
}
document.getElementById('resetModal').addEventListener('click', function(e) {
    if (e.target === this) closeResetModal();
});

function openToggleModal(action, name, currentStatus) {
    var isActive  = currentStatus === 'active';
    var icon      = document.getElementById('toggleModalIcon');
    var title     = document.getElementById('toggleModalTitle');
    var desc      = document.getElementById('toggleModalDesc');
    var btn       = document.getElementById('toggleConfirmBtn');

    icon.className         = 'modal-icon ' + (isActive ? 'danger' : 'warn');
    icon.innerHTML         = '<i class="fas fa-' + (isActive ? 'ban' : 'circle-check') + '"></i>';
    title.textContent      = isActive ? 'Deactivate Account?' : 'Activate Account?';
    desc.innerHTML         = isActive
        ? '<strong>' + name + '</strong> will no longer be able to log in or perform any operations.'
        : '<strong>' + name + '</strong> will regain access to the system.';
    btn.textContent        = isActive ? 'Deactivate' : 'Activate';
    btn.style.background   = isActive ? '#DC2626' : '#16A34A';
    document.getElementById('toggleForm').action = action;
    document.getElementById('toggleModal').classList.add('open');
}
function closeToggleModal() {
    document.getElementById('toggleModal').classList.remove('open');
}
document.getElementById('toggleModal').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});

document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('users.index') }}',
        searchFieldName: 'search',
        debounceMs: 300,
        statsSelectors: {
            total: '#statTotal',
            active: '#statActive',
            inactive: '#statInactive',
            pendingResets: '#statPendingResets',
        },
    });
});
</script>
@endsection
