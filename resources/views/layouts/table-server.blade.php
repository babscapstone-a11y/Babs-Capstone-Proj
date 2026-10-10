<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Food Server') – BAB'S RESTO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/BabsLandingPageLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary:    #DC2626;
            --primary-dk: #B91C1C;
            --accent:     #F59E0B;
            --dark:       #111827;
            --white:      #ffffff;
            --bg:         #F8FAFC;
            --muted:      #6B7280;
            --border:     rgba(17,24,39,0.08);
            --status-ready:     #16A34A;
            --status-served:    #2563EB;
            --status-packaged:  #F59E0B;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { overflow-x: hidden; }
        body { font-family: 'Poppins', system-ui, sans-serif; margin: 0; background: var(--bg); color: var(--dark); width: 100%; }
        a { text-decoration: none; }

        /* ── Top nav ─────────────────────────────────────────── */
        .kds-topbar {
            background: var(--dark);
            padding: .85rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; left: 0; right: 0; z-index: 100;
            width: 100%;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .kds-brand { display: flex; align-items: center; gap: .75rem; }
        .logo-badge {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 800; font-size: 14px;
            box-shadow: 0 8px 20px rgba(220,38,38,0.35);
        }
        .kds-brand-text { color: var(--white); font-weight: 700; font-size: 1rem; line-height: 1.2; }
        .kds-brand-sub  { color: rgba(255,255,255,0.4); font-size: .72rem; margin-top: .1rem; }

        .kds-topbar-right { display: flex; align-items: center; gap: 1.5rem; }
        .kds-datetime { text-align: right; color: rgba(255,255,255,0.85); line-height: 1.25; }
        .kds-date { font-size: .78rem; color: rgba(255,255,255,0.45); }
        .kds-clock { font-size: 1.05rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .kds-staff { display: flex; align-items: center; gap: .6rem; }
        .kds-staff-avatar {
            width: 36px; height: 36px; border-radius: 9px;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 700; font-size: .78rem;
        }
        .kds-staff-name { color: var(--white); font-size: .85rem; font-weight: 600; }
        .kds-staff-role { color: rgba(255,255,255,0.4); font-size: .68rem; }
        .btn-logout-kds {
            display: flex; align-items: center; justify-content: center; gap: .45rem;
            padding: .55rem .9rem; border-radius: 9px;
            background: rgba(220,38,38,0.18); border: 1px solid rgba(220,38,38,0.3);
            color: #FCA5A5; font-size: .8rem; font-weight: 600; font-family: inherit;
            cursor: pointer; transition: all .2s;
        }
        .btn-logout-kds:hover { background: rgba(220,38,38,0.32); color: var(--white); }
        .nav-link-ts {
            display: flex; align-items: center; gap: .45rem;
            padding: .55rem .9rem; border-radius: 9px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8); font-size: .8rem; font-weight: 600;
            transition: all .2s;
        }
        .nav-link-ts:hover { background: rgba(255,255,255,0.12); color: var(--white); }

        /* ── Page content ────────────────────────────────────── */
        .kds-content { padding: 1.5rem 1.75rem 2.5rem; }

        /* ── Toasts ──────────────────────────────────────────── */
        .toast-wrap {
            /* below the top bar, so pop-ups never cover the bell, staff name or Sign Out */
            position: fixed; top: calc(var(--ts-header-h, 70px) + 12px); right: 1.25rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
        }
        .toast {
            display: flex; align-items: center; gap: .65rem;
            padding: .85rem 1.2rem; border-radius: 12px;
            font-size: .9rem; font-weight: 500; min-width: 300px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            animation: toastIn .3s cubic-bezier(.34,1.56,.64,1) both;
            color: var(--white);
        }
        .toast.success { background: #16A34A; }
        .toast.error   { background: var(--primary); }
        .toast.info    { background: #2563EB; }
        @keyframes toastIn  { from { opacity:0; transform:translateX(60px) } to { opacity:1; transform:translateX(0) } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0) }   to { opacity:0; transform:translateX(60px) } }
        .toast.hiding { animation: toastOut .3s ease forwards; }

        /* ── Confirm modal ───────────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: none; align-items: center; justify-content: center;
            z-index: 1000; padding: 1rem;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 20px;
            padding: 2rem; max-width: 440px; width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.2);
            animation: modalIn .3s cubic-bezier(.22,.68,0,1.2) both;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(.92) translateY(16px) } to { opacity: 1; transform: none } }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.1rem; font-size: 1.4rem;
            background: rgba(245,158,11,0.12); color: var(--accent);
        }
        .modal-title { font-size: 1.15rem; font-weight: 700; text-align: center; margin: 0 0 .4rem; }
        .modal-desc  { font-size: .9rem; color: var(--muted); text-align: center; line-height: 1.6; margin: 0 0 1.5rem; }
        .modal-actions { display: flex; gap: .75rem; }
        .modal-actions button {
            flex: 1; padding: .7rem; border-radius: 10px;
            font-size: .9rem; font-weight: 600; font-family: inherit;
            cursor: pointer; text-align: center;
            transition: all .18s ease; border: none;
        }
        .btn-modal-cancel { background: rgba(17,24,39,0.07); color: var(--dark); }
        .btn-modal-cancel:hover { background: rgba(17,24,39,0.12); }
        .btn-modal-confirm { background: var(--primary); color: var(--white); }
        .btn-modal-confirm:hover { background: var(--primary-dk); }

        /* ── Notification bell ───────────────────────────────── */
        .ts-notif { position: relative; }
        .ts-bell {
            position: relative; width: 42px; height: 42px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            color: #fff; font-size: 1.05rem; cursor: pointer; transition: background .18s;
        }
        .ts-bell:hover, .ts-bell[aria-expanded="true"] { background: rgba(255,255,255,0.14); }
        .ts-bell:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
        .ts-bell.ring i { animation: bellRing .9s ease; transform-origin: 50% 0; }
        @keyframes bellRing {
            0%,100% { transform: rotate(0); } 15% { transform: rotate(16deg); } 30% { transform: rotate(-14deg); }
            45% { transform: rotate(10deg); } 60% { transform: rotate(-8deg); } 75% { transform: rotate(4deg); }
        }
        .ts-bell-badge {
            position: absolute; top: -6px; right: -6px; min-width: 20px; height: 20px; padding: 0 5px;
            border-radius: 50px; background: var(--primary); color: #fff; border: 2px solid var(--dark);
            font-size: .68rem; font-weight: 800; display: flex; align-items: center; justify-content: center;
        }
        .ts-bell-badge[hidden] { display: none; }

        /* Fixed to the screen (placed under the bell by script): the page body clips anything
           absolutely positioned past its content because html/body use overflow-x: hidden */
        .ts-notif-panel {
            position: fixed; top: calc(var(--ts-header-h, 70px) + 8px); right: 1.25rem; z-index: 1200;
            width: 360px; max-width: calc(100vw - 2rem);
            background: var(--white); color: var(--dark); border-radius: 16px;
            box-shadow: 0 20px 50px rgba(17,24,39,0.25); border: 1px solid var(--border);
            overflow: hidden; animation: notifPanelIn .18s ease both;
        }
        .ts-notif-panel[hidden] { display: none; }
        @keyframes notifPanelIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .ts-notif-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: .9rem 1.1rem; border-bottom: 1px solid var(--border);
            font-weight: 700; font-size: .92rem;
        }
        .ts-notif-head small { color: var(--muted); font-weight: 500; font-size: .74rem; }
        .ts-notif-list { max-height: 380px; overflow-y: auto; }
        .ts-notif-item {
            display: flex; gap: .75rem; padding: .85rem 1.1rem; text-decoration: none; color: inherit;
            border-bottom: 1px solid rgba(17,24,39,0.06); transition: background .15s;
        }
        .ts-notif-item:last-child { border-bottom: none; }
        .ts-notif-item:hover { background: #F9FAFB; }
        .ts-notif-item.unread { background: #F0FDF4; }
        .ts-notif-item.unread:hover { background: #DCFCE7; }
        .ts-notif-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .9rem; color: #fff;
            background: #16A34A;
        }
        .ts-notif-icon.packaging { background: #F59E0B; }
        .ts-notif-text { flex: 1; min-width: 0; font-size: .84rem; line-height: 1.4; }
        .ts-notif-text strong { font-weight: 700; }
        .ts-notif-time { font-size: .72rem; color: var(--muted); margin-top: .15rem; }
        .ts-notif-dot { width: 8px; height: 8px; border-radius: 50%; background: #16A34A; flex-shrink: 0; margin-top: .45rem; }
        .ts-notif-empty { padding: 2rem 1.1rem; text-align: center; color: var(--muted); font-size: .85rem; }
        .ts-notif-empty i { display: block; font-size: 1.6rem; opacity: .35; margin-bottom: .5rem; }
        .ts-notif-foot {
            display: block; text-align: center; padding: .75rem; font-size: .82rem; font-weight: 700;
            color: var(--primary); border-top: 1px solid var(--border); text-decoration: none;
        }
        .ts-notif-foot:hover { background: #FEF2F2; }

        @media (max-width: 768px) {
            .ts-notif-panel { right: 1rem !important; left: 1rem; width: auto; }
            .kds-topbar { padding: .75rem 1rem; flex-wrap: wrap; gap: .6rem; }
            .kds-content { padding: 1rem; }
            .kds-datetime { display: none; }
        }
    </style>

    @yield('styles')
</head>
<body>

    <header class="kds-topbar">
        <div class="kds-brand">
            <div class="logo-badge" aria-label="BAB'S RESTO">BR</div>
            <div>
                <div class="kds-brand-text">BAB'S RESTO</div>
                <div class="kds-brand-sub">Food Server Ordering</div>
            </div>
        </div>

        <div class="kds-topbar-right">
            @php
                $tsNavItems = [
                    ['route' => 'table-server.index',         'icon' => 'fa-utensils',         'label' => 'Take Order',    'active' => request()->routeIs('table-server.index')],
                    ['route' => 'table-server.service.index', 'icon' => 'fa-bell-concierge',    'label' => 'Ready Orders',  'active' => request()->routeIs('table-server.service.*')],
                    ['route' => 'table-server.orders.index',  'icon' => 'fa-receipt',           'label' => 'My Orders',     'active' => request()->routeIs('table-server.orders.index')],
                ];
            @endphp
            @foreach($tsNavItems as $navItem)
                @if(! $navItem['active'])
                <a href="{{ route($navItem['route']) }}" class="nav-link-ts">
                    <i class="fas {{ $navItem['icon'] }}"></i> {{ $navItem['label'] }}
                </a>
                @endif
            @endforeach
            <div class="kds-datetime">
                <div class="kds-date">{{ now()->format('l, F d, Y') }}</div>
                <div class="kds-clock" id="liveClock">--:--:-- --</div>
            </div>

            {{-- Notifications: orders that are ready for the food server to serve / package --}}
            <div class="ts-notif" id="tsNotif">
                <button type="button" class="ts-bell" id="tsBell" aria-haspopup="true" aria-expanded="false"
                        aria-controls="tsNotifPanel" aria-label="Notifications" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="ts-bell-badge" id="tsBellBadge" hidden>0</span>
                </button>
                <div class="ts-notif-panel" id="tsNotifPanel" role="region" aria-label="Notifications" hidden>
                    <div class="ts-notif-head">
                        <span>Notifications</span>
                        <small id="tsNotifSummary">Orders ready for you</small>
                    </div>
                    <div class="ts-notif-list" id="tsNotifList">
                        <div class="ts-notif-empty"><i class="fas fa-bell-slash"></i> Loading…</div>
                    </div>
                    <a href="{{ route('table-server.service.index') }}" class="ts-notif-foot">
                        View all ready orders <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="kds-staff">
                <div class="kds-staff-avatar">{{ auth()->user()->initials }}</div>
                <div>
                    <div class="kds-staff-name">{{ auth()->user()->name ?: auth()->user()->email }}</div>
                    <div class="kds-staff-role">Food Server</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
            </form>
            <button type="button" class="btn-logout-kds" onclick="openConfirmModal({
                    title: 'Log Out?',
                    desc: 'Are you sure you want to log out of your account?',
                    confirmText: 'Log Out',
                    onConfirm: () => document.getElementById('logoutForm').submit(),
                })">
                <i class="fas fa-right-from-bracket"></i> Sign Out
            </button>
        </div>
    </header>

    <div class="toast-wrap" id="toastContainer" aria-live="polite"></div>

    <main class="kds-content">
        @yield('content')
    </main>

    <!-- ── Shared confirm modal (JS-callback based, not form-based) ── -->
    <div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
        <div class="modal-box">
            <div class="modal-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <h3 class="modal-title" id="modalTitle">Are you sure?</h3>
            <p class="modal-desc" id="modalDesc"></p>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-modal-confirm" id="modalConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Re-fetches this page's HTML purely to read a live CSRF token out of it —
        // used to recover from a 419 (stale token) without a full page reload, which
        // would otherwise wipe any in-progress client-side state (e.g. a food
        // server's not-yet-submitted order). Returns false if the session itself
        // is gone (the request gets redirected to the login page instead).
        async function refreshCsrfToken() {
            try {
                const res = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
                if (res.redirected && new URL(res.url).pathname.startsWith('/login')) {
                    return false;
                }
                const html = await res.text();
                const match = html.match(/name="csrf-token"\s+content="([^"]+)"/);
                if (!match) return false;
                csrfToken = match[1];
                document.querySelector('meta[name="csrf-token"]').content = csrfToken;
                return true;
            } catch (e) {
                return false;
            }
        }

        function showToast(msg, type = 'success', duration = 3500) {
            const container = document.getElementById('toastContainer');
            const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${msg}</span>`;
            container.appendChild(el);
            setTimeout(() => {
                el.classList.add('hiding');
                setTimeout(() => el.remove(), 300);
            }, duration);
        }

        let _confirmCallback = null;
        function openConfirmModal({ title, desc, confirmText = 'Confirm', onConfirm }) {
            document.getElementById('modalTitle').textContent = title || 'Are you sure?';
            document.getElementById('modalDesc').textContent = desc || '';
            const btn = document.getElementById('modalConfirmBtn');
            btn.textContent = confirmText;
            _confirmCallback = onConfirm;
            document.getElementById('confirmModal').classList.add('open');
        }
        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('open');
            _confirmCallback = null;
        }
        document.getElementById('modalConfirmBtn').addEventListener('click', function () {
            if (typeof _confirmCallback === 'function') _confirmCallback();
        });
        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirmModal();
        });

        // Live clock
        function tickClock() {
            const el = document.getElementById('liveClock');
            if (!el) return;
            el.textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        tickClock();
        setInterval(tickClock, 1000);

        // ── Notification bell ───────────────────────────────────────────────
        // Lists every order that is Ready for the food server (to serve, or to package for
        // pickup). Polls the same endpoint as the Ready Orders page. "Read" order ids are
        // remembered per staff member in localStorage, so the badge survives page changes.
        window.tsNotifications = (function () {
            const ORDERS_URL = @json(route('table-server.service.orders'));
            const SHOW_BASE  = @json(url('/table-server/service'));
            const SEEN_KEY   = 'tsNotifSeen:' + @json(auth()->id());

            const bell  = document.getElementById('tsBell');
            const badge = document.getElementById('tsBellBadge');
            const panel = document.getElementById('tsNotifPanel');
            const list  = document.getElementById('tsNotifList');
            const summary = document.getElementById('tsNotifSummary');

            let ready = [];          // current Ready orders
            let known = null;        // ids seen on the previous poll (null until the first poll)

            const loadSeen = () => { try { return new Set(JSON.parse(localStorage.getItem(SEEN_KEY) || '[]')); } catch (e) { return new Set(); } };
            const saveSeen = s => { try { localStorage.setItem(SEEN_KEY, JSON.stringify([...s])); } catch (e) {} };
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function ago(iso) {
                if (!iso) return '';
                const m = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 60000));
                if (m < 1) return 'Just now';
                if (m < 60) return m + ' min ago';
                const h = Math.floor(m / 60);
                return h + ' hr' + (h > 1 ? 's' : '') + (m % 60 ? ' ' + (m % 60) + ' min' : '') + ' ago';
            }

            function message(o) {
                return o.uses_packaging
                    ? `<strong>Order #${esc(o.order_number)}</strong> (${esc(o.order_type_label)}) is ready to package for pickup.`
                    : `<strong>Order #${esc(o.order_number)}</strong>${o.table_number ? ` for <strong>Table ${esc(o.table_number)}</strong>` : ''} is ready to serve.`;
            }

            function render() {
                const seen = loadSeen();
                const unread = ready.filter(o => !seen.has(o.id)).length;
                badge.textContent = unread > 99 ? '99+' : unread;
                badge.hidden = unread === 0;
                bell.setAttribute('aria-label', unread ? `Notifications, ${unread} unread` : 'Notifications');
                summary.textContent = ready.length ? `${ready.length} order${ready.length === 1 ? '' : 's'} ready` : 'Orders ready for you';

                list.innerHTML = ready.length
                    ? ready.map(o => `
                        <a class="ts-notif-item ${seen.has(o.id) ? '' : 'unread'}" href="${SHOW_BASE}/${o.id}">
                            <div class="ts-notif-icon ${o.uses_packaging ? 'packaging' : ''}"><i class="fas ${o.uses_packaging ? 'fa-box' : 'fa-bell-concierge'}"></i></div>
                            <div class="ts-notif-text">
                                <div>${message(o)}</div>
                                <div class="ts-notif-time"><i class="fas fa-clock"></i> Ready ${ago(o.ready_at)}${o.customer_name ? ' · ' + esc(o.customer_name) : ''}</div>
                            </div>
                            ${seen.has(o.id) ? '' : '<span class="ts-notif-dot" aria-label="Unread"></span>'}
                        </a>`).join('')
                    : '<div class="ts-notif-empty"><i class="fas fa-bell-slash"></i> No new notifications. Ready orders will appear here.</div>';
            }

            // Takes the orders list (from the poll, or from a page that already fetched it)
            function ingest(orders) {
                ready = orders.filter(o => o.status === 'Ready')
                              .sort((a, b) => new Date(b.ready_at || 0) - new Date(a.ready_at || 0));
                const ids = new Set(ready.map(o => o.id));

                // Alert for orders that became ready since the last poll (not on first load)
                if (known) {
                    const fresh = ready.filter(o => !known.has(o.id) && !loadSeen().has(o.id));
                    if (fresh.length) {
                        bell.classList.remove('ring'); void bell.offsetWidth; bell.classList.add('ring');
                        showToast(fresh.length === 1
                            ? `Order #${esc(fresh[0].order_number)} is ready ${fresh[0].uses_packaging ? 'to package' : 'to serve'}.`
                            : `${fresh.length} new orders are ready.`, 'info', 5000);
                    }
                }
                known = ids;

                // Forget "read" ids for orders that are no longer ready, so the stored list stays small
                const seen = loadSeen();
                saveSeen(new Set([...seen].filter(id => ids.has(id))));
                render();
            }

            async function poll() {
                try {
                    const res = await fetch(ORDERS_URL, { headers: { Accept: 'application/json' } });
                    if (!res.ok) return;
                    ingest((await res.json()).orders || []);
                } catch (e) { /* offline or session ended — try again next poll */ }
            }

            // The top bar's height positions the dropdown and pop-ups just below it
            const header = document.querySelector('.kds-topbar');
            const measure = () => document.documentElement.style.setProperty('--ts-header-h', header.offsetHeight + 'px');
            measure();
            window.addEventListener('resize', () => { measure(); if (!panel.hidden) place(); });

            // Line the dropdown's right edge up with the bell (desktop; on phones CSS spans the width)
            function place() {
                panel.style.right = Math.max(16, window.innerWidth - bell.getBoundingClientRect().right) + 'px';
            }

            function open(show) {
                if (show) place();
                panel.hidden = !show;
                bell.setAttribute('aria-expanded', show ? 'true' : 'false');
                if (show) {
                    render();                                              // show which ones are new…
                    const seen = loadSeen(); ready.forEach(o => seen.add(o.id)); saveSeen(seen);
                    badge.hidden = true;                                   // …and mark them all read
                    bell.setAttribute('aria-label', 'Notifications');
                } else {
                    render();
                }
            }

            bell.addEventListener('click', e => { e.stopPropagation(); open(panel.hidden); });
            document.addEventListener('click', e => { if (!panel.hidden && !document.getElementById('tsNotif').contains(e.target)) open(false); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && !panel.hidden) { open(false); bell.focus(); } });

            poll();
            setInterval(poll, 8000);
            setInterval(() => { if (!panel.hidden) render(); }, 30000);   // keep "x min ago" fresh

            return { refresh: poll, ingest };
        })();
    </script>

    @yield('scripts')
</body>
</html>
