@extends('layouts.cashier')

@section('title', 'Order #' . $order->order_number)

@section('styles')
<style>
    .detail-layout { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.25rem; align-items: start; }
    @media (max-width: 1100px) { .detail-layout { grid-template-columns: 1fr; } }

    .back-link {
        display: inline-flex; align-items: center; gap: .45rem; font-size: .85rem; font-weight: 600;
        color: var(--primary); margin-bottom: 1.1rem;
    }
    .back-link:hover { text-decoration: underline; }

    .order-hero {
        background: var(--dark); color: #fff; border-radius: 16px; padding: 1.4rem 1.5rem;
        margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }
    .order-hero .oh-number { font-size: 1.3rem; font-weight: 800; }
    .order-hero .oh-sub { font-size: .78rem; color: rgba(255,255,255,.5); margin-top: .2rem; }
    .order-hero .oh-total { font-size: 1.5rem; font-weight: 800; color: var(--accent); text-align: right; }
    .order-hero .oh-total-label { font-size: .72rem; color: rgba(255,255,255,.5); text-align: right; }

    .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .25rem .75rem; border-radius: 50px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
    .badge-pending   { background: rgba(245,158,11,0.16); color: #FDE68A; }
    .badge-approved  { background: rgba(22,163,74,0.18);  color: #86EFAC; }
    .badge-rejected  { background: rgba(220,38,38,0.18);  color: #FCA5A5; }
    .badge-cancelled { background: rgba(107,114,128,0.18); color: #D1D5DB; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
    @media (max-width: 480px) { .info-grid { grid-template-columns: 1fr; } }
    .info-item .label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); margin-bottom: .2rem; }
    .info-item .value { font-size: .9rem; font-weight: 600; color: var(--dark); }

    .item-row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px dashed var(--border); font-size: .88rem; }
    .item-row:last-child { border-bottom: none; }
    .item-name { font-weight: 600; color: var(--dark); }
    .item-unit { font-size: .76rem; color: var(--muted); }
    .item-qty { font-weight: 700; color: var(--primary); padding: 0 .6rem; }
    .item-sub { font-weight: 700; min-width: 80px; text-align: right; }

    .summary-row { display: flex; justify-content: space-between; padding: .4rem 0; font-size: .88rem; }
    .summary-row.total { border-top: 1.5px solid var(--border); margin-top: .4rem; padding-top: .7rem; font-size: 1.05rem; font-weight: 800; }

    .instructions-box {
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px;
        padding: .75rem .9rem; margin-top: 1rem; font-size: .86rem; color: #92400E;
    }

    .proof-thumb {
        width: 100%; max-width: 260px; border-radius: 12px; border: 1.5px solid var(--border);
        cursor: zoom-in; display: block; margin: 0 auto;
    }
    .proof-caption { text-align: center; font-size: .78rem; color: var(--muted); margin-top: .5rem; }

    .timeline-step { display: flex; gap: .8rem; padding-bottom: 1.1rem; position: relative; }
    .timeline-step:not(:last-child)::before {
        content: ''; position: absolute; left: 15px; top: 32px; bottom: 0; width: 2px; background: var(--border);
    }
    .timeline-dot {
        width: 32px; height: 32px; border-radius: 50%; background: var(--bg); border: 2px solid var(--border);
        display: flex; align-items: center; justify-content: center; font-size: .75rem; color: var(--muted); flex-shrink: 0; z-index: 1;
    }
    .timeline-step.done .timeline-dot { background: var(--primary); border-color: var(--primary); color: #fff; }
    .timeline-step.rejected .timeline-dot { background: var(--primary); border-color: var(--primary); color: #fff; }
    .timeline-title { font-weight: 700; font-size: .88rem; color: var(--dark); }
    .timeline-time { font-size: .76rem; color: var(--muted); margin-top: .1rem; }
    .timeline-note { font-size: .8rem; color: var(--muted); margin-top: .3rem; }

    .reviewed-box {
        border-radius: 12px; padding: 1rem 1.1rem; font-size: .87rem; display: flex; gap: .7rem; align-items: flex-start;
    }
    .reviewed-box.approved { background: rgba(22,163,74,0.08); border: 1px solid rgba(22,163,74,0.25); color: #15803D; }
    .reviewed-box.rejected { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.25); color: #B91C1C; }

    /* Enlarge modal */
    .img-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center;
        z-index: 1100; padding: 2rem;
    }
    .img-modal-overlay.open { display: flex; }
    .img-modal-overlay img { max-width: 100%; max-height: 90vh; border-radius: 8px; }
    .img-modal-close {
        position: absolute; top: 1.5rem; right: 1.5rem; background: rgba(255,255,255,0.15); border: none;
        color: #fff; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1rem;
    }
</style>
@endsection

@section('content')

<a href="{{ route('cashier.online-orders.index') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Online Orders</a>

<div class="order-hero">
    <div>
        <div class="oh-number">#{{ $order->order_number }}</div>
        <div class="oh-sub">{{ $order->created_at->format('F d, Y \a\t h:i A') }} &middot; {{ $order->order_type_label }}</div>
        <div style="margin-top:.6rem"><span class="badge {{ $order->approval_status_badge_class }}">{{ $order->approval_status_label }}</span></div>
    </div>
    <div>
        <div class="oh-total">₱{{ number_format($order->total_amount, 2) }}</div>
        <div class="oh-total-label">Grand Total</div>
    </div>
</div>

<div class="detail-layout">

    {{-- Left column --}}
    <div>
        {{-- Customer Information --}}
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user"></i> Customer Information</h3></div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item"><div class="label">Customer Name</div><div class="value">{{ $order->customer_name }}</div></div>
                    <div class="info-item"><div class="label">Contact Number</div><div class="value">{{ $order->customer?->contact_no ?: '—' }}</div></div>
                    <div class="info-item"><div class="label">Email Address</div><div class="value">{{ $order->customer?->email ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-ul"></i> Order Summary</h3>
                <span style="font-size:.76rem;color:var(--muted)">{{ $order->item_count }} {{ Str::plural('item', $order->item_count) }}</span>
            </div>
            <div class="card-body">
                <div class="info-grid" style="margin-bottom:1rem">
                    <div class="info-item"><div class="label">Order Number</div><div class="value">#{{ $order->order_number }}</div></div>
                    <div class="info-item"><div class="label">Order Date</div><div class="value">{{ $order->created_at->format('M d, Y h:i A') }}</div></div>
                    <div class="info-item"><div class="label">Pick-up Date</div><div class="value">{{ $order->pickup_at?->format('M d, Y') ?? '—' }}</div></div>
                    <div class="info-item"><div class="label">Pick-up Time</div><div class="value">{{ $order->pickup_at?->format('h:i A') ?? '—' }}</div></div>
                </div>

                @foreach($order->details as $detail)
                    <div class="item-row">
                        <div>
                            <div class="item-name">{{ $detail->item_name }}</div>
                            <div class="item-unit">₱{{ number_format($detail->price, 2) }} each</div>
                        </div>
                        <div class="item-qty">×{{ $detail->quantity }}</div>
                        <div class="item-sub">₱{{ number_format($detail->subtotal, 2) }}</div>
                    </div>
                @endforeach

                <div class="summary-row"><span>Total Items</span><span>{{ $order->item_count }}</span></div>
                <div class="summary-row total"><span>Grand Total</span><span>₱{{ number_format($order->total_amount, 2) }}</span></div>

                @if($order->special_instructions)
                <div class="instructions-box"><i class="fas fa-circle-info"></i> {{ $order->special_instructions }}</div>
                @endif
            </div>
        </div>

        {{-- Order Timeline --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-timeline"></i> Order Timeline</h3></div>
            <div class="card-body">
                <div class="timeline-step done">
                    <div class="timeline-dot"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="timeline-title">Order Placed</div>
                        <div class="timeline-time">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
                @if($order->paymentProof)
                <div class="timeline-step done">
                    <div class="timeline-dot"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="timeline-title">Proof of Payment Submitted</div>
                        <div class="timeline-time">{{ ($order->paymentProof->paid_at ?? $order->paymentProof->created_at)->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
                @endif
                @if($order->approval_status === 'pending')
                <div class="timeline-step">
                    <div class="timeline-dot"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="timeline-title">Awaiting Cashier Review</div>
                        <div class="timeline-note">This order has not yet been reviewed.</div>
                    </div>
                </div>
                @else
                <div class="timeline-step {{ $order->approval_status === 'rejected' ? 'rejected' : 'done' }}">
                    <div class="timeline-dot"><i class="fas fa-{{ $order->approval_status === 'rejected' ? 'xmark' : 'check' }}"></i></div>
                    <div>
                        <div class="timeline-title">Order {{ $order->approval_status_label }}</div>
                        <div class="timeline-time">
                            {{ $order->reviewed_at?->format('M d, Y h:i A') }}
                            @if($order->reviewedBy)
                                &middot; by {{ $order->reviewedBy->staff?->full_name ?? $order->reviewedBy->email }}
                            @endif
                        </div>
                        @if($order->approval_status === 'rejected' && $order->rejection_reason)
                            <div class="timeline-note">Reason: {{ $order->rejection_reason }}</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div>
        {{-- Payment Information --}}
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-money-check-dollar"></i> Payment Information</h3></div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item"><div class="label">Required Down-Payment ({{ \App\Models\Order::DOWN_PAYMENT_PERCENT }}%)</div><div class="value">₱{{ number_format($order->required_down_payment, 2) }}</div></div>
                    @if($order->paymentProof)
                        <div class="info-item"><div class="label">Amount Submitted</div><div class="value">₱{{ number_format($order->paymentProof->amount, 2) }}</div></div>
                        <div class="info-item"><div class="label">Payment Method</div><div class="value">{{ $order->paymentProof->payment_method_label }}</div></div>
                        <div class="info-item"><div class="label">Reference Number</div><div class="value">{{ $order->paymentProof->reference_number ?: '—' }}</div></div>
                        <div class="info-item"><div class="label">Payment Date &amp; Time</div><div class="value">{{ ($order->paymentProof->paid_at ?? $order->paymentProof->created_at)->format('M d, Y h:i A') }}</div></div>
                    @else
                        <div class="info-item" style="grid-column:1/-1;color:var(--muted)">No proof of payment was submitted for this order.</div>
                    @endif
                </div>

                @if((float) $order->paymentProof?->amount < $order->required_down_payment)
                    <div class="instructions-box" style="margin-top:1rem">
                        <i class="fas fa-triangle-exclamation"></i> Amount submitted is less than the required down-payment. Review carefully before approving.
                    </div>
                @endif
            </div>
        </div>

        {{-- Proof of Payment --}}
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-image"></i> Proof of Payment</h3></div>
            <div class="card-body">
                @if($order->paymentProof?->proof_image_url)
                    <img src="{{ $order->paymentProof->proof_image_url }}" alt="Proof of payment" class="proof-thumb" onclick="openImageModal(this.src)">
                    <div class="proof-caption">Click image to enlarge</div>
                @else
                    <div style="text-align:center;color:var(--muted);padding:1.5rem 0"><i class="fas fa-image" style="font-size:1.8rem;opacity:.3;display:block;margin-bottom:.5rem"></i>No proof of payment uploaded.</div>
                @endif
            </div>
        </div>

        {{-- Cashier Actions --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check"></i> Cashier Actions</h3></div>
            <div class="card-body">
                @if($order->approval_status === 'pending')
                    <div style="display:flex;flex-direction:column;gap:.6rem">
                        <button type="button" class="btn btn-primary btn-block" onclick="confirmApproveDetail()">
                            <i class="fas fa-check"></i> Approve Order
                        </button>
                        <button type="button" class="btn btn-outline btn-block" onclick="openRejectModal({{ $order->id }})">
                            <i class="fas fa-xmark"></i> Reject Order
                        </button>
                    </div>
                @elseif($order->approval_status === 'approved')
                    <div class="reviewed-box approved">
                        <i class="fas fa-circle-check"></i>
                        <div>
                            This order was <strong>approved</strong>{{ $order->reviewedBy ? ' by ' . ($order->reviewedBy->staff?->full_name ?? $order->reviewedBy->email) : '' }}
                            on {{ $order->reviewed_at?->format('M d, Y h:i A') }} and forwarded to the Kitchen Display System.
                        </div>
                    </div>
                @elseif($order->approval_status === 'rejected')
                    <div class="reviewed-box rejected">
                        <i class="fas fa-circle-xmark"></i>
                        <div>
                            This order was <strong>rejected</strong>{{ $order->reviewedBy ? ' by ' . ($order->reviewedBy->staff?->full_name ?? $order->reviewedBy->email) : '' }}
                            on {{ $order->reviewed_at?->format('M d, Y h:i A') }}.
                            <div style="margin-top:.3rem">Reason: {{ $order->rejection_reason }}</div>
                        </div>
                    </div>
                @else
                    <div class="reviewed-box" style="background:rgba(107,114,128,0.08);border:1px solid rgba(107,114,128,0.25);color:#4B5563">
                        <i class="fas fa-ban"></i>
                        <div>This order has been cancelled and requires no further action.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Reject reason modal --}}
<div class="modal-overlay" id="rejectModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon"><i class="fas fa-ban"></i></div>
        <h3 class="modal-title">Reject Online Order?</h3>
        <p class="modal-desc">Please provide a reason. This will be shown to the customer.</p>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.6rem">
            <button type="button" class="reason-preset" data-reason="Invalid proof of payment" style="font-size:.74rem;padding:.3rem .65rem;border-radius:50px;border:1.5px solid var(--border);background:var(--bg);cursor:pointer;color:var(--muted)">Invalid proof of payment</button>
            <button type="button" class="reason-preset" data-reason="Incorrect payment amount" style="font-size:.74rem;padding:.3rem .65rem;border-radius:50px;border:1.5px solid var(--border);background:var(--bg);cursor:pointer;color:var(--muted)">Incorrect payment amount</button>
            <button type="button" class="reason-preset" data-reason="Unclear payment receipt" style="font-size:.74rem;padding:.3rem .65rem;border-radius:50px;border:1.5px solid var(--border);background:var(--bg);cursor:pointer;color:var(--muted)">Unclear payment receipt</button>
            <button type="button" class="reason-preset" data-reason="Payment not received" style="font-size:.74rem;padding:.3rem .65rem;border-radius:50px;border:1.5px solid var(--border);background:var(--bg);cursor:pointer;color:var(--muted)">Payment not received</button>
        </div>
        <textarea id="rejectReasonInput" placeholder="Reason for rejection..." style="width:100%;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:.65rem .85rem;font-size:.87rem;font-family:inherit;outline:none;resize:vertical;min-height:90px"></textarea>
        <div class="modal-actions" style="margin-top:1rem">
            <button type="button" class="btn-modal-cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="submitReject()">Reject Order</button>
        </div>
    </div>
</div>

{{-- Enlarge image modal --}}
<div class="img-modal-overlay" id="imageModal" onclick="closeImageModal(event)">
    <button type="button" class="img-modal-close" onclick="closeImageModal(event)"><i class="fas fa-xmark"></i></button>
    <img id="enlargedImage" src="" alt="Proof of payment (enlarged)">
</div>

@endsection

@section('scripts')
<script>
    const orderId = {{ $order->id }};
    const orderNumber = @json($order->order_number);

    function openImageModal(src) {
        document.getElementById('enlargedImage').src = src;
        document.getElementById('imageModal').classList.add('open');
    }
    function closeImageModal(e) {
        if (e.target.id === 'imageModal' || e.target.closest('.img-modal-close')) {
            document.getElementById('imageModal').classList.remove('open');
        }
    }

    document.querySelectorAll('.reason-preset').forEach(btn => {
        btn.addEventListener('click', () => { document.getElementById('rejectReasonInput').value = btn.dataset.reason; });
    });

    function openRejectModal() {
        document.getElementById('rejectReasonInput').value = '';
        document.getElementById('rejectModal').classList.add('open');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('open');
    }
    document.getElementById('rejectModal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeRejectModal();
    });

    async function submitReject() {
        const reason = document.getElementById('rejectReasonInput').value.trim();
        if (! reason) { showToast('Please provide a rejection reason.', 'error'); return; }

        try {
            const res = await fetch(`/cashier/online-orders/${orderId}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify({ reason }),
            });
            const data = await res.json();
            closeRejectModal();

            if (! res.ok) { showToast(data.message || 'Failed to reject order.', 'error'); return; }

            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } catch (e) {
            closeRejectModal();
            showToast('Failed to reject order.', 'error');
        }
    }

    function confirmApproveDetail() {
        openConfirmModal({
            title: 'Approve Online Order?',
            desc: `Approve Order #${orderNumber}? It will be forwarded to the Kitchen Display System immediately.`,
            confirmText: 'Approve Order',
            onConfirm: submitApproveDetail,
        });
    }

    async function submitApproveDetail() {
        try {
            const res = await fetch(`/cashier/online-orders/${orderId}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            });
            const data = await res.json();
            closeConfirmModal();

            if (! res.ok) { showToast(data.message || 'Failed to approve order.', 'error'); return; }

            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1200);
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to approve order.', 'error');
        }
    }
</script>
@endsection
