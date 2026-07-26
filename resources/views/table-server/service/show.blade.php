@extends('layouts.table-server')

@section('title', "Order #{$order->order_number}")

@section('styles')
<style>
    .sf-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: .75rem; }
    .sf-back { display: inline-flex; align-items: center; gap: .5rem; font-size: .86rem; font-weight: 600; color: var(--muted); }
    .sf-back:hover { color: var(--primary); }
    .sf-title-row { display: flex; align-items: center; gap: .8rem; }
    .sf-order-number { font-size: 1.5rem; font-weight: 800; color: var(--dark); }
    .sf-status-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .3rem .85rem; border-radius: 50px; font-size: .82rem; font-weight: 700; color: #fff;
    }

    .sf-layout { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.25rem; align-items: start; }
    @media (max-width: 1000px) { .sf-layout { grid-template-columns: 1fr; } }

    .sf-card { background: var(--white); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(17,24,39,0.05); padding: 1.4rem; margin-bottom: 1.25rem; }
    .sf-card-title { font-size: .95rem; font-weight: 800; color: var(--dark); margin: 0 0 1rem; display: flex; align-items: center; gap: .55rem; }
    .sf-card-title i { color: var(--primary); }

    .sf-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
    .sf-meta-item { background: rgba(17,24,39,0.03); border-radius: 10px; padding: .65rem .85rem; }
    .sf-meta-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 700; }
    .sf-meta-value { font-size: .95rem; color: var(--dark); font-weight: 700; margin-top: .2rem; }
    .sf-meta-value.table-highlight { color: var(--primary); font-size: 1.15rem; }

    .sf-item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .65rem 0; border-bottom: 1px dashed var(--border); gap: .6rem; }
    .sf-item-row:last-child { border-bottom: none; }
    .sf-item-name { font-weight: 600; color: var(--dark); font-size: .92rem; }
    .sf-item-note { font-size: .78rem; color: var(--accent); font-style: italic; margin-top: .15rem; }
    .sf-item-qty { font-weight: 700; color: var(--primary); flex-shrink: 0; }
    .sf-total-items { text-align: right; font-size: .85rem; color: var(--muted); font-weight: 600; margin-top: .7rem; padding-top: .7rem; border-top: 1px solid var(--border); }

    .sf-instructions-box { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px; padding: .8rem .95rem; margin-top: 1rem; font-size: .87rem; color: #92400E; }

    /* ── Timeline ── */
    .sf-timeline { display: flex; flex-direction: column; }
    .sf-tl-step { display: flex; gap: .9rem; position: relative; padding-bottom: 1.4rem; }
    .sf-tl-step:last-child { padding-bottom: 0; }
    .sf-tl-step:not(:last-child)::before {
        content: ''; position: absolute; left: 15px; top: 34px; bottom: 0; width: 2px; background: var(--border);
    }
    .sf-tl-step.done:not(:last-child)::before { background: var(--status-ready); }
    .sf-tl-dot {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; z-index: 1;
        display: flex; align-items: center; justify-content: center; font-size: .8rem;
        background: rgba(17,24,39,0.06); color: var(--muted); border: 2px solid var(--border);
    }
    .sf-tl-step.done .sf-tl-dot { background: var(--status-ready); color: #fff; border-color: var(--status-ready); }
    .sf-tl-step.current .sf-tl-dot { background: var(--primary); color: #fff; border-color: var(--primary); }
    .sf-tl-label { font-weight: 700; font-size: .88rem; color: var(--dark); margin-top: .3rem; }
    .sf-tl-time { font-size: .76rem; color: var(--muted); margin-top: .1rem; }

    .sf-action-btn { width: 100%; padding: .85rem; border-radius: 12px; border: none; font-family: inherit; font-size: .95rem; font-weight: 700; cursor: pointer; color: #fff; background: var(--status-ready); transition: filter .15s; }
    .sf-action-btn:hover { filter: brightness(1.08); }
    .sf-action-btn.packaging { background: var(--status-packaged); }
    .sf-back-btn { width: 100%; padding: .85rem; border-radius: 12px; border: 1.5px solid var(--border); font-family: inherit; font-size: .9rem; font-weight: 600; color: var(--dark); background: var(--white); cursor: pointer; margin-top: .6rem; display: block; text-align: center; }
    .sf-back-btn:hover { border-color: var(--primary); color: var(--primary); }

    .sf-done-box { text-align: center; padding: 1.5rem 1rem; background: rgba(37,99,235,0.06); border-radius: 12px; }
    .sf-done-box i { font-size: 2rem; color: var(--status-served); margin-bottom: .6rem; display: block; }
    .sf-done-box p { color: var(--dark); font-weight: 600; margin: 0; font-size: .92rem; }
</style>
@endsection

@section('content')

@php
    $isDineIn = $order->order_type === 'dine_in';
    $fulfillmentStatus = $isDineIn ? 'Served' : 'Packaged';
    $steps = [
        ['name' => 'Pending',    'label' => 'Order Placed', 'icon' => 'fa-clock',         'time' => $order->created_at],
        ['name' => 'Processing', 'label' => 'Preparing',    'icon' => 'fa-fire-burner',   'time' => null],
        ['name' => 'Ready',      'label' => 'Ready',        'icon' => 'fa-bell',          'time' => $order->ready_at],
        [
            'name'  => $fulfillmentStatus,
            'label' => $isDineIn ? 'Served' : 'Packaged',
            'icon'  => $isDineIn ? 'fa-utensils' : 'fa-box',
            'time'  => $isDineIn ? $order->served_at : $order->packaged_at,
        ],
    ];
    $statusOrder = ['Pending', 'Processing', 'Ready', $fulfillmentStatus];
    // fulfillment_status, not the raw status_name — the kitchen's own
    // "Completed" hand-off signal reads as "Ready" from here, and raw
    // status_name wouldn't be found in $statusOrder at all.
    $currentIdx = array_search($order->fulfillment_status, $statusOrder);
@endphp

<div class="sf-header">
    <a href="{{ route('table-server.service.index') }}" class="sf-back"><i class="fas fa-arrow-left"></i> Back to Ready Orders</a>
</div>

<div class="sf-title-row" style="margin-bottom:1.25rem">
    <div class="sf-order-number">#{{ $order->order_number }}</div>
    <span class="sf-status-badge" style="background:{{ $order->fulfillment_status_color }}">{{ $order->fulfillment_status_label }}</span>
</div>

<div class="sf-layout">
    <div>
        <div class="sf-card">
            <h3 class="sf-card-title"><i class="fas fa-receipt"></i> Order Information</h3>
            <div class="sf-meta-grid">
                <div class="sf-meta-item"><div class="sf-meta-label">Order Number</div><div class="sf-meta-value">#{{ $order->order_number }}</div></div>
                <div class="sf-meta-item"><div class="sf-meta-label">Order Type</div><div class="sf-meta-value">{{ $order->order_type_label }}</div></div>
                <div class="sf-meta-item">
                    <div class="sf-meta-label">Table Card Number</div>
                    <div class="sf-meta-value {{ $order->dineInOrder?->table_number ? 'table-highlight' : '' }}">
                        {{ $order->dineInOrder?->table_number ?? '— (Take-Out)' }}
                    </div>
                </div>
                <div class="sf-meta-item"><div class="sf-meta-label">Order Date</div><div class="sf-meta-value">{{ $order->created_at->format('M d, Y · h:i A') }}</div></div>
                <div class="sf-meta-item"><div class="sf-meta-label">Time Prepared</div><div class="sf-meta-value">{{ $order->ready_at?->format('h:i A') ?? '—' }}</div></div>
                <div class="sf-meta-item"><div class="sf-meta-label">Customer</div><div class="sf-meta-value">{{ $order->customer?->full_name ?? 'Walk-in' }}</div></div>
            </div>
        </div>

        <div class="sf-card">
            <h3 class="sf-card-title"><i class="fas fa-utensils"></i> Ordered Items</h3>
            @foreach($order->details as $detail)
            <div class="sf-item-row">
                <div>
                    <div class="sf-item-name">{{ $detail->item_name }}</div>
                    @if($detail->notes)
                    <div class="sf-item-note"><i class="fas fa-note-sticky"></i> {{ $detail->notes }}</div>
                    @endif
                </div>
                <div class="sf-item-qty">×{{ $detail->quantity }}</div>
            </div>
            @endforeach
            <div class="sf-total-items">Total Items: {{ $order->item_count }}</div>

            @if($order->special_instructions)
            <div class="sf-instructions-box"><i class="fas fa-circle-info"></i> {{ $order->special_instructions }}</div>
            @endif
        </div>
    </div>

    <div>
        <div class="sf-card">
            <h3 class="sf-card-title"><i class="fas fa-timeline"></i> Order Timeline</h3>
            <div class="sf-timeline">
                @foreach($steps as $idx => $step)
                @php
                    $isDone = $currentIdx !== false && $idx < $currentIdx;
                    $isCurrent = $currentIdx !== false && $idx === $currentIdx;
                @endphp
                <div class="sf-tl-step {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
                    <div class="sf-tl-dot"><i class="fas {{ $step['icon'] }}"></i></div>
                    <div>
                        <div class="sf-tl-label">{{ $step['label'] }}</div>
                        <div class="sf-tl-time">{{ $step['time']?->format('h:i A') ?? ($isDone || $isCurrent ? '—' : 'Pending') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="sf-card">
            @if($order->canBeFulfilled())
                <button type="button" class="sf-action-btn {{ $order->usesPackagingFlow() ? 'packaging' : '' }}" id="fulfillBtn">
                    <i class="fas {{ $order->usesPackagingFlow() ? 'fa-box' : 'fa-utensils' }}"></i> {{ $order->fulfillment_action_label }}
                </button>
            @else
                <div class="sf-done-box">
                    <i class="fas fa-circle-check"></i>
                    <p>
                        This order was already {{ strtolower($order->kitchen_status_label) }}
                        @if($order->servedBy)by {{ $order->servedBy->name ?: $order->servedBy->email }}@endif
                        @if($order->served_at) at {{ $order->served_at->format('h:i A') }}@elseif($order->packaged_at) at {{ $order->packaged_at->format('h:i A') }}@endif.
                    </p>
                </div>
            @endif
            <a href="{{ route('table-server.service.index') }}" class="sf-back-btn"><i class="fas fa-arrow-left"></i> Back to Ready Orders</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const fulfillBtn = document.getElementById('fulfillBtn');
    if (fulfillBtn) {
        const usesPackaging = @json($order->usesPackagingFlow());
        const actionLabel = @json($order->fulfillment_action_label);
        const orderId = @json($order->id);
        const orderNumber = @json($order->order_number);
        const endpoint = `{{ url('/table-server/service/' . $order->id) }}/${usesPackaging ? 'package' : 'serve'}`;
        const redirectUrl = "{{ route('table-server.service.index') }}";

        fulfillBtn.addEventListener('click', () => {
            openConfirmModal({
                title: `${actionLabel}?`,
                desc: usesPackaging
                    ? `Confirm that Order #${orderNumber} has been packaged and is ready for customer pickup?`
                    : `Confirm that this order has been served to the customer?`,
                confirmText: actionLabel,
                onConfirm: submitFulfill,
            });
        });

        async function submitFulfill() {
            fulfillBtn.disabled = true;

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });

                closeConfirmModal();

                if (res.status === 419) {
                    const refreshed = await refreshCsrfToken();
                    showToast(refreshed ? 'Session refreshed — please try again.' : 'Your session has expired. Please log back in.', refreshed ? 'info' : 'error');
                    fulfillBtn.disabled = false;
                    return;
                }

                const data = await res.json();

                if (!res.ok) {
                    showToast(data.message || 'Failed to update this order.', 'error');
                    fulfillBtn.disabled = false;
                    return;
                }

                showToast(data.message, 'success');
                setTimeout(() => { window.location.href = redirectUrl; }, 900);
            } catch (e) {
                closeConfirmModal();
                showToast('Failed to update this order.', 'error');
                fulfillBtn.disabled = false;
            }
        }
    }
</script>
@endsection
