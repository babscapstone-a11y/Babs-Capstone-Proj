@extends('layouts.customer-app')
@section('title', 'Order ' . ($order->order_number ?? '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT)) . " – Bab's Resto")

@section('styles')
<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both; }
.fade-up-2 { animation: fadeUp .4s .08s cubic-bezier(.22,1,.36,1) both; }

.order-wrap { max-width: 900px; margin: 0 auto; }

/* Back / actions */
.top-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
}
.back-link {
    display: inline-flex; align-items: center; gap: .45rem;
    font-size: .84rem; font-weight: 600; color: var(--primary);
    cursor: pointer; transition: gap .15s;
}
.back-link:hover { gap: .65rem; }

/* Cards */
.card {
    background: var(--white); border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    overflow: hidden; margin-bottom: 1.25rem;
}
.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: #FAFBFC;
    display: flex; align-items: center; justify-content: space-between;
}
.card-header h2 {
    font-size: .95rem; font-weight: 700; color: var(--dark);
    display: flex; align-items: center; gap: .55rem;
}
.card-header h2 i { color: var(--primary); }
.card-body { padding: 1.4rem 1.5rem; }

/* Order hero */
.order-hero {
    background: linear-gradient(135deg, var(--dark), #1F2937);
    color: #fff; padding: 1.5rem; border-radius: 18px;
    margin-bottom: 1.25rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
}
.oh-left .oh-number { font-size: 1.25rem; font-weight: 900; }
.oh-left .oh-date { font-size: .8rem; color: rgba(255,255,255,.55); margin-top: .2rem; }
.oh-right { text-align: right; }
.oh-total { font-size: 1.65rem; font-weight: 900; color: var(--accent); line-height: 1; }
.oh-total-label { font-size: .72rem; color: rgba(255,255,255,.5); margin-top: .1rem; }

/* Badges */
.badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .75rem; border-radius: 20px;
    font-size: .73rem; font-weight: 700; white-space: nowrap;
}
.badge-dot::before {
    content: ''; display: block;
    width: 6px; height: 6px; border-radius: 50%;
    background: currentColor;
}
.badge-paid     { background: #DCFCE7; color: #15803D; }
.badge-pending  { background: #FEF3C7; color: #92400E; }
.badge-failed   { background: #FEE2E2; color: #B91C1C; }
.badge-refunded { background: #EDE9FE; color: #6D28D9; }

.order-type-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .75rem; font-weight: 600; padding: .22rem .7rem;
    border-radius: 20px;
}
.ot-dine   { background: rgba(29,78,216,.15); color: #93C5FD; }
.ot-take   { background: rgba(14,116,144,.15); color: #67E8F9; }
.ot-online { background: rgba(194,65,12,.15); color: #FCA5A5; }

/* Info row */
.info-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 540px) { .info-grid { grid-template-columns: 1fr; } }
.info-item { display: flex; flex-direction: column; gap: .2rem; }
.info-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); }
.info-value { font-size: .9rem; font-weight: 600; color: var(--dark); }

/* Status timeline */
.timeline { display: flex; gap: 0; overflow-x: auto; padding-bottom: .5rem; }
.tl-step {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; min-width: 80px; position: relative;
}
.tl-step:not(:last-child)::after {
    content: ''; position: absolute;
    top: 18px; left: calc(50% + 18px); right: calc(-50% + 18px);
    height: 2px; background: var(--border); z-index: 0;
}
.tl-step.done:not(:last-child)::after { background: var(--primary); }
.tl-dot {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--bg); border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; color: var(--muted);
    z-index: 1; position: relative; flex-shrink: 0;
    transition: all .3s;
}
.tl-step.done .tl-dot {
    background: var(--primary); border-color: var(--primary); color: #fff;
    box-shadow: 0 0 0 4px rgba(220,38,38,.15);
}
.tl-step.current .tl-dot {
    background: var(--accent); border-color: var(--accent); color: #fff;
    box-shadow: 0 0 0 4px rgba(245,158,11,.2);
    animation: pulse 1.8s infinite;
}
@keyframes pulse {
    0%,100% { box-shadow: 0 0 0 4px rgba(245,158,11,.2); }
    50%      { box-shadow: 0 0 0 8px rgba(245,158,11,.05); }
}
.tl-step.cancelled .tl-dot { background: #FEE2E2; border-color: #DC2626; color: #DC2626; }
.tl-label {
    font-size: .68rem; font-weight: 600; color: var(--muted);
    text-align: center; margin-top: .45rem; line-height: 1.3;
}
.tl-step.done    .tl-label { color: var(--primary); }
.tl-step.current .tl-label { color: var(--dark); font-weight: 700; }
.tl-step.cancelled .tl-label { color: #DC2626; }

/* Order items table */
.items-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.items-table th {
    padding: .65rem 1rem; text-align: left;
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    color: var(--muted); background: #F8FAFC;
    border-bottom: 1px solid var(--border);
}
.items-table td { padding: .85rem 1rem; border-bottom: 1px solid #F5F5F5; vertical-align: middle; }
.items-table tr:last-child td { border-bottom: none; }
.item-name { font-weight: 600; color: var(--dark); }
.item-sub  { font-size: .74rem; color: var(--muted); margin-top: .1rem; }

/* Summary section */
.order-summary { border-top: 1px solid var(--border); padding: 1rem 1.5rem; }
.summary-row { display: flex; justify-content: space-between; padding: .4rem 0; font-size: .85rem; }
.summary-row.total {
    border-top: 2px solid var(--border); margin-top: .4rem; padding-top: .85rem;
    font-size: 1.05rem; font-weight: 800; color: var(--dark);
}

/* Cancellation banner */
.cancel-banner {
    background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 12px;
    padding: 1rem 1.25rem; margin-bottom: 1.25rem;
    display: flex; gap: .75rem; align-items: flex-start;
    font-size: .84rem; color: #B91C1C;
}
.cancel-banner i { font-size: 1.1rem; margin-top: .05rem; flex-shrink: 0; }

/* Delivery info */
.delivery-box {
    background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex; flex-direction: column; gap: .6rem;
    font-size: .84rem;
}
.delivery-box .db-row { display: flex; align-items: flex-start; gap: .65rem; }
.delivery-box .db-row i { color: #16A34A; margin-top: .1rem; flex-shrink: 0; }
.delivery-box .db-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #15803D; margin-bottom: .1rem; }
.delivery-box .db-val   { font-weight: 600; color: var(--dark); }

.instructions-box {
    background: #FAFBFC; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 1rem 1.25rem; font-size: .85rem; color: var(--text);
    display: flex; gap: .65rem;
}
.instructions-box i { color: var(--muted); margin-top: .1rem; flex-shrink: 0; }

/* Print button */
.btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .58rem 1.15rem; border-radius: 10px;
    font-size: .83rem; font-weight: 600; font-family: inherit;
    cursor: pointer; border: none; transition: all .18s; text-decoration: none;
}
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-dk); }
.btn-outline { background: var(--white); border: 1.5px solid var(--border); color: var(--text); }
.btn-outline:hover { border-color: var(--primary); color: var(--primary); }

@media print {
    .app-nav, .back-link, .btn, #toastContainer { display: none !important; }
    body { padding: 0; }
    .card { box-shadow: none; border: 1px solid #ccc; border-radius: 6px; }
}
</style>
@endsection

@section('content')
<div class="page-wrap order-wrap">

    {{-- Top bar --}}
    <div class="top-bar fade-up">
        <a href="{{ route('account.index', ['#orders']) }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <div style="display:flex;gap:.6rem">
            <button class="btn btn-outline" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    {{-- Online pre-order: awaiting cashier verification --}}
    @if($order->isOnline() && $order->approval_status === 'pending')
    <div class="cancel-banner fade-up" style="background:#FFFBEB;border-color:#FDE68A;color:#92400E">
        <i class="fas fa-hourglass-half"></i>
        <div>
            <strong>Awaiting Payment Verification</strong><br>
            Our staff is reviewing your down-payment. You'll be notified as soon as it's verified.
        </div>
    </div>
    @endif

    {{-- Online pre-order: rejected --}}
    @if($order->isOnline() && $order->approval_status === 'rejected')
    <div class="cancel-banner fade-up">
        <i class="fas fa-circle-xmark"></i>
        <div>
            <strong>Order Rejected</strong><br>
            @if($order->rejection_reason)
                Reason: {{ $order->rejection_reason }}
            @else
                Your down-payment could not be verified.
            @endif
            @if($order->reviewed_at)
                <div style="font-size:.76rem;margin-top:.2rem;color:#B91C1C99">Reviewed on {{ $order->reviewed_at->format('F d, Y h:i A') }}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Cancellation banner --}}
    @if($order->isCancelled())
    <div class="cancel-banner fade-up">
        <i class="fas fa-circle-xmark"></i>
        <div>
            <strong>Order Cancelled</strong><br>
            @if($order->cancellation_reason)
                Reason: {{ $order->cancellation_reason }}
            @else
                This order has been cancelled.
            @endif
            @if($order->cancelled_at)
                <div style="font-size:.76rem;margin-top:.2rem;color:#B91C1C99">Cancelled on {{ $order->cancelled_at->format('F d, Y h:i A') }}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Order hero card --}}
    <div class="order-hero fade-up">
        <div class="oh-left">
            <div style="font-size:.7rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:.35rem">ORDER</div>
            <div class="oh-number">{{ $order->order_number ?? '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="oh-date">{{ $order->created_at->format('F d, Y \a\t h:i A') }}</div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem">
                @php
                    $typeClass = match($order->order_type) {
                        'dine_in' => 'ot-dine', 'takeout' => 'ot-take', 'online' => 'ot-online', default => 'ot-dine'
                    };
                @endphp
                <span class="order-type-chip {{ $typeClass }}">
                    <i class="fas {{ $order->order_type_icon }}"></i> {{ $order->order_type_label }}
                </span>
                <span class="badge {{ $order->payment_status_class }} badge-dot">{{ $order->payment_status_label }}</span>
            </div>
        </div>
        <div class="oh-right">
            <div class="oh-total">₱{{ number_format($order->total_amount, 2) }}</div>
            <div class="oh-total-label">Grand Total</div>
            <div style="margin-top:.75rem">
                <span class="badge" style="background:{{ $order->status_color }}25;color:{{ $order->status_color }};font-size:.78rem;padding:.3rem .9rem" id="heroStatusBadge">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $order->status_color }};margin-right:.25rem"></span>
                    {{ $order->customer_status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Status timeline --}}
    @php
        $allStatuses   = ['Pending', 'Processing', 'Ready', 'Completed'];
        $statusLabels  = ['Pending' => 'Order Received', 'Processing' => 'Preparing', 'Ready' => ($order->order_type === 'dine_in' ? 'Ready for Serving' : 'Ready for Pickup'), 'Completed' => 'Completed'];
        $cancelledRaw  = $order->isCancelled();
        $currentStatus = $order->status_name;
        $statusIcons   = ['Pending' => 'fa-clock', 'Processing' => 'fa-fire-burner', 'Ready' => 'fa-bell', 'Completed' => 'fa-circle-check'];
        $currentIdx    = array_search($currentStatus, $allStatuses);
        $awaitingApproval = $order->isOnline() && in_array($order->approval_status, ['pending', 'rejected', 'cancelled'], true);
    @endphp
    <div class="card fade-up">
        <div class="card-header">
            <h2><i class="fas fa-timeline"></i> Order Status</h2>
            @if(! $cancelledRaw && ! $order->isCompleted())
            <span style="font-size:.72rem;color:var(--muted);display:flex;align-items:center;gap:.3rem"><i class="fas fa-rotate" id="refreshSpinner"></i> Auto-updating</span>
            @endif
        </div>
        <div class="card-body">
            @if($cancelledRaw)
                <div style="display:flex;align-items:center;gap:1.25rem" id="timelineContainer">
                    <div class="tl-dot tl-step cancelled" style="width:40px;height:40px;font-size:.9rem;border-radius:50%;background:#FEE2E2;border:2px solid #DC2626;display:flex;align-items:center;justify-content:center;color:#DC2626;flex-shrink:0">
                        <i class="fas fa-xmark"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;color:#DC2626">Order Cancelled</div>
                        @if($order->cancellation_reason)
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">{{ $order->cancellation_reason }}</div>
                        @endif
                    </div>
                </div>
            @elseif($awaitingApproval)
                @php
                    $isRejected = $order->approval_status === 'rejected';
                @endphp
                <div style="display:flex;align-items:center;gap:1.25rem" id="timelineContainer">
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $isRejected ? '#FEE2E2' : '#FEF3C7' }};border:2px solid {{ $isRejected ? '#DC2626' : '#F59E0B' }};display:flex;align-items:center;justify-content:center;color:{{ $isRejected ? '#DC2626' : '#B45309' }};flex-shrink:0">
                        <i class="fas {{ $isRejected ? 'fa-xmark' : 'fa-hourglass-half' }}"></i>
                    </div>
                    <div>
                        <div style="font-weight:700">{{ $order->customer_status_label }}</div>
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem">
                            @if($order->approval_status === 'pending')
                                Your order will move to the kitchen once our staff verifies your down-payment.
                            @else
                                This order will not be prepared by the kitchen.
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="timeline" id="timelineContainer">
                    @foreach($allStatuses as $idx => $sName)
                    @php
                        $isDone    = $currentIdx !== false && $idx < $currentIdx;
                        $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                        $icon      = $statusIcons[$sName] ?? 'fa-circle';
                        $stepClass = $isDone ? 'done' : ($isCurrent ? 'current' : '');
                    @endphp
                    <div class="tl-step {{ $stepClass }}">
                        <div class="tl-dot">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div class="tl-label">{{ $statusLabels[$sName] ?? $sName }}</div>
                        @if($sName === 'Pending')
                        <div style="font-size:.66rem;color:var(--muted);margin-top:.15rem">{{ $order->created_at->format('h:i A') }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if($order->estimated_completion)
                <div style="margin-top:1.1rem;padding-top:1rem;border-top:1px solid var(--border);font-size:.83rem;color:var(--muted);display:flex;align-items:center;gap:.45rem">
                    <i class="fas fa-clock" style="color:var(--accent)"></i>
                    Estimated completion: <strong style="color:var(--dark)">{{ $order->estimated_completion->format('h:i A') }}</strong>
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Two-column layout for items + sidebar --}}
    <div style="display:grid;grid-template-columns:1fr 280px;gap:1.25rem;align-items:start">

    {{-- Order Items --}}
    <div class="fade-up-2">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list-ul"></i> Ordered Items</h2>
                <span style="font-size:.76rem;color:var(--muted)">{{ $order->item_count }} {{ Str::plural('item', $order->item_count) }}</span>
            </div>
            @if($order->details->isEmpty())
                <div style="padding:2rem;text-align:center;color:var(--muted);font-size:.85rem">
                    <i class="fas fa-utensils" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
                    No items recorded for this order.
                </div>
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
                                <td>
                                    <div class="item-name">{{ $detail->item_name }}</div>
                                    @if($detail->menuItem)
                                    <div class="item-sub">{{ $detail->menuItem->category?->category_name }}</div>
                                    @endif
                                    @if($detail->notes)
                                    <div class="item-sub"><i class="fas fa-note-sticky"></i> {{ $detail->notes }}</div>
                                    @endif
                                </td>
                                <td style="text-align:center;font-weight:600">{{ $detail->quantity }}</td>
                                <td style="text-align:right;color:var(--muted)">₱{{ number_format($detail->price, 2) }}</td>
                                <td style="text-align:right;font-weight:700;color:var(--dark)">₱{{ number_format($detail->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="order-summary">
                    <div class="summary-row"><span style="color:var(--muted)">Subtotal</span><span>₱{{ number_format($order->details->sum('subtotal'), 2) }}</span></div>
                    <div class="summary-row total"><span>Grand Total</span><span style="color:var(--primary)">₱{{ number_format($order->total_amount, 2) }}</span></div>
                </div>
            @endif
        </div>

        {{-- Special Instructions --}}
        @if($order->special_instructions)
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-note-sticky"></i> Special Instructions</h2></div>
            <div class="card-body">
                <div class="instructions-box">
                    <i class="fas fa-quote-left"></i>
                    <span>{{ $order->special_instructions }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="fade-up-2">
        {{-- Order Summary --}}
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-info-circle"></i> Order Details</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.85rem">
                <div class="info-item">
                    <span class="info-label">Order Number</span>
                    <span class="info-value">{{ $order->order_number ?? '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Date</span>
                    <span class="info-value" style="font-size:.84rem">{{ $order->created_at->format('F d, Y') }}</span>
                    <span style="font-size:.75rem;color:var(--muted)">{{ $order->created_at->format('h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Type</span>
                    <span class="info-value">{{ $order->order_type_label }}</span>
                </div>
                @if($order->order_type === 'dine_in' && $order->dineInOrder?->table_number)
                <div class="info-item">
                    <span class="info-label">Table Number</span>
                    <span class="info-value">{{ $order->dineInOrder->table_number }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Order Status</span>
                    <span class="badge" style="background:{{ $order->status_color }}1a;color:{{ $order->status_color }};width:fit-content" id="statusBadge">
                        {{ $order->customer_status_label }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">{{ $order->payment_method_label }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status</span>
                    <span class="badge {{ $order->payment_status_class }}" style="width:fit-content">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value" style="font-size:1.2rem;color:var(--primary)">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Delivery info --}}
        @if($order->isDelivery() && $order->onlineOrder)
        <div class="card">
            <div class="card-header"><h2><i class="fas fa-motorcycle"></i> Delivery Information</h2></div>
            <div class="card-body">
                <div class="delivery-box">
                    <div class="db-row">
                        <i class="fas fa-location-dot"></i>
                        <div>
                            <div class="db-label">Delivery Address</div>
                            <div class="db-val">{{ $order->onlineOrder->delivery_address }}</div>
                        </div>
                    </div>
                    <div class="db-row">
                        <i class="fas fa-phone"></i>
                        <div>
                            <div class="db-label">Contact Number</div>
                            <div class="db-val">{{ $order->onlineOrder->contact_number }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;flex-direction:column;gap:.6rem">
            <a href="{{ route('account.index', ['#orders']) }}" class="btn btn-outline" style="justify-content:center">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="justify-content:center">
                <i class="fas fa-utensils"></i> Order Again
            </a>
        </div>
    </div>

    </div>{{-- end grid --}}

</div>
@endsection

@section('layout-styles')
<style>
@media (max-width: 700px) {
    div[style*="grid-template-columns: 1fr 280px"] {
        display: block !important;
    }
    div[style*="grid-template-columns: 1fr 280px"] > * {
        margin-bottom: 1.25rem;
    }
}
#refreshSpinner { animation: spin 2s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@if(! $order->isCancelled() && ! $order->isCompleted() && $order->approval_status !== 'rejected')
@section('scripts')
<script>
const orderStatusUrl = "{{ route('account.orders.status', $order) }}";
let lastStatus = @json($order->status_name);
let lastApprovalStatus = @json($order->approval_status);

async function pollOrderStatus() {
    try {
        const res = await fetch(orderStatusUrl, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (data.status_name !== lastStatus || data.approval_status !== lastApprovalStatus || data.is_cancelled || data.is_completed) {
            window.location.reload();
            return;
        }
    } catch (e) { /* silent — retry on next interval */ }
}

setInterval(pollOrderStatus, 15000);
</script>
@endsection
@endif
