@extends('layouts.table-server')

@section('title', 'Ready Orders')

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
    .summary-icon.ready     { background: rgba(22,163,74,.14);  color: var(--status-ready); }
    .summary-icon.served    { background: rgba(37,99,235,.14);  color: var(--status-served); }
    .summary-icon.packaged  { background: rgba(245,158,11,.14); color: var(--status-packaged); }
    .summary-icon.avg       { background: rgba(220,38,38,.12);  color: var(--primary); }
    .summary-count { font-size: 1.7rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .summary-label  { font-size: .78rem; color: var(--muted); font-weight: 600; margin-top: .2rem; }

    /* ── Notification banner ────────────────────────────────── */
    .notif-stack { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.25rem; }
    .notif-banner {
        display: flex; align-items: center; gap: .8rem;
        background: rgba(22,163,74,.08); border: 1.5px solid rgba(22,163,74,.3);
        border-radius: 12px; padding: .8rem 1rem; cursor: pointer; transition: background .15s;
        animation: notifIn .3s ease both;
    }
    .notif-banner:hover { background: rgba(22,163,74,.14); }
    @keyframes notifIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
    .notif-icon {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        background: var(--status-ready); color: #fff; display: flex; align-items: center; justify-content: center;
        animation: notifPulse 1.6s ease-in-out infinite;
    }
    @keyframes notifPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.35); } 50% { box-shadow: 0 0 0 8px rgba(22,163,74,0); } }
    .notif-text { flex: 1; font-size: .87rem; color: var(--dark); font-weight: 600; }
    .notif-dismiss { background: none; border: none; color: var(--muted); cursor: pointer; font-size: .9rem; padding: .3rem; }
    .notif-dismiss:hover { color: var(--dark); }

    /* ── Search + filters ───────────────────────────────────── */
    .toolbar-row { display: flex; gap: .8rem; margin-bottom: 1.1rem; flex-wrap: wrap; align-items: center; }
    .toolbar-search {
        flex: 1; min-width: 240px; display: flex; align-items: center; gap: .6rem;
        background: var(--white); border: 1.5px solid var(--border); border-radius: 50px;
        padding: 0 .3rem 0 1.1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .toolbar-search:focus-within { border-color: var(--primary); }
    .toolbar-search i { color: var(--muted); }
    .toolbar-search input { flex: 1; border: none; outline: none; background: transparent; font-family: inherit; font-size: .88rem; padding: .68rem 0; }
    .filter-chips { display: flex; gap: .55rem; }
    .filter-chip {
        padding: .55rem 1.1rem; border-radius: 50px; border: 1.5px solid var(--border);
        background: var(--white); font-size: .82rem; font-weight: 600; color: var(--dark);
        cursor: pointer; transition: all .18s; white-space: nowrap; font-family: inherit;
    }
    .filter-chip.active, .filter-chip:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

    /* ── Order cards ─────────────────────────────────────────── */
    .service-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.1rem; }
    .service-card {
        background: var(--white); border-radius: 14px; padding: 1.1rem 1.2rem;
        border-left: 5px solid var(--status-ready);
        box-shadow: 0 2px 10px rgba(17,24,39,0.06);
        cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
    }
    .service-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(17,24,39,0.1); }
    .service-card.status-served    { border-left-color: var(--status-served); opacity: .85; }
    .service-card.status-packaged  { border-left-color: var(--status-packaged); opacity: .85; }

    .sc-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
    .sc-order-number { font-size: 1.15rem; font-weight: 800; color: var(--dark); }
    .sc-badge {
        font-size: .72rem; font-weight: 700; padding: .2rem .6rem; border-radius: 50px; color: #fff; white-space: nowrap;
    }
    .sc-waiting {
        font-size: .78rem; font-weight: 700; padding: .15rem .55rem; border-radius: 50px; margin-top: .5rem;
        background: rgba(17,24,39,0.06); color: var(--muted); display: inline-block;
    }
    .sc-waiting.urgent { background: rgba(220,38,38,0.12); color: var(--primary); }
    .sc-meta { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; font-size: .8rem; color: var(--muted); margin-top: .55rem; }
    .sc-chip { display: inline-flex; align-items: center; gap: .3rem; background: rgba(17,24,39,0.05); border-radius: 50px; padding: .15rem .55rem; font-weight: 600; }
    .sc-customer { font-size: .92rem; font-weight: 600; color: var(--dark); margin-top: .5rem; }
    .sc-items { font-size: .8rem; color: var(--muted); margin-top: .3rem; }

    .sc-action-btn {
        width: 100%; margin-top: .9rem; padding: .65rem; border-radius: 10px;
        border: none; font-family: inherit; font-size: .87rem; font-weight: 700;
        cursor: pointer; color: #fff; transition: filter .15s; background: var(--status-ready);
    }
    .sc-action-btn:hover { filter: brightness(1.08); }
    .sc-action-btn.packaging { background: var(--status-packaged); }

    .service-empty { text-align: center; color: var(--muted); padding: 3.5rem 1.5rem; background: var(--white); border-radius: 14px; border: 1px solid var(--border); grid-column: 1 / -1; }
    .service-empty i { font-size: 2.5rem; margin-bottom: .8rem; opacity: .35; display: block; }

    @media (max-width: 1100px) { .summary-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .summary-row { grid-template-columns: 1fr; } .toolbar-row { flex-direction: column; align-items: stretch; } }
</style>
@endsection

@section('content')

<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon ready"><i class="fas fa-bell-concierge"></i></div>
        <div><div class="summary-count" id="countReady">0</div><div class="summary-label">Ready to Serve</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon served"><i class="fas fa-circle-check"></i></div>
        <div><div class="summary-count" id="countServed">0</div><div class="summary-label">Served Today</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon packaged"><i class="fas fa-box"></i></div>
        <div><div class="summary-count" id="countPackaged">0</div><div class="summary-label">Ready for Pickup (Take-Out)</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon avg"><i class="fas fa-stopwatch"></i></div>
        <div><div class="summary-count" id="countAvg">—</div><div class="summary-label">Avg. Serving Time</div></div>
    </div>
</div>

<div class="notif-stack" id="notifStack"></div>

<div class="toolbar-row">
    <div class="toolbar-search">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search order #, table number, or customer name…">
    </div>
    <div class="filter-chips" id="filterChips">
        <button type="button" class="filter-chip active" data-filter="all">All</button>
        <button type="button" class="filter-chip" data-filter="Ready">Ready</button>
        <button type="button" class="filter-chip" data-filter="Served">Served</button>
        <button type="button" class="filter-chip" data-filter="Packaged">Packaged</button>
    </div>
</div>

<div class="service-grid" id="serviceGrid"></div>

@endsection

@section('scripts')
<script>
    const ORDERS_URL = "{{ route('table-server.service.orders') }}";
    const SHOW_URL_BASE = "{{ url('/table-server/service') }}";
    const STATUS_CSS_CLASS = { Ready: 'status-ready', Served: 'status-served', Packaged: 'status-packaged' };
    const STATUS_VAR = { Ready: '--status-ready', Served: '--status-served', Packaged: '--status-packaged' };

    let ordersCache = [];
    let activeFilter = 'all';
    let acknowledgedIds = new Set();
    let knownReadyIds = new Set();

    function statusColor(status) {
        return getComputedStyle(document.documentElement).getPropertyValue(STATUS_VAR[status] || '--status-ready').trim();
    }

    function formatTime(iso) {
        if (!iso) return '—';
        return new Date(iso).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function elapsedText(iso) {
        if (!iso) return '—';
        const diffMs = Date.now() - new Date(iso).getTime();
        const totalMinutes = Math.max(0, Math.floor(diffMs / 60000));
        const h = Math.floor(totalMinutes / 60);
        const m = totalMinutes % 60;
        return h > 0 ? `${h}h ${m}m` : `${m}m`;
    }

    function isUrgent(iso) {
        return iso && (Date.now() - new Date(iso).getTime()) > 10 * 60 * 1000; // 10+ min waiting since ready
    }

    function matchesSearch(order, q) {
        if (!q) return true;
        q = q.toLowerCase();
        return order.order_number.toLowerCase().includes(q)
            || (order.table_number && String(order.table_number).includes(q))
            || order.customer_name.toLowerCase().includes(q);
    }

    function renderGrid() {
        const q = document.getElementById('searchInput').value.trim();
        const filtered = ordersCache.filter(o =>
            (activeFilter === 'all' || o.status === activeFilter) && matchesSearch(o, q)
        );

        const grid = document.getElementById('serviceGrid');

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="service-empty">
                    <i class="fas fa-bell-concierge"></i>
                    No orders match this view right now.
                </div>`;
            return;
        }

        grid.innerHTML = filtered.map(order => {
            const cssClass = STATUS_CSS_CLASS[order.status] || 'status-ready';
            const waitingSource = order.status === 'Ready' ? order.ready_at : (order.served_at || order.packaged_at);
            const itemsPreview = order.items.slice(0, 3).map(i => `${i.name} ×${i.quantity}`).join(', ');
            const moreCount = order.items.length - 3;

            let actionBtn = '';
            if (order.can_be_fulfilled) {
                const cls = order.uses_packaging ? 'packaging' : '';
                actionBtn = `<button type="button" class="sc-action-btn ${cls}" onclick="event.stopPropagation(); confirmFulfill(${order.id})">${order.fulfillment_action_label}</button>`;
            }

            return `
                <div class="service-card ${cssClass}" onclick="window.location.href='${SHOW_URL_BASE}/${order.id}'">
                    <div class="sc-top">
                        <div class="sc-order-number">#${order.order_number}</div>
                        <span class="sc-badge" style="background:${statusColor(order.status)}">${order.status_label}</span>
                    </div>
                    ${order.status === 'Ready'
                        ? `<div class="sc-waiting ${isUrgent(waitingSource) ? 'urgent' : ''}" data-waiting-since="${waitingSource || ''}"><i class="fas fa-hourglass-half"></i> Waiting ${elapsedText(waitingSource)}</div>`
                        : `<div class="sc-waiting"><i class="fas fa-clock"></i> ${order.status} at ${formatTime(waitingSource)}</div>`
                    }
                    <div class="sc-customer"><i class="fas fa-user"></i> ${order.customer_name}</div>
                    <div class="sc-meta">
                        <span class="sc-chip">${order.order_type_label}</span>
                        ${order.table_number ? `<span class="sc-chip"><i class="fas fa-chair"></i> Table ${order.table_number}</span>` : ''}
                        <span class="sc-chip"><i class="fas fa-clock"></i> Ready ${formatTime(order.ready_at)}</span>
                    </div>
                    <div class="sc-items">${order.item_count} item${order.item_count === 1 ? '' : 's'}${itemsPreview ? ' — ' + itemsPreview : ''}${moreCount > 0 ? ` +${moreCount} more` : ''}</div>
                    ${actionBtn}
                </div>
            `;
        }).join('');
    }

    function renderNotifications() {
        const readyOrders = ordersCache.filter(o => o.status === 'Ready' && !acknowledgedIds.has(o.id));
        const stack = document.getElementById('notifStack');
        stack.innerHTML = readyOrders.map(o => `
            <div class="notif-banner" onclick="window.location.href='${SHOW_URL_BASE}/${o.id}'">
                <div class="notif-icon"><i class="fas fa-bell"></i></div>
                <div class="notif-text">Order #${o.order_number}${o.table_number ? ` for Table ${o.table_number}` : ''} is ready to serve.</div>
                <button type="button" class="notif-dismiss" onclick="event.stopPropagation(); acknowledgeOrder(${o.id})" title="Dismiss">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
        `).join('');
    }

    function acknowledgeOrder(id) {
        acknowledgedIds.add(id);
        renderNotifications();
    }

    function renderSummary(summary) {
        document.getElementById('countReady').textContent = summary.ready_to_serve;
        document.getElementById('countServed').textContent = summary.served_today;
        document.getElementById('countPackaged').textContent = summary.ready_for_pickup;
        document.getElementById('countAvg').textContent = summary.avg_serving_minutes !== null ? `${summary.avg_serving_minutes}m` : '—';
    }

    async function pollOrders() {
        try {
            const res = await fetch(ORDERS_URL, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            ordersCache = data.orders;

            // New Ready orders since last poll get a fresh, un-acknowledged notification.
            const currentReadyIds = new Set(ordersCache.filter(o => o.status === 'Ready').map(o => o.id));
            knownReadyIds = currentReadyIds;

            renderSummary(data.summary);
            renderNotifications();
            renderGrid();
        } catch (e) {
            console.error('Failed to poll ready orders', e);
        }
    }

    function tickElapsed() {
        document.querySelectorAll('.sc-waiting[data-waiting-since]').forEach(el => {
            const since = el.dataset.waitingSince;
            if (!since) return;
            el.innerHTML = `<i class="fas fa-hourglass-half"></i> Waiting ${elapsedText(since)}`;
            el.classList.toggle('urgent', isUrgent(since));
        });
    }

    function confirmFulfill(orderId) {
        const order = ordersCache.find(o => o.id === orderId);
        if (!order) return;

        openConfirmModal({
            title: `${order.fulfillment_action_label}?`,
            desc: order.uses_packaging
                ? `Confirm that Order #${order.order_number} has been packaged and is ready for customer pickup?`
                : `Confirm that this order has been served to the customer?`,
            confirmText: order.fulfillment_action_label,
            onConfirm: () => submitFulfill(order),
        });
    }

    async function submitFulfill(order) {
        const endpoint = `${SHOW_URL_BASE}/${order.id}/${order.uses_packaging ? 'package' : 'serve'}`;

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            });

            closeConfirmModal();

            if (res.status === 419) {
                const refreshed = await refreshCsrfToken();
                showToast(refreshed ? 'Session refreshed — please try again.' : 'Your session has expired. Please log back in.', refreshed ? 'info' : 'error');
                return;
            }

            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to update this order.', 'error');
                return;
            }

            acknowledgeOrder(order.id);
            showToast(data.message, 'success');
            pollOrders();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to update this order.', 'error');
        }
    }

    document.getElementById('searchInput').addEventListener('input', renderGrid);
    document.getElementById('filterChips').addEventListener('click', (e) => {
        const btn = e.target.closest('.filter-chip');
        if (!btn) return;
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        renderGrid();
    });

    // Initial paint + polling
    pollOrders();
    setInterval(pollOrders, 8000);
    setInterval(tickElapsed, 30000);
</script>
@endsection
