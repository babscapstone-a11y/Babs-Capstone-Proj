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
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.1rem;
        align-items: start;
    }
    .kanban-col {
        background: rgba(17,24,39,0.03); border-radius: 16px;
        padding: .9rem; min-height: 200px;
    }
    .kanban-col-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .3rem .3rem .8rem; font-weight: 700; font-size: .95rem;
    }
    .kanban-col-header .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: .5rem; }
    .kanban-count {
        background: var(--white); border-radius: 50px; padding: .12rem .6rem;
        font-size: .78rem; font-weight: 700; color: var(--muted);
        border: 1px solid var(--border);
    }
    .kanban-cards { display: flex; flex-direction: column; gap: .85rem; }
    .kanban-empty { text-align: center; color: var(--muted); font-size: .85rem; padding: 2rem 1rem; }

    /* ── Ticket card ─────────────────────────────────────────── */
    .ticket-card {
        background: var(--white); border-radius: 14px; padding: 1.1rem 1.2rem;
        border-left: 5px solid var(--status-pending);
        box-shadow: 0 2px 10px rgba(17,24,39,0.06);
        cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
    }
    .ticket-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(17,24,39,0.1); }
    .ticket-card.status-pending   { border-left-color: var(--status-pending); }
    .ticket-card.status-preparing { border-left-color: var(--status-preparing); }
    .ticket-card.status-ready     { border-left-color: var(--status-ready); }
    .ticket-card.status-completed { border-left-color: var(--status-completed); opacity: .82; }

    .ticket-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
    .ticket-order-number { font-size: 1.25rem; font-weight: 800; color: var(--dark); }
    .ticket-elapsed {
        font-size: .82rem; font-weight: 700; padding: .18rem .55rem; border-radius: 50px;
        background: rgba(17,24,39,0.06); color: var(--muted); white-space: nowrap;
    }
    .ticket-elapsed.urgent { background: rgba(220,38,38,0.12); color: var(--primary); }
    .ticket-customer { font-size: 1rem; font-weight: 600; color: var(--dark); margin-top: .35rem; }
    .ticket-meta {
        display: flex; align-items: center; gap: .6rem; flex-wrap: wrap;
        font-size: .8rem; color: var(--muted); margin-top: .3rem;
    }
    .ticket-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: rgba(17,24,39,0.05); border-radius: 50px; padding: .15rem .55rem;
        font-weight: 600;
    }
    .ticket-items { margin-top: .75rem; border-top: 1px dashed var(--border); padding-top: .7rem; }
    .ticket-item-row { display: flex; justify-content: space-between; gap: .5rem; font-size: .88rem; margin-bottom: .3rem; }
    .ticket-item-name { font-weight: 600; color: var(--dark); }
    .ticket-item-qty { font-weight: 700; color: var(--primary); flex-shrink: 0; }
    .ticket-item-note { font-size: .78rem; color: var(--accent); font-style: italic; margin-top: -.15rem; margin-bottom: .3rem; }
    .ticket-more-items { font-size: .78rem; color: var(--muted); font-style: italic; }

    .ticket-action-btn {
        width: 100%; margin-top: .9rem; padding: .65rem; border-radius: 10px;
        border: none; font-family: inherit; font-size: .88rem; font-weight: 700;
        cursor: pointer; color: var(--white); transition: filter .15s;
    }
    .ticket-action-btn:hover { filter: brightness(1.08); }
    .ticket-action-btn.status-pending   { background: var(--status-pending); }
    .ticket-action-btn.status-preparing { background: var(--status-preparing); }
    .ticket-action-btn.status-ready     { background: var(--status-ready); }

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

    @media (max-width: 1100px) { .summary-row, .kanban-board { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .summary-row, .kanban-board { grid-template-columns: 1fr; } }
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
        <div class="kanban-col-header"><span><span class="dot" style="background:var(--status-pending)"></span>Pending</span><span class="kanban-count" id="colCountPending">0</span></div>
        <div class="kanban-cards" id="col-Pending"></div>
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
    const COLUMN_IDS = { Pending: 'col-Pending', Processing: 'col-Processing', Ready: 'col-Ready', Completed: 'col-Completed' };

    let ordersCache = {};

    function formatTime(iso) {
        return new Date(iso).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
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

        return `
            <div class="ticket-card ${cssClass}" onclick="openDetailModal(${order.id})">
                <div class="ticket-top">
                    <div class="ticket-order-number">#${order.order_number}</div>
                    <div class="ticket-elapsed ${isUrgent(order.created_at) ? 'urgent' : ''}" data-created="${order.created_at}">${elapsedText(order.created_at)}</div>
                </div>
                <div class="ticket-customer"><i class="fas fa-user"></i> ${order.customer_name}</div>
                <div class="ticket-meta">
                    <span class="ticket-chip">${order.order_type_label}</span>
                    ${order.table_number ? `<span class="ticket-chip"><i class="fas fa-chair"></i> Table ${order.table_number}</span>` : ''}
                    <span class="ticket-chip"><i class="fas fa-clock"></i> ${formatTime(order.created_at)}</span>
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
        const grouped = { Pending: [], Processing: [], Ready: [], Completed: [] };
        orders.forEach(o => { if (grouped[o.status]) grouped[o.status].push(o); });

        Object.keys(COLUMN_IDS).forEach(status => {
            const col = document.getElementById(COLUMN_IDS[status]);
            const list = grouped[status];
            col.innerHTML = list.length
                ? list.map(renderCard).join('')
                : '<div class="kanban-empty">No orders</div>';
            document.getElementById('colCount' + status).textContent = list.length;
        });

        document.getElementById('countPending').textContent = grouped.Pending.length;
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
            </div>
            <div class="detail-section-label">Ordered Items</div>
            ${itemsHtml}
            ${order.special_instructions ? `<div class="detail-instructions-box"><i class="fas fa-circle-info"></i> ${order.special_instructions}</div>` : ''}
            ${actionBtn}
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

    // Initial paint + polling
    pollOrders();
    setInterval(pollOrders, 8000);
    setInterval(tickElapsed, 1000);
</script>
@endsection
