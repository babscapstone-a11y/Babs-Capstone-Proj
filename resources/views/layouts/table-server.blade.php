<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Food Server') – BAB'S RESTO</title>

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
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', system-ui, sans-serif; margin: 0; background: var(--bg); color: var(--dark); }
        a { text-decoration: none; }

        /* ── Top nav ─────────────────────────────────────────── */
        .kds-topbar {
            background: var(--dark);
            padding: .85rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
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
            position: fixed; top: 1.25rem; right: 1.25rem;
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

        @media (max-width: 768px) {
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
            @if(request()->routeIs('table-server.orders.index'))
                <a href="{{ route('table-server.index') }}" class="nav-link-ts">
                    <i class="fas fa-utensils"></i> Take Order
                </a>
            @else
                <a href="{{ route('table-server.orders.index') }}" class="nav-link-ts">
                    <i class="fas fa-receipt"></i> My Orders
                </a>
            @endif
            <div class="kds-datetime">
                <div class="kds-date">{{ now()->format('l, F d, Y') }}</div>
                <div class="kds-clock" id="liveClock">--:--:-- --</div>
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
                    desc: 'Are you sure you want to log out?',
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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
    </script>

    @yield('scripts')
</body>
</html>
