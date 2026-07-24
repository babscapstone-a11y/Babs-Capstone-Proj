@extends('layouts.admin')

@php
    $customer = $cancellationRequest->customer;
    $eligible = $order && $order->isCancellationEligible();
@endphp

@section('title', 'Review ' . ($cancellationRequest->request_number ?? '#'.$cancellationRequest->id))
@section('page-title', 'Cancellation Request Review')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('cancellations.index') }}">Cancellation Requests</a>
    <span class="breadcrumb-sep">/</span>
    <span>{{ $cancellationRequest->request_number ?? '#'.$cancellationRequest->id }}</span>
@endsection

@section('styles')
<style>
    .review-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
    @media (max-width: 900px) { .review-grid { grid-template-columns: 1fr; } }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 480px) { .info-grid { grid-template-columns: 1fr; } }
    .info-item { display: flex; flex-direction: column; gap: .2rem; }
    .info-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); }
    .info-value { font-size: .9rem; font-weight: 600; color: var(--dark); }

    .items-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .items-table th {
        padding: .65rem 1rem; text-align: left;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: var(--muted); background: #F8FAFC; border-bottom: 1px solid var(--border);
    }
    .items-table td { padding: .8rem 1rem; border-bottom: 1px solid #F5F5F5; vertical-align: middle; }
    .items-table tr:last-child td { border-bottom: none; }
    .order-summary { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }
    .summary-row { display: flex; justify-content: space-between; padding: .4rem 0; font-size: .85rem; }
    .summary-row.total { border-top: 2px solid var(--border); margin-top: .4rem; padding-top: .85rem; font-size: 1.05rem; font-weight: 800; color: var(--dark); }

    .warn-banner {
        background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 12px;
        padding: 1rem 1.25rem; margin-bottom: 1.25rem;
        display: flex; gap: .75rem; align-items: flex-start;
        font-size: .84rem; color: #B91C1C;
    }
    .warn-banner i { font-size: 1.1rem; margin-top: .05rem; flex-shrink: 0; }

    .decision-box {
        border-radius: 12px; padding: 1rem 1.25rem; font-size: .84rem;
        display: flex; gap: .75rem; align-items: flex-start;
    }
    .decision-box.approved { background: #F0FDF4; border: 1.5px solid #BBF7D0; color: #15803D; }
    .decision-box.rejected { background: #FEF2F2; border: 1.5px solid #FECACA; color: #B91C1C; }
    .decision-box i { font-size: 1.1rem; margin-top: .05rem; flex-shrink: 0; }

    /* Timeline */
    .timeline { display: flex; gap: 0; overflow-x: auto; padding: .5rem 0; }
    .tl-step { display: flex; flex-direction: column; align-items: center; flex: 1; min-width: 80px; position: relative; }
    .tl-step:not(:last-child)::after {
        content: ''; position: absolute; top: 18px; left: calc(50% + 18px); right: calc(-50% + 18px);
        height: 2px; background: var(--border); z-index: 0;
    }
    .tl-step.done:not(:last-child)::after { background: var(--primary); }
    .tl-dot {
        width: 36px; height: 36px; border-radius: 50%; background: var(--bg); border: 2px solid var(--border);
        display: flex; align-items: center; justify-content: center; font-size: .8rem; color: var(--muted);
        z-index: 1; position: relative; flex-shrink: 0;
    }
    .tl-step.done .tl-dot { background: var(--primary); border-color: var(--primary); color: #fff; }
    .tl-step.current .tl-dot { background: var(--accent); border-color: var(--accent); color: #fff; }
    .tl-step.cancelled .tl-dot { background: #FEE2E2; border-color: #DC2626; color: #DC2626; }
    .tl-label { font-size: .68rem; font-weight: 600; color: var(--muted); text-align: center; margin-top: .45rem; line-height: 1.3; }
    .tl-step.done .tl-label { color: var(--primary); }
    .tl-step.current .tl-label { color: var(--dark); font-weight: 700; }
    .tl-step.cancelled .tl-label { color: #DC2626; }

    .reason-quote {
        background: #FAFBFC; border: 1.5px solid var(--border); border-radius: 12px;
        padding: 1rem 1.25rem; font-size: .85rem; color: var(--dark); display: flex; gap: .65rem;
    }
    .reason-quote i { color: var(--muted); margin-top: .1rem; flex-shrink: 0; }
</style>
@endsection

@section('content')

<div style="margin-bottom:1.1rem">
    <a href="{{ route('cancellations.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Requests
    </a>
</div>

@if(session('error'))
<div class="warn-banner" style="margin-bottom:1.1rem">
    <i class="fas fa-circle-exclamation"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

{{-- Eligibility warning --}}
@if($cancellationRequest->isPending() && ! $eligible)
<div class="warn-banner">
    <i class="fas fa-triangle-exclamation"></i>
    <div>
        <strong>This order is no longer eligible for cancellation.</strong><br>
        This order has already entered the preparation stage and cannot be cancelled. You may only reject this request.
    </div>
</div>
@endif

<div class="review-grid">

    {{-- Left column --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Customer Information --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-user" style="color:var(--primary);margin-right:.4rem"></i>Customer Information</h2></div>
            <div class="card-body info-grid">
                <div class="info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value">{{ $customer?->full_name ?? 'Unknown' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Contact Number</span>
                    <span class="info-value">{{ $customer?->contact_no ?: '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">{{ $customer?->email ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Order Information --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-receipt" style="color:var(--primary);margin-right:.4rem"></i>Order Information</h2></div>
            <div class="card-body info-grid">
                <div class="info-item">
                    <span class="info-label">Order Number</span>
                    <span class="info-value">{{ $order?->order_number ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Type</span>
                    <span class="info-value">{{ $order?->order_type_label ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date Ordered</span>
                    <span class="info-value">{{ $order?->created_at?->format('F d, Y h:i A') ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Status</span>
                    @if($order)
                    <span class="badge" style="background:{{ $order->status_color }}1a;color:{{ $order->status_color }};width:fit-content">{{ $order->status_name }}</span>
                    @else
                    <span class="info-value">—</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Ordered Items --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-list-ul" style="color:var(--primary);margin-right:.4rem"></i>Ordered Items</h2></div>
            @if(! $order || $order->details->isEmpty())
                <div style="padding:2rem;text-align:center;color:var(--muted);font-size:.85rem">No items recorded for this order.</div>
            @else
                <div style="overflow-x:auto">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align:center">Qty</th>
                                <th style="text-align:right">Unit Price</th>
                                <th style="text-align:right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->details as $detail)
                            <tr>
                                <td style="font-weight:600">{{ $detail->item_name }}</td>
                                <td style="text-align:center">{{ $detail->quantity }}</td>
                                <td style="text-align:right;color:var(--muted)">₱{{ number_format($detail->price, 2) }}</td>
                                <td style="text-align:right;font-weight:700">₱{{ number_format($detail->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="order-summary">
                    <div class="summary-row"><span style="color:var(--muted)">Total Items</span><span>{{ $order->details->sum('quantity') }}</span></div>
                    <div class="summary-row total"><span>Grand Total</span><span style="color:var(--primary)">₱{{ number_format($order->total_amount, 2) }}</span></div>
                </div>
            @endif
        </div>

        {{-- Order Status Timeline --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-timeline" style="color:var(--primary);margin-right:.4rem"></i>Order Status Timeline</h2></div>
            <div class="card-body">
                @php
                    $allStatuses  = ['Pending', 'Processing', 'Ready', 'Completed'];
                    $statusLabels = ['Pending' => 'Order Received', 'Processing' => 'Preparing', 'Ready' => 'Ready', 'Completed' => 'Completed'];
                    $statusIcons  = ['Pending' => 'fa-clock', 'Processing' => 'fa-fire-burner', 'Ready' => 'fa-bell', 'Completed' => 'fa-circle-check'];
                    $isCancelledOrder = $order?->isCancelled();
                    $currentIdx = $order ? array_search($order->status_name, $allStatuses) : false;
                @endphp
                @if($isCancelledOrder)
                    <div style="display:flex;align-items:center;gap:1.25rem">
                        <div class="tl-dot" style="width:40px;height:40px;font-size:.9rem;background:#FEE2E2;border:2px solid #DC2626;color:#DC2626">
                            <i class="fas fa-xmark"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:#DC2626">Order Cancelled</div>
                            @if($order->cancellation_reason)
                            <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">{{ $order->cancellation_reason }}</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="timeline">
                        @foreach($allStatuses as $idx => $sName)
                        @php
                            $isDone    = $currentIdx !== false && $idx < $currentIdx;
                            $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                            $stepClass = $isDone ? 'done' : ($isCurrent ? 'current' : '');
                        @endphp
                        <div class="tl-step {{ $stepClass }}">
                            <div class="tl-dot"><i class="fas {{ $statusIcons[$sName] }}"></i></div>
                            <div class="tl-label">{{ $statusLabels[$sName] }}</div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right column (sidebar) --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Cancellation Request --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-ban" style="color:var(--primary);margin-right:.4rem"></i>Cancellation Request</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                <div class="info-item">
                    <span class="info-label">Request Number</span>
                    <span class="info-value">{{ $cancellationRequest->request_number ?? '#'.$cancellationRequest->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Request Date</span>
                    <span class="info-value" style="font-size:.84rem">{{ $cancellationRequest->created_at->format('F d, Y h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Review Status</span>
                    <span class="badge {{ $cancellationRequest->review_status_badge_class }}" style="width:fit-content">
                        {{ $cancellationRequest->review_status_label }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cancellation Reason</span>
                    <div class="reason-quote" style="margin-top:.2rem">
                        <i class="fas fa-quote-left"></i>
                        <span>{{ $cancellationRequest->cancellation_reason }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Administrator Decision --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-gavel" style="color:var(--primary);margin-right:.4rem"></i>Administrator Decision</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">

                @if($cancellationRequest->isApproved())
                    <div class="decision-box approved">
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Approved</strong><br>
                            By {{ $cancellationRequest->reviewedBy?->name ?? 'Unknown' }} on {{ $cancellationRequest->review_date?->format('F d, Y h:i A') }}
                        </div>
                    </div>
                @elseif($cancellationRequest->isRejected())
                    <div class="decision-box rejected">
                        <i class="fas fa-circle-xmark"></i>
                        <div>
                            <strong>Rejected</strong><br>
                            By {{ $cancellationRequest->reviewedBy?->name ?? 'Unknown' }} on {{ $cancellationRequest->review_date?->format('F d, Y h:i A') }}
                            @if($cancellationRequest->rejection_reason)
                                <div style="margin-top:.4rem;font-style:italic">"{{ $cancellationRequest->rejection_reason }}"</div>
                            @endif
                        </div>
                    </div>
                @else
                    <p style="font-size:.83rem;color:var(--muted);margin:0">
                        Review the order and customer details, then approve or reject this cancellation request.
                    </p>

                    @if($eligible)
                    <button type="button" class="btn btn-success" style="justify-content:center"
                        onclick="openModal({
                            type: 'warn',
                            iconClass: 'fas fa-circle-check',
                            title: 'Approve Cancellation?',
                            desc: 'Are you sure you want to approve this cancellation request?',
                            action: '{{ route('cancellations.approve', $cancellationRequest) }}',
                            method: 'PUT',
                            confirmText: 'Approve Request',
                        })">
                        <i class="fas fa-check"></i> Approve Request
                    </button>
                    @endif

                    <button type="button" class="btn btn-danger" style="justify-content:center"
                        onclick="openRejectModal('{{ route('cancellations.reject', $cancellationRequest) }}', '{{ addslashes($cancellationRequest->request_number ?? '#'.$cancellationRequest->id) }}')">
                        <i class="fas fa-xmark"></i> Reject Request
                    </button>
                @endif

                <a href="{{ route('cancellations.index') }}" class="btn btn-secondary" style="justify-content:center">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>

    </div>
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
            <textarea name="rejection_reason" id="rejectReasonText" placeholder="Rejection reason…" rows="3" required
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
function openRejectModal(action, reqNumber) {
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
</script>
@endsection
