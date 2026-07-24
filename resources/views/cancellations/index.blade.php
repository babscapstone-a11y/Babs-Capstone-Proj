@extends('layouts.admin')

@section('title', 'Cancellation Requests')
@section('page-title', 'Order Cancellation Review')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <span>Cancellation Requests</span>
@endsection

@section('styles')
<style>
    /* ── Stats row ──────────────────────────────────────────── */
    .cr-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .9rem;
        margin-bottom: 1.5rem;
    }
    .cr-stat {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        display: flex; align-items: center; gap: .85rem;
        box-shadow: 0 2px 8px rgba(17,24,39,0.04);
        position: relative; overflow: hidden;
    }
    .cr-stat::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: 14px 14px 0 0;
        background: var(--stat-bar, linear-gradient(90deg, var(--primary), #F97316));
    }
    .cr-stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem; flex-shrink: 0;
    }
    .cr-stat-val { font-size: 1.65rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .cr-stat-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); margin-top: .25rem; }

    /* ── Filter bar ─────────────────────────────────────────── */
    .filter-bar {
        background: #fff; border: 1.5px solid var(--border); border-radius: 14px;
        padding: .9rem 1.1rem; margin-bottom: 1.25rem;
        display: flex; align-items: center; gap: .65rem; flex-wrap: wrap;
    }
    .filter-bar select {
        height: 38px; padding: 0 .75rem;
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: .83rem; font-family: inherit; color: var(--dark);
        background: var(--bg); outline: none; transition: border-color .18s;
    }
    .filter-bar select:focus { border-color: var(--primary); }

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
        display: flex; align-items: center; gap: .4rem; text-decoration: none;
    }
    .btn-reset:hover { border-color: var(--primary); color: var(--primary); }

    /* ── Table ──────────────────────────────────────────────── */
    .table-card { background: #fff; border: 1.5px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(17,24,39,0.05); }
    .table-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .table-header h2 { font-size: .95rem; font-weight: 700; color: var(--dark); margin: 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    thead th {
        background: var(--bg); padding: .65rem .9rem;
        text-align: left; font-size: .68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: var(--muted);
        border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #FAFAFA; }
    td { padding: .7rem .9rem; color: var(--dark); vertical-align: middle; }

    .cust-name { font-weight: 600; color: var(--dark); font-size: .84rem; }
    .reason-cell { max-width: 220px; font-size: .8rem; color: var(--muted); white-space: normal; }

    .action-group { display: flex; align-items: center; gap: .35rem; justify-content: flex-end; flex-wrap: wrap; }
    .btn-action {
        display: inline-flex; align-items: center; gap: .28rem;
        padding: .3rem .65rem; border-radius: 8px; font-size: .74rem; font-weight: 600;
        border: 1.5px solid; cursor: pointer; font-family: inherit;
        text-decoration: none; white-space: nowrap; transition: all .18s; background: none;
    }
    .btn-view  { color: #2563EB; border-color: rgba(37,99,235,0.3); background: rgba(37,99,235,0.06); }
    .btn-view:hover { background: rgba(37,99,235,0.12); }
    .btn-appr  { color: #15803D; border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
    .btn-appr:hover { background: rgba(22,163,74,0.12); }
    .btn-rej   { color: #B91C1C; border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .btn-rej:hover { background: rgba(220,38,38,0.12); }

    .empty-state { text-align: center; padding: 3.5rem 2rem; color: var(--muted); }
    .empty-state i { font-size: 2.5rem; margin-bottom: .85rem; display: block; opacity: .3; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0 0 .35rem; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    .anim-1 { animation: fadeUp .45s ease both; }
    .anim-2 { animation: fadeUp .45s .07s ease both; }
    .anim-3 { animation: fadeUp .45s .14s ease both; }

    @media (max-width: 700px) { .cr-stats { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="cr-stats anim-1">
    <div class="cr-stat" style="--stat-bar: linear-gradient(90deg,#F59E0B,#F97316)" id="statPendingCard">
        <div class="cr-stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="cr-stat-val" id="statPending" style="color:#D97706">{{ $pendingCount }}</div>
            <div class="cr-stat-lbl">Pending Requests</div>
        </div>
    </div>
    <div class="cr-stat" style="--stat-bar: linear-gradient(90deg,#16A34A,#059669)">
        <div class="cr-stat-icon" style="background:rgba(22,163,74,0.10);color:#16A34A"><i class="fas fa-circle-check"></i></div>
        <div>
            <div class="cr-stat-val" id="statApproved" style="color:#16A34A">{{ $approvedCount }}</div>
            <div class="cr-stat-lbl">Approved Requests</div>
        </div>
    </div>
    <div class="cr-stat" style="--stat-bar: linear-gradient(90deg,#DC2626,#F97316)">
        <div class="cr-stat-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)"><i class="fas fa-circle-xmark"></i></div>
        <div>
            <div class="cr-stat-val" id="statRejected" style="color:var(--primary)">{{ $rejectedCount }}</div>
            <div class="cr-stat-lbl">Rejected Requests</div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('cancellations.index') }}" class="filter-bar anim-2" id="liveFilterForm">
    <div class="search-wrap">
        <input type="text" id="search" name="search" class="search-input"
               placeholder="Search by order number, customer name, or request number…"
               value="{{ request('search') }}" autocomplete="off">
        <button type="button" class="search-clear" aria-label="Clear search">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <select name="status">
        <option value="">All Requests</option>
        <option value="pending"  @selected(request('status') === 'pending')>Pending Review</option>
        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
    </select>

    <a href="{{ route('cancellations.index') }}" class="btn-reset"><i class="fas fa-rotate-left"></i> Reset</a>
</form>

{{-- Table --}}
<div class="table-card anim-3" id="results">
    @include('cancellations._results', ['cancellationRequests' => $cancellationRequests])
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="rejectModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon danger"><i class="fas fa-xmark"></i></div>
        <h3 class="modal-title">Reject Cancellation Request</h3>
        <p class="modal-desc">Provide a reason for rejecting <strong id="rejectReqNumber"></strong>. The customer will see this reason.</p>
        <form id="rejectForm" method="POST">
            @csrf @method('PUT')
            <select id="rejectReasonPreset" style="width:100%;margin-bottom:.6rem;height:40px;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:0 .7rem;font-size:.85rem;font-family:inherit;color:var(--dark);background:#fff">
                <option value="">Choose a common reason…</option>
                <option value="Food preparation has already started.">Food preparation has already started.</option>
                <option value="Order is already ready for pickup.">Order is already ready for pickup.</option>
                <option value="Cancellation request is not valid.">Cancellation request is not valid.</option>
                <option value="Order has already been completed.">Order has already been completed.</option>
            </select>
            <textarea name="rejection_reason" id="rejectReasonText" class="reject-note-input" placeholder="Rejection reason…" rows="3" required
                      style="width:100%;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:.55rem .85rem;font-size:.85rem;color:var(--dark);font-family:inherit;resize:vertical;outline:none;min-height:72px"></textarea>
            <div class="modal-actions" style="margin-top:1rem">
                <button type="button" class="btn-modal-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-modal-confirm" style="background:#DC2626">
                    <i class="fas fa-xmark"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openRejectModal(id, action, reqNumber) {
    document.getElementById('rejectReqNumber').textContent = reqNumber;
    document.getElementById('rejectForm').action = action;
    document.getElementById('rejectReasonPreset').value = '';
    document.getElementById('rejectReasonText').value = '';
    document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() { document.getElementById('rejectModal').classList.remove('open'); }
document.getElementById('rejectModal').addEventListener('click', function (e) {
    if (e.target === this) closeRejectModal();
});
document.getElementById('rejectReasonPreset').addEventListener('change', function () {
    if (this.value) document.getElementById('rejectReasonText').value = this.value;
});

document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('cancellations.index') }}',
        searchFieldName: 'search',
        debounceMs: 300,
        statsSelectors: { pendingCount: '#statPending', approvedCount: '#statApproved', rejectedCount: '#statRejected' },
    });
});
</script>
@endsection
