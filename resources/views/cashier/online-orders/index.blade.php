@extends('layouts.cashier')

@section('title', 'Online Orders')

@section('styles')
<style>
    .summary-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .summary-card {
        background: var(--white); border-radius: 14px; padding: 1.1rem 1.3rem;
        border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(17,24,39,0.05);
        display: flex; align-items: center; gap: .9rem;
    }
    .summary-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .summary-icon.pending  { background: rgba(245,158,11,0.14); color: var(--accent); }
    .summary-icon.approved { background: rgba(22,163,74,0.14);  color: #16A34A; }
    .summary-icon.rejected { background: rgba(220,38,38,0.12);  color: var(--primary); }
    .summary-icon.verified { background: rgba(37,99,235,0.14);  color: #2563EB; }
    .summary-count { font-size: 1.6rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .summary-label  { font-size: .78rem; color: var(--muted); font-weight: 600; margin-top: .2rem; }

    .filter-bar {
        background: var(--white); border: 1.5px solid var(--border); border-radius: 14px;
        padding: .9rem 1.1rem; margin-bottom: 1.25rem;
        display: flex; align-items: center; gap: .65rem; flex-wrap: wrap;
    }
    .filter-bar select {
        height: 40px; padding: 0 .75rem; border: 1.5px solid var(--border); border-radius: 9px;
        font-size: .83rem; font-family: inherit; color: var(--dark); background: var(--bg); outline: none;
    }
    .search-wrap { position: relative; flex: 1; min-width: 220px; }
    .search-input {
        width: 100%; height: 40px; padding: 0 2.3rem 0 .85rem; border: 1.5px solid var(--border);
        border-radius: 9px; font-size: .85rem; font-family: inherit; color: var(--dark); background: var(--bg); outline: none;
    }
    .search-input:focus, .filter-bar select:focus { border-color: var(--primary); }
    .search-clear { position: absolute; right: .6rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--muted); cursor: pointer; padding: .25rem; display: none; }
    .search-wrap.has-value .search-clear { display: block; }
    .results-count { font-size: .8rem; color: var(--muted); padding: .85rem 1.25rem 0; }
    #results.is-loading { opacity: .5; transition: opacity .15s; }

    .table-card { background: var(--white); border: 1.5px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(17,24,39,0.05); }
    .table-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .table-header h2 { font-size: .95rem; font-weight: 700; color: var(--dark); margin: 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    thead th {
        background: var(--bg); padding: .65rem .9rem; text-align: left; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: var(--muted); border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--border); }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #FAFAFA; }
    td { padding: .7rem .9rem; color: var(--dark); vertical-align: middle; }

    .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem; border-radius: 50px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .badge-pending   { background: rgba(245,158,11,0.14); color: #B45309; }
    .badge-approved  { background: rgba(22,163,74,0.12);  color: #15803D; }
    .badge-rejected  { background: rgba(220,38,38,0.10);  color: #B91C1C; }
    .badge-cancelled { background: rgba(107,114,128,0.12); color: #4B5563; }

    .action-group { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
    .btn-action {
        display: inline-flex; align-items: center; gap: .28rem; padding: .32rem .65rem; border-radius: 8px;
        font-size: .74rem; font-weight: 600; border: 1.5px solid; cursor: pointer; font-family: inherit;
        text-decoration: none; white-space: nowrap; transition: all .18s; background: none;
    }
    .btn-view    { color: #2563EB; border-color: rgba(37,99,235,0.3); background: rgba(37,99,235,0.06); }
    .btn-view:hover { background: rgba(37,99,235,0.12); }
    .btn-approve { color: #15803D; border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
    .btn-approve:hover { background: rgba(22,163,74,0.12); }
    .btn-reject  { color: #B91C1C; border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .btn-reject:hover { background: rgba(220,38,38,0.12); }

    .empty-state { text-align: center; padding: 3.5rem 2rem; color: var(--muted); }
    .empty-state i { font-size: 2.5rem; margin-bottom: .85rem; display: block; opacity: .4; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0 0 .35rem; }

    /* Reject reason modal */
    .modal-box textarea {
        width: 100%; border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px;
        padding: .65rem .85rem; font-size: .87rem; font-family: inherit; outline: none; resize: vertical; min-height: 90px;
    }
    .reason-presets { display: flex; flex-wrap: wrap; gap: .4rem; margin: .6rem 0; }
    .reason-preset {
        font-size: .74rem; padding: .3rem .65rem; border-radius: 50px; border: 1.5px solid var(--border);
        background: var(--bg); cursor: pointer; color: var(--muted);
    }
    .reason-preset:hover { border-color: var(--primary); color: var(--primary); }

    @media (max-width: 1100px) { .summary-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .summary-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon pending"><i class="fas fa-hourglass-half"></i></div>
        <div><div class="summary-count" id="statPendingCount">{{ $pendingCount }}</div><div class="summary-label">Pending Online Orders</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon approved"><i class="fas fa-circle-check"></i></div>
        <div><div class="summary-count" id="statApprovedToday">{{ $approvedToday }}</div><div class="summary-label">Approved Today</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon rejected"><i class="fas fa-circle-xmark"></i></div>
        <div><div class="summary-count" id="statRejectedToday">{{ $rejectedToday }}</div><div class="summary-label">Rejected Today</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon verified"><i class="fas fa-shield-halved"></i></div>
        <div><div class="summary-count" id="statTotalVerified">{{ $totalVerified }}</div><div class="summary-label">Total Verified Payments</div></div>
    </div>
</div>

<form method="GET" action="{{ route('cashier.online-orders.index') }}" class="filter-bar" id="liveFilterForm">
    <div class="search-wrap">
        <input type="text" id="q" name="q" class="search-input"
               placeholder="Search by order number, customer name, or contact number…"
               value="{{ request('q') }}" autocomplete="off">
        <button type="button" class="search-clear" aria-label="Clear search"><i class="fas fa-times"></i></button>
    </div>
    <select name="status">
        <option value="pending"   @selected($status === 'pending')>Pending Approval</option>
        <option value="approved"  @selected($status === 'approved')>Approved</option>
        <option value="rejected"  @selected($status === 'rejected')>Rejected</option>
        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
    </select>
</form>

<div class="table-card" id="results">
    @include('cashier.online-orders._results', ['orders' => $orders, 'status' => $status])
</div>

{{-- Reject reason modal --}}
<div class="modal-overlay" id="rejectModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon"><i class="fas fa-ban"></i></div>
        <h3 class="modal-title">Reject Online Order?</h3>
        <p class="modal-desc">Please provide a reason. This will be shown to the customer.</p>
        <div class="reason-presets">
            <button type="button" class="reason-preset" data-reason="Invalid proof of payment">Invalid proof of payment</button>
            <button type="button" class="reason-preset" data-reason="Incorrect payment amount">Incorrect payment amount</button>
            <button type="button" class="reason-preset" data-reason="Unclear payment receipt">Unclear payment receipt</button>
            <button type="button" class="reason-preset" data-reason="Payment not received">Payment not received</button>
        </div>
        <textarea id="rejectReasonInput" placeholder="Reason for rejection..."></textarea>
        <div class="modal-actions" style="margin-top:1rem">
            <button type="button" class="btn-modal-cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" id="rejectConfirmBtn" onclick="submitReject()">Reject Order</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let rejectOrderId = null;

    document.querySelectorAll('.reason-preset').forEach(btn => {
        btn.addEventListener('click', () => { document.getElementById('rejectReasonInput').value = btn.dataset.reason; });
    });

    function openRejectModal(orderId) {
        rejectOrderId = orderId;
        document.getElementById('rejectReasonInput').value = '';
        document.getElementById('rejectModal').classList.add('open');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('open');
        rejectOrderId = null;
    }
    document.getElementById('rejectModal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeRejectModal();
    });

    async function submitReject() {
        const reason = document.getElementById('rejectReasonInput').value.trim();
        if (! reason) { showToast('Please provide a rejection reason.', 'error'); return; }

        try {
            const res = await fetch(`/cashier/online-orders/${rejectOrderId}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify({ reason }),
            });
            const data = await res.json();
            closeRejectModal();

            if (! res.ok) { showToast(data.message || 'Failed to reject order.', 'error'); return; }

            showToast(data.message, 'success');
            refreshTable();
        } catch (e) {
            closeRejectModal();
            showToast('Failed to reject order.', 'error');
        }
    }

    function confirmApprove(orderId, orderNumber) {
        openConfirmModal({
            title: 'Approve Online Order?',
            desc: `Approve Order #${orderNumber}? It will be forwarded to the Kitchen Display System immediately.`,
            confirmText: 'Approve Order',
            onConfirm: () => submitApprove(orderId),
        });
    }

    async function submitApprove(orderId) {
        try {
            const res = await fetch(`/cashier/online-orders/${orderId}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            });
            const data = await res.json();
            closeConfirmModal();

            if (! res.ok) { showToast(data.message || 'Failed to approve order.', 'error'); return; }

            showToast(data.message, 'success');
            refreshTable();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to approve order.', 'error');
        }
    }

    function refreshTable() {
        const form = document.getElementById('liveFilterForm');
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of fd.entries()) { if (value !== '') params.set(key, value); }

        fetch(`{{ route('cashier.online-orders.index') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('results').innerHTML = data.html;
                if (data.stats) {
                    document.getElementById('statPendingCount').textContent = data.stats.pendingCount;
                    document.getElementById('statApprovedToday').textContent = data.stats.approvedToday;
                    document.getElementById('statRejectedToday').textContent = data.stats.rejectedToday;
                    document.getElementById('statTotalVerified').textContent = data.stats.totalVerified;
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        LiveTable.init({
            formSelector: '#liveFilterForm',
            resultsSelector: '#results',
            url: '{{ route('cashier.online-orders.index') }}',
            searchFieldName: 'q',
            debounceMs: 300,
            statsSelectors: {
                pendingCount: '#statPendingCount',
                approvedToday: '#statApprovedToday',
                rejectedToday: '#statRejectedToday',
                totalVerified: '#statTotalVerified',
            },
        });
    });
</script>
@endsection
