@extends('layouts.admin')

@php
    $order = $specialDiscountRequest->order;
@endphp

@section('title', 'Review ' . ($specialDiscountRequest->request_number ?? '#'.$specialDiscountRequest->id))
@section('page-title', 'Special Discount Request Review')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('discounts.index') }}">Discounts</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('special-discount-requests.index') }}">Special Discount Requests</a>
    <span class="breadcrumb-sep">/</span>
    <span>{{ $specialDiscountRequest->request_number ?? '#'.$specialDiscountRequest->id }}</span>
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
    .summary-row.discount { color: var(--primary); }

    .amount-hero {
        background: linear-gradient(135deg, var(--dark), #1F2937); border-radius: 14px;
        padding: 1.25rem; color: #fff; text-align: center; margin-bottom: 0;
    }
    .amount-hero .ah-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.5); margin-bottom: .5rem; }
    .amount-hero .ah-value { font-size: 2.2rem; font-weight: 900; color: var(--accent); line-height: 1; }

    .decision-box {
        border-radius: 12px; padding: 1rem 1.25rem; font-size: .84rem;
        display: flex; gap: .75rem; align-items: flex-start;
    }
    .decision-box.approved { background: #F0FDF4; border: 1.5px solid #BBF7D0; color: #15803D; }
    .decision-box.rejected { background: #FEF2F2; border: 1.5px solid #FECACA; color: #B91C1C; }
    .decision-box i { font-size: 1.1rem; margin-top: .05rem; flex-shrink: 0; }

    .reason-quote {
        background: #FAFBFC; border: 1.5px solid var(--border); border-radius: 12px;
        padding: 1rem 1.25rem; font-size: .85rem; color: var(--dark); display: flex; gap: .65rem;
    }
    .reason-quote i { color: var(--muted); margin-top: .1rem; flex-shrink: 0; }
</style>
@endsection

@section('content')

<div style="margin-bottom:1.1rem">
    <a href="{{ route('special-discount-requests.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Requests
    </a>
</div>

@if(session('error'))
<div class="decision-box rejected" style="margin-bottom:1.1rem">
    <i class="fas fa-circle-exclamation"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

<div class="review-grid">

    {{-- Left column --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Cashier & Order Information --}}
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
                    <span class="info-label">Requested By</span>
                    <span class="info-value">{{ $specialDiscountRequest->cashier?->name ?: ($specialDiscountRequest->cashier?->username ?? 'Unknown') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Order Status</span>
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
                @php $subtotal = $order->details->sum('subtotal'); @endphp
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
                    <div class="summary-row"><span style="color:var(--muted)">Subtotal</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
                    <div class="summary-row discount"><span>Requested Special Discount</span><span>- ₱{{ number_format($specialDiscountRequest->requested_amount, 2) }}</span></div>
                    <div class="summary-row total"><span>Total If Approved</span><span style="color:var(--primary)">₱{{ number_format(max($subtotal - $specialDiscountRequest->requested_amount, 0), 2) }}</span></div>
                </div>
            @endif
        </div>

    </div>

    {{-- Right column (sidebar) --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        <div class="amount-hero">
            <div class="ah-label">Requested Amount</div>
            <div class="ah-value">₱{{ number_format($specialDiscountRequest->requested_amount, 2) }}</div>
        </div>

        {{-- Special Discount Request --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-hand-holding-dollar" style="color:var(--primary);margin-right:.4rem"></i>Request Details</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                <div class="info-item">
                    <span class="info-label">Request Number</span>
                    <span class="info-value">{{ $specialDiscountRequest->request_number ?? '#'.$specialDiscountRequest->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Discount Rule Used</span>
                    <span class="info-value">{{ $specialDiscountRequest->discount?->discount_name ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Request Date</span>
                    <span class="info-value" style="font-size:.84rem">{{ $specialDiscountRequest->created_at->format('F d, Y h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Review Status</span>
                    <span class="badge {{ $specialDiscountRequest->review_status_badge_class }}" style="width:fit-content">
                        {{ $specialDiscountRequest->review_status_label }}
                    </span>
                </div>
                @if($specialDiscountRequest->reason)
                <div class="info-item">
                    <span class="info-label">Cashier's Reason</span>
                    <div class="reason-quote" style="margin-top:.2rem">
                        <i class="fas fa-quote-left"></i>
                        <span>{{ $specialDiscountRequest->reason }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Administrator Decision --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-gavel" style="color:var(--primary);margin-right:.4rem"></i>Administrator Decision</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">

                @if($specialDiscountRequest->isApproved())
                    <div class="decision-box approved">
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Approved</strong><br>
                            By {{ $specialDiscountRequest->reviewedBy?->name ?? 'Unknown' }} on {{ $specialDiscountRequest->review_date?->format('F d, Y h:i A') }}
                        </div>
                    </div>
                @elseif($specialDiscountRequest->isRejected())
                    <div class="decision-box rejected">
                        <i class="fas fa-circle-xmark"></i>
                        <div>
                            <strong>Rejected</strong><br>
                            By {{ $specialDiscountRequest->reviewedBy?->name ?? 'Unknown' }} on {{ $specialDiscountRequest->review_date?->format('F d, Y h:i A') }}
                            @if($specialDiscountRequest->rejection_reason)
                                <div style="margin-top:.4rem;font-style:italic">"{{ $specialDiscountRequest->rejection_reason }}"</div>
                            @endif
                        </div>
                    </div>
                @else
                    <p style="font-size:.83rem;color:var(--muted);margin:0">
                        Review the order and requested amount, then approve or reject this special discount request.
                    </p>

                    <button type="button" class="btn btn-success" style="justify-content:center"
                        onclick="openModal({
                            type: 'warn',
                            iconClass: 'fas fa-circle-check',
                            title: 'Approve Special Discount?',
                            desc: 'Are you sure you want to approve this ₱{{ number_format($specialDiscountRequest->requested_amount, 2) }} special discount request?',
                            action: '{{ route('special-discount-requests.approve', $specialDiscountRequest) }}',
                            method: 'PUT',
                            confirmText: 'Approve Request',
                        })">
                        <i class="fas fa-check"></i> Approve Request
                    </button>

                    <button type="button" class="btn btn-danger" style="justify-content:center"
                        onclick="openRejectModal('{{ route('special-discount-requests.reject', $specialDiscountRequest) }}', '{{ addslashes($specialDiscountRequest->request_number ?? '#'.$specialDiscountRequest->id) }}')">
                        <i class="fas fa-xmark"></i> Reject Request
                    </button>
                @endif

                <a href="{{ route('special-discount-requests.index') }}" class="btn btn-secondary" style="justify-content:center">
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
        <h3 class="modal-title">Reject Special Discount Request</h3>
        <p class="modal-desc">Provide a reason for rejecting <strong id="rejectReqNumber"></strong>.</p>
        <form id="rejectForm" method="POST">
            @csrf @method('PUT')
            <select id="rejectReasonPreset" style="width:100%;margin-bottom:.6rem;height:40px;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:0 .7rem;font-size:.85rem;font-family:inherit;color:var(--dark);background:#fff">
                <option value="">Choose a common reason…</option>
                <option value="Requested amount is too high for this order.">Requested amount is too high for this order.</option>
                <option value="No valid business reason provided for this discount.">No valid business reason provided for this discount.</option>
                <option value="Please use an existing discount rule instead.">Please use an existing discount rule instead.</option>
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
