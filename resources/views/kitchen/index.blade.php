@extends('layouts.kitchen')

@section('title', 'Kitchen Display')

@section('styles')
<style>
    /* ── Summary cards ───────────────────────────────────────── */
    .summary-row {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .summary-card {
        background: var(--white); border-radius: 14px; padding: 1.1rem 1.3rem;
        border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(17,24,39,0.05);
        display: flex; align-items: center; gap: .9rem;
    }
    .summary-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .summary-icon.pending    { background: rgba(107,114,128,0.12); color: var(--status-pending); }
    .summary-icon.preparing  { background: rgba(245,158,11,0.14); color: var(--status-preparing); }
    .summary-icon.ready      { background: rgba(22,163,74,0.14);  color: var(--status-ready); }
    .summary-icon.completed  { background: rgba(37,99,235,0.14); color: var(--status-completed); }
    .summary-count { font-size: 1.7rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .summary-label  { font-size: .78rem; color: var(--muted); font-weight: 600; margin-top: .2rem; }

    /* ── Kanban board ────────────────────────────────────────── */
    .kanban-board {
        display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.1rem;
        align-items: start;
    }
    .kanban-col {
        background: rgba(17,24,39,0.03); border-radius: 16px;
        padding: .9rem; height: calc(100vh - 235px); min-height: 340px;
        display: flex; flex-direction: column;
    }
    .kanban-col-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .3rem .3rem .8rem; font-weight: 700; font-size: .95rem; flex-shrink: 0;
    }
    .kanban-col-header .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: .5rem; }
    .kanban-count {
        background: var(--white); border-radius: 50px; padding: .12rem .6rem;
        font-size: .78rem; font-weight: 700; color: var(--muted);
        border: 1px solid var(--border);
    }
    .kanban-cards {
        display: flex; flex-direction: column; gap: .55rem;
        flex: 1 1 auto; min-height: 0; overflow-y: auto; padding-right: .2rem;
    }
    .kanban-cards::-webkit-scrollbar { width: 6px; }
    .kanban-cards::-webkit-scrollbar-track { background: transparent; }
    .kanban-cards::-webkit-scrollbar-thumb { background: rgba(17,24,39,0.15); border-radius: 10px; }
    .kanban-empty { text-align: center; color: var(--muted); font-size: .85rem; padding: 2rem 1rem; }

    /* ── Ticket card ─────────────────────────────────────────── */
    .ticket-card {
        background: var(--white); border-radius: 12px; padding: .7rem .8rem;
        border-left: 4px solid var(--status-pending); flex-shrink: 0;
        box-shadow: 0 2px 10px rgba(17,24,39,0.06);
        cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
    }
    .ticket-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(17,24,39,0.1); }
    .ticket-card.status-pending   { border-left-color: var(--status-pending); }
    .ticket-card.status-preparing { border-left-color: var(--status-preparing); }
    .ticket-card.status-ready     { border-left-color: var(--status-ready); }
    .ticket-card.status-completed { border-left-color: var(--status-completed); opacity: .82; }

    .ticket-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
    .ticket-order-number { font-size: 1.02rem; font-weight: 800; color: var(--dark); }
    .ticket-elapsed {
        font-size: .74rem; font-weight: 700; padding: .14rem .45rem; border-radius: 50px;
        background: rgba(17,24,39,0.06); color: var(--muted); white-space: nowrap;
    }
    .ticket-elapsed.urgent { background: rgba(220,38,38,0.12); color: var(--primary); }
    .ticket-customer { font-size: .86rem; font-weight: 600; color: var(--dark); margin-top: .25rem; }
    .ticket-meta {
        display: flex; align-items: center; gap: .4rem; flex-wrap: wrap;
        font-size: .72rem; color: var(--muted); margin-top: .25rem;
    }
    .ticket-chip {
        display: inline-flex; align-items: center; gap: .25rem;
        background: rgba(17,24,39,0.05); border-radius: 50px; padding: .12rem .45rem;
        font-weight: 600;
    }
    .ticket-items { margin-top: .5rem; border-top: 1px dashed var(--border); padding-top: .45rem; }
    .ticket-item-row { display: flex; justify-content: space-between; gap: .5rem; font-size: .8rem; margin-bottom: .2rem; }
    .ticket-item-name { font-weight: 600; color: var(--dark); }
    .ticket-item-qty { font-weight: 700; color: var(--primary); flex-shrink: 0; }
    .ticket-item-note { font-size: .72rem; color: var(--accent); font-style: italic; margin-top: -.1rem; margin-bottom: .2rem; }
    .ticket-more-items { font-size: .72rem; color: var(--muted); font-style: italic; }

    .ticket-action-btn {
        width: 100%; margin-top: .6rem; padding: .5rem; border-radius: 8px;
        border: none; font-family: inherit; font-size: .8rem; font-weight: 700;
        cursor: pointer; color: var(--white); transition: filter .15s;
    }
    .ticket-action-btn:hover { filter: brightness(1.08); }
    .ticket-action-btn.status-pending   { background: var(--status-pending); }
    .ticket-action-btn.status-preparing { background: var(--status-preparing); }
    .ticket-action-btn.status-ready     { background: var(--status-ready); }

    .ticket-revert-btn {
        background: rgba(220,38,38,0.08); border: none; color: var(--muted);
        width: 24px; height: 24px; border-radius: 7px; cursor: pointer; font-size: .72rem; flex-shrink: 0;
    }
    .ticket-revert-btn:hover { background: rgba(220,38,38,0.15); color: var(--primary); }
    .ticket-revert-btn-wide {
        width: 100%; margin-top: .5rem; padding: .6rem; border-radius: 10px;
        border: 1px solid rgba(220,38,38,0.3); background: rgba(220,38,38,0.06);
        color: var(--primary); font-family: inherit; font-weight: 700; font-size: .85rem; cursor: pointer;
    }
    .ticket-revert-btn-wide:hover { background: rgba(220,38,38,0.12); }
    .kanban-col-sublabel { font-size: .68rem; color: var(--muted); font-weight: 600; margin: -.5rem .3rem .5rem; flex-shrink: 0; }

    .ticket-extend-btn {
        background: rgba(245,158,11,0.12); border: none; color: var(--accent);
        width: 24px; height: 24px; border-radius: 7px; cursor: pointer; font-size: .72rem; flex-shrink: 0;
    }
    .ticket-extend-btn:hover { background: rgba(245,158,11,0.22); }
    .ticket-extend-btn-wide {
        width: 100%; margin-top: .5rem; padding: .6rem; border-radius: 10px;
        border: 1px solid rgba(245,158,11,0.35); background: rgba(245,158,11,0.1);
        color: #92400E; font-family: inherit; font-weight: 700; font-size: .85rem; cursor: pointer;
    }
    .ticket-extend-btn-wide:hover { background: rgba(245,158,11,0.18); }
    .ticket-extend-btn-wide:disabled { opacity: .5; cursor: not-allowed; }
    .ticket-chip.extended { background: rgba(245,158,11,0.14); color: #92400E; }

    /* ── Detail modal ────────────────────────────────────────── */
    .detail-modal-box {
        background: var(--white); border-radius: 20px;
        padding: 1.75rem; max-width: 560px; width: 100%; max-height: 85vh; overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.2);
        animation: modalIn .3s cubic-bezier(.22,.68,0,1.2) both;
    }
    .detail-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
    .detail-order-number { font-size: 1.4rem; font-weight: 800; color: var(--dark); }
    .detail-status-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .25rem .75rem; border-radius: 50px; font-size: .8rem; font-weight: 700; color: var(--white);
    }
    .detail-close-btn {
        background: rgba(17,24,39,0.06); border: none; width: 32px; height: 32px; border-radius: 8px;
        cursor: pointer; color: var(--muted); font-size: .85rem;
    }
    .detail-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; margin-bottom: 1.1rem; }
    .detail-meta-item { background: rgba(17,24,39,0.03); border-radius: 10px; padding: .6rem .8rem; }
    .detail-meta-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 700; }
    .detail-meta-value { font-size: .92rem; color: var(--dark); font-weight: 600; margin-top: .15rem; }
    .detail-section-label {
        font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        color: var(--muted); border-bottom: 1px solid var(--border); padding-bottom: .4rem; margin-bottom: .6rem;
    }
    .detail-item-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px dashed var(--border); }
    .detail-item-row:last-child { border-bottom: none; }
    .detail-item-note { font-size: .8rem; color: var(--accent); font-style: italic; margin-top: .15rem; }
    .detail-instructions-box {
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);
        border-radius: 10px; padding: .75rem .9rem; margin-top: 1rem; font-size: .88rem; color: #92400E;
    }

    @media (max-width: 1300px) { .kanban-board { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 1100px) {
        .summary-row { grid-template-columns: repeat(2, 1fr); }
        .kanban-col { height: calc(100vh - 330px); }
    }
    @media (max-width: 900px) { .kanban-board { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) {
        .summary-row, .kanban-board { grid-template-columns: 1fr; }
        .kanban-col { height: calc(100vh - 380px); min-height: 280px; }
    }
</style>
@endsection

@section('content')

<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon pending"><i class="fas fa-inbox"></i></div>
        <div><div class="summary-count" id="countPending">0</div><div class="summary-label">Pending Orders</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon preparing"><i class="fas fa-fire-burner"></i></div>
        <div><div class="summary-count" id="countPreparing">0</div><div class="summary-label">Preparing</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon ready"><i class="fas fa-bell-concierge"></i></div>
        <div><div class="summary-count" id="countReady">0</div><div class="summary-label">Ready</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon completed"><i class="fas fa-circle-check"></i></div>
        <div><div class="summary-count" id="countCompleted">0</div><div class="summary-label">Completed Today</div></div>
    </div>
</div>

<div class="kanban-board">
    <div class="kanban-col">
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-pending)"></span>Pending · Online</span><span class="kanban-count" id="colCountPendingOnline">0</span></div>
        <div class="kanban-col-sublabel"><i class="fas fa-calendar-clock"></i> Sorted by pickup date &amp; time</div>
        <div class="kanban-cards" id="col-PendingOnline"></div>
    </div>
    <div class="kanban-col">
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-pending)"></span>Pending · Walk-in</span><span class="kanban-count" id="colCountPendingWalkin">0</span></div>
        <div class="kanban-cards" id="col-PendingWalkin"></div>
    </div>
    <div class="kanban-col">
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-preparing)"></span>Preparing</span><span class="kanban-count" id="colCountProcessing">0</span></div>
        <div class="kanban-cards" id="col-Processing"></div>
    </div>
    <div class="kanban-col">
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-ready)"></span>Ready</span><span class="kanban-count" id="colCountReady">0</span></div>
        <div class="kanban-cards" id="col-Ready"></div>
    </div>
    <div class="kanban-col">
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-completed)"></span>Completed</span><span class="kanban-count" id="colCountCompleted">0</span></div>
        <div class="kanban-cards" id="col-Completed"></div>
    </div>
</div>

<!-- ── Order detail modal ── -->
<div class="modal-overlay" id="orderDetailModal" role="dialog" aria-modal="true">
    <div class="detail-modal-box" id="detailModalContent"></div>
</div>

@endsection

@section('scripts')
<script>
    const STATUS_COLORS = {
        Pending:    getComputedStyle(document.documentElement).getPropertyValue('--status-pending').trim(),
        Processing: getComputedStyle(document.documentElement).getPropertyValue('--status-preparing').trim(),
        Ready:      getComputedStyle(document.documentElement).getPropertyValue('--status-ready').trim(),
        Completed:  getComputedStyle(document.documentElement).getPropertyValue('--status-completed').trim(),
    };
    const STATUS_CSS_CLASS = { Pending: 'status-pending', Processing: 'status-preparing', Ready: 'status-ready', Completed: 'status-completed' };
    const COLUMN_IDS = { PendingOnline: 'col-PendingOnline', PendingWalkin: 'col-PendingWalkin', Processing: 'col-Processing', Ready: 'col-Ready', Completed: 'col-Completed' };

    let ordersCache = {};

    function formatTime(iso) {
        return new Date(iso).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function formatPickup(iso) {
        return new Date(iso).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function elapsedText(iso) {
        const diffMs = Date.now() - new Date(iso).getTime();
        const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        return m > 0 ? `${m}m ${s}s` : `${s}s`;
    }

    function isUrgent(iso) {
        return (Date.now() - new Date(iso).getTime()) > 20 * 60 * 1000; // 20+ min old
    }

    function renderCard(order) {
        const cssClass = STATUS_CSS_CLASS[order.status] || 'status-pending';
        const itemsHtml = order.items.slice(0, 4).map(item => `
            <div class="ticket-item-row">
                <span class="ticket-item-name">${item.name}</span>
                <span class="ticket-item-qty">×${item.quantity}</span>
            </div>
            ${item.notes ? `<div class="ticket-item-note"><i class="fas fa-note-sticky"></i> ${item.notes}</div>` : ''}
        `).join('');
        const moreCount = order.items.length - 4;

        let actionBtn = '';
        if (order.next_action) {
            actionBtn = `<button type="button" class="ticket-action-btn ${cssClass}" onclick="event.stopPropagation(); confirmStatusChange(${order.id})">${order.next_action}</button>`;
        }

        const revertBtn = order.can_revert
            ? `<button type="button" class="ticket-revert-btn" title="Revert to ${order.previous_status_label}" onclick="event.stopPropagation(); confirmRevert(${order.id})"><i class="fas fa-rotate-left"></i></button>`
            : '';

        const extendBtn = order.can_extend_prep
            ? `<button type="button" class="ticket-extend-btn" title="Extend prep time by 10 min" onclick="event.stopPropagation(); confirmExtendPrep(${order.id})"><i class="fas fa-clock-rotate-left"></i></button>`
            : '';

        return `
            <div class="ticket-card ${cssClass}" onclick="openDetailModal(${order.id})">
                <div class="ticket-top">
                    <div class="ticket-order-number">#${order.order_number}</div>
                    <div style="display:flex;align-items:center;gap:.4rem">
                        ${extendBtn}
                        ${revertBtn}
                        <div class="ticket-elapsed ${isUrgent(order.created_at) ? 'urgent' : ''}" data-created="${order.created_at}">${elapsedText(order.created_at)}</div>
                    </div>
                </div>
                <div class="ticket-customer"><i class="fas fa-user"></i> ${order.customer_name}</div>
                <div class="ticket-meta">
                    <span class="ticket-chip">${order.order_type_label}</span>
                    ${order.table_number ? `<span class="ticket-chip"><i class="fas fa-chair"></i> Table ${order.table_number}</span>` : ''}
                    <span class="ticket-chip"><i class="fas fa-clock"></i> ${formatTime(order.created_at)}</span>
                    ${order.order_type === 'online' && order.pickup_at ? `<span class="ticket-chip"><i class="fas fa-calendar-clock"></i> Pickup ${formatPickup(order.pickup_at)}</span>` : ''}
                    ${order.extra_prep_minutes > 0 ? `<span class="ticket-chip extended"><i class="fas fa-hourglass-half"></i> +${order.extra_prep_minutes}m added</span>` : ''}
                </div>
                <div class="ticket-items">
                    ${itemsHtml}
                    ${moreCount > 0 ? `<div class="ticket-more-items">+${moreCount} more item${moreCount > 1 ? 's' : ''}</div>` : ''}
                </div>
                ${actionBtn}
            </div>
        `;
    }

    function renderBoard(orders) {
        const grouped = { PendingOnline: [], PendingWalkin: [], Processing: [], Ready: [], Completed: [] };
        orders.forEach(o => {
            if (o.status === 'Pending') {
                (o.order_type === 'online' ? grouped.PendingOnline : grouped.PendingWalkin).push(o);
            } else if (grouped[o.status]) {
                grouped[o.status].push(o);
            }
        });

        // Online pending orders are sorted by requested pickup date/time
        // (soonest first) rather than by when the order was placed, since
        // that's what the kitchen needs to prioritize prep against.
        grouped.PendingOnline.sort((a, b) => {
            if (!a.pickup_at && !b.pickup_at) return 0;
            if (!a.pickup_at) return 1;
            if (!b.pickup_at) return -1;
            return new Date(a.pickup_at) - new Date(b.pickup_at);
        });

        Object.keys(COLUMN_IDS).forEach(key => {
            const col = document.getElementById(COLUMN_IDS[key]);
            const list = grouped[key];
            col.innerHTML = list.length
                ? list.map(renderCard).join('')
                : '<div class="kanban-empty">No orders</div>';
            document.getElementById('colCount' + key).textContent = list.length;
        });

        document.getElementById('countPending').textContent = grouped.PendingOnline.length + grouped.PendingWalkin.length;
        document.getElementById('countPreparing').textContent = grouped.Processing.length;
        document.getElementById('countReady').textContent = grouped.Ready.length;
        document.getElementById('countCompleted').textContent = grouped.Completed.length;
    }

    async function pollOrders() {
        try {
            const res = await fetch("{{ route('kitchen.orders') }}", { headers: { Accept: 'application/json' } });
            const data = await res.json();
            ordersCache = Object.fromEntries(data.orders.map(o => [o.id, o]));
            renderBoard(data.orders);
        } catch (e) {
            console.error('Failed to poll kitchen orders', e);
        }
    }

    function tickElapsed() {
        document.querySelectorAll('.ticket-elapsed[data-created]').forEach(el => {
            const created = el.dataset.created;
            el.textContent = elapsedText(created);
            el.classList.toggle('urgent', isUrgent(created));
        });
    }

    function openDetailModal(orderId) {
        const order = ordersCache[orderId];
        if (!order) return;

        const itemsHtml = order.items.map(item => `
            <div class="detail-item-row">
                <div>
                    <div>${item.name}${item.notes ? `<div class="detail-item-note"><i class="fas fa-note-sticky"></i> ${item.notes}</div>` : ''}</div>
                </div>
                <div style="font-weight:700;color:var(--primary);flex-shrink:0">×${item.quantity}</div>
            </div>
        `).join('');

        const actionBtn = order.next_action
            ? `<button type="button" class="ticket-action-btn ${STATUS_CSS_CLASS[order.status]}" style="margin-top:1rem" onclick="confirmStatusChange(${order.id})">${order.next_action}</button>`
            : '';

        const revertBtn = order.can_revert
            ? `<button type="button" class="ticket-revert-btn-wide" onclick="confirmRevert(${order.id})"><i class="fas fa-rotate-left"></i> Revert to ${order.previous_status_label}</button>`
            : '';

        const extendBtn = order.can_extend_prep
            ? `<button type="button" class="ticket-extend-btn-wide" onclick="confirmExtendPrep(${order.id})"><i class="fas fa-clock-rotate-left"></i> Extend Prep Time (+10 min)</button>`
            : '';

        document.getElementById('detailModalContent').innerHTML = `
            <div class="detail-header">
                <div>
                    <div class="detail-order-number">#${order.order_number}</div>
                    <span class="detail-status-badge" style="background:${STATUS_COLORS[order.status]}">${order.status_label}</span>
                </div>
                <button type="button" class="detail-close-btn" onclick="closeDetailModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="detail-meta-grid">
                <div class="detail-meta-item"><div class="detail-meta-label">Customer</div><div class="detail-meta-value">${order.customer_name}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Order Type</div><div class="detail-meta-value">${order.order_type_label}${order.table_number ? ' · Table ' + order.table_number : ''}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Order Time</div><div class="detail-meta-value">${formatTime(order.created_at)}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Total Items</div><div class="detail-meta-value">${order.item_count}</div></div>
                ${order.estimated_completion ? `
                <div class="detail-meta-item"><div class="detail-meta-label">Est. Ready</div><div class="detail-meta-value">${formatTime(order.estimated_completion)}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Extended By</div><div class="detail-meta-value">${order.extra_prep_minutes > 0 ? order.extra_prep_minutes + ' min' : '—'}</div></div>
                ` : ''}
            </div>
            <div class="detail-section-label">Ordered Items</div>
            ${itemsHtml}
            ${order.special_instructions ? `<div class="detail-instructions-box"><i class="fas fa-circle-info"></i> ${order.special_instructions}</div>` : ''}
            ${actionBtn}
            ${extendBtn}
            ${revertBtn}
        `;
        document.getElementById('orderDetailModal').classList.add('open');
    }

    function closeDetailModal() {
        document.getElementById('orderDetailModal').classList.remove('open');
    }
    document.getElementById('orderDetailModal').addEventListener('click', function (e) {
        if (e.target === this) closeDetailModal();
    });

    function confirmStatusChange(orderId) {
        const order = ordersCache[orderId];
        if (!order || !order.next_action) return;

        openConfirmModal({
            title: `${order.next_action}?`,
            desc: `Mark Order #${order.order_number} as ${order.next_action.replace(/^(Start|Mark as)\s*/, '')}?`,
            confirmText: order.next_action,
            onConfirm: () => submitStatusChange(orderId),
        });
    }

    async function submitStatusChange(orderId) {
        const order = ordersCache[orderId];
        const targetStatus = { Pending: 'Processing', Processing: 'Ready', Ready: 'Completed' }[order.status];

        try {
            const res = await fetch(`/kitchen/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify({ status: targetStatus }),
            });
            const data = await res.json();

            closeConfirmModal();
            closeDetailModal();

            if (!res.ok) {
                showToast(data.message || 'Failed to update order status.', 'error');
                return;
            }

            showToast(data.message, 'success');
            pollOrders();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to update order status.', 'error');
        }
    }

    function confirmRevert(orderId) {
        const order = ordersCache[orderId];
        if (!order || !order.can_revert) return;

        openConfirmModal({
            title: 'Revert Order?',
            desc: `Move Order #${order.order_number} back to ${order.previous_status_label}? Only do this if the status was changed by mistake.`,
            confirmText: 'Revert',
            onConfirm: () => submitRevert(orderId),
        });
    }

    async function submitRevert(orderId) {
        try {
            const res = await fetch(`/kitchen/orders/${orderId}/revert`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            });
            const data = await res.json();

            closeConfirmModal();
            closeDetailModal();

            if (!res.ok) {
                showToast(data.message || 'Failed to revert order status.', 'error');
                return;
            }

            showToast(data.message, 'success');
            pollOrders();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to revert order status.', 'error');
        }
    }

    function confirmExtendPrep(orderId) {
        const order = ordersCache[orderId];
        if (!order || !order.can_extend_prep) return;

        openConfirmModal({
            title: 'Extend Prep Time?',
            desc: `Push back Order #${order.order_number}'s estimated ready time by 10 minutes? The customer will see the updated estimate.`,
            confirmText: 'Extend +10 min',
            onConfirm: () => submitExtendPrep(orderId),
        });
    }

    async function submitExtendPrep(orderId) {
        try {
            const res = await fetch(`/kitchen/orders/${orderId}/extend-prep`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            });
            const data = await res.json();

            closeConfirmModal();
            closeDetailModal();

            if (!res.ok) {
                showToast(data.message || 'Failed to extend prep time.', 'error');
                return;
            }

            showToast(data.message, 'success');
            pollOrders();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to extend prep time.', 'error');
        }
    }

    // Initial paint + polling
    pollOrders();
    setInterval(pollOrders, 8000);
    setInterval(tickElapsed, 1000);
</script>
@endsection
