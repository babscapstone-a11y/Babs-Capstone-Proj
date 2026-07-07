<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – BAB'S RESTO</title>

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
            --sidebar-w:  260px;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', system-ui, sans-serif; margin: 0; background: var(--bg); color: var(--dark); }
        a { text-decoration: none; }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--dark);
            position: fixed; left: 0; top: 0; bottom: 0;
            display: flex; flex-direction: column;
            z-index: 200;
            overflow-y: auto;
            transition: transform .28s ease;
        }
        .sidebar-logo {
            display: flex; align-items: center; gap: .75rem;
            padding: 1.4rem 1.25rem 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .logo-badge {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 800; font-size: 14px;
            box-shadow: 0 8px 20px rgba(220,38,38,0.35);
        }
        .logo-text { color: var(--white); font-weight: 700; font-size: .9rem; line-height: 1.2; }
        .logo-sub  { color: rgba(255,255,255,0.38); font-size: .68rem; margin-top: .1rem; }

        .nav-section { padding: .75rem 0; flex: 1; }
        .nav-label {
            padding: .5rem 1.25rem .2rem;
            font-size: .65rem; font-weight: 700; letter-spacing: .08em;
            color: rgba(255,255,255,0.28); text-transform: uppercase;
        }
        .nav-item {
            display: flex; align-items: center; gap: .72rem;
            padding: .62rem 1.25rem;
            color: rgba(255,255,255,0.55);
            font-size: .845rem; font-weight: 500;
            transition: all .18s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }
        .nav-item:hover { color: var(--white); background: rgba(255,255,255,0.06); }
        .nav-item.active {
            color: var(--white);
            background: rgba(220,38,38,0.18);
            border-left-color: var(--primary);
        }
        .nav-item i { width: 18px; text-align: center; font-size: .85rem; }
        .nav-badge {
            margin-left: auto; background: var(--primary);
            color: var(--white); font-size: .65rem; font-weight: 700;
            border-radius: 50px; padding: .1rem .45rem; min-width: 18px; text-align: center;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .user-chip {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem .7rem; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            margin-bottom: .6rem;
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 700; font-size: .75rem;
        }
        .user-name  { color: var(--white); font-size: .82rem; font-weight: 600; }
        .user-role  { color: rgba(255,255,255,0.4); font-size: .68rem; }
        .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            width: 100%; padding: .5rem; border-radius: 8px;
            background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.25);
            color: #FCA5A5; font-size: .8rem; font-weight: 600; font-family: inherit;
            cursor: pointer; transition: all .2s;
        }
        .btn-logout:hover { background: rgba(220,38,38,0.3); color: var(--white); }

        /* ── Main wrapper ───────────────────────────────────── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* ── Top bar ────────────────────────────────────────── */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: .9rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 8px rgba(0,0,0,0.04);
        }
        .topbar-left { display: flex; align-items: center; gap: .75rem; }
        .hamburger {
            display: none; background: none; border: none; cursor: pointer;
            color: var(--muted); font-size: 1.2rem; padding: .25rem;
        }
        .page-title { font-size: 1.1rem; font-weight: 700; color: var(--dark); }
        .breadcrumb { display: flex; align-items: center; gap: .35rem; font-size: .78rem; color: var(--muted); margin-top: .05rem; }
        .breadcrumb a { color: var(--primary); }
        .breadcrumb-sep { color: var(--muted); }

        .topbar-right { display: flex; align-items: center; gap: .75rem; }
        .topbar-user {
            display: flex; align-items: center; gap: .5rem;
            color: var(--dark); font-size: .83rem; font-weight: 500;
        }
        .topbar-avatar {
            width: 34px; height: 34px; border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 700; font-size: .75rem;
        }

        /* ── Page content ───────────────────────────────────── */
        .page-content { padding: 1.75rem 2rem; flex: 1; }

        /* ── Toast flash messages ───────────────────────────── */
        .toast-wrap {
            position: fixed; top: 1.25rem; right: 1.25rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
        }
        .toast {
            display: flex; align-items: center; gap: .65rem;
            padding: .75rem 1.1rem; border-radius: 12px;
            font-size: .85rem; font-weight: 500; min-width: 280px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            animation: toastIn .3s ease both;
        }
        @keyframes toastIn { from { opacity: 0; transform: translateX(20px) } to { opacity: 1; transform: none } }
        .toast-success { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #166534; }
        .toast-error   { background: #FEF2F2; border: 1.5px solid #FCA5A5; color: #991B1B; }
        .toast-info    { background: #EFF6FF; border: 1.5px solid #93C5FD; color: #1E40AF; }
        .toast i { font-size: .9rem; }

        /* ── Shared card ────────────────────────────────────── */
        .card {
            background: var(--white); border-radius: 16px;
            box-shadow: 0 2px 16px rgba(17,24,39,0.06);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem;
        }
        .card-title { font-size: .95rem; font-weight: 700; color: var(--dark); margin: 0; }
        .card-body  { padding: 1.5rem; }

        /* ── Confirmation modal ─────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.45);
            display: none; align-items: center; justify-content: center;
            z-index: 1000; padding: 1rem;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 20px;
            padding: 2rem; max-width: 420px; width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.15);
            animation: modalIn .3s cubic-bezier(.22,.68,0,1.2) both;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(.92) translateY(16px) } to { opacity: 1; transform: none } }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.1rem; font-size: 1.4rem;
        }
        .modal-icon.warn  { background: rgba(245,158,11,0.12); color: var(--accent); }
        .modal-icon.danger{ background: rgba(220,38,38,0.10); color: var(--primary); }
        .modal-title { font-size: 1.1rem; font-weight: 700; text-align: center; margin: 0 0 .4rem; }
        .modal-desc  { font-size: .86rem; color: var(--muted); text-align: center; line-height: 1.6; margin: 0 0 1.5rem; }
        .modal-actions { display: flex; gap: .75rem; }
        .modal-actions button, .modal-actions a {
            flex: 1; padding: .65rem; border-radius: 10px;
            font-size: .88rem; font-weight: 600; font-family: inherit;
            cursor: pointer; text-align: center;
            transition: all .18s ease; border: none;
        }
        .btn-modal-cancel { background: rgba(17,24,39,0.07); color: var(--dark); }
        .btn-modal-cancel:hover { background: rgba(17,24,39,0.12); }
        .btn-modal-confirm { background: var(--primary); color: var(--white); }
        .btn-modal-confirm:hover { background: var(--primary-dk); }

        /* ── Shared badges ──────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: .3rem;
            border-radius: 50px; font-size: .72rem; font-weight: 600;
            padding: .22rem .65rem; white-space: nowrap;
        }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }

        .badge-admin        { background: rgba(220,38,38,0.10);  color: #DC2626; }
        .badge-cashier      { background: rgba(37,99,235,0.10);  color: #2563EB; }
        .badge-kitchen_staff{ background: rgba(217,119,6,0.10);  color: #D97706; }
        .badge-table_server { background: rgba(22,163,74,0.10);  color: #16A34A; }
        .badge-active       { background: rgba(22,163,74,0.10);  color: #16A34A; }
        .badge-inactive     { background: rgba(107,114,128,0.10);color: #6B7280; }
        .badge-pending      { background: rgba(245,158,11,0.12); color: #D97706; }
        .badge-approved     { background: rgba(22,163,74,0.10);  color: #16A34A; }
        .badge-rejected     { background: rgba(220,38,38,0.10);  color: #DC2626; }

        /* ── Action buttons ─────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .48rem .9rem; border-radius: 9px;
            font-size: .82rem; font-weight: 600; font-family: inherit;
            cursor: pointer; border: none;
            transition: all .18s ease;
        }
        .btn-primary { background: linear-gradient(90deg, var(--primary), #F97316); color: var(--white); box-shadow: 0 4px 14px rgba(220,38,38,0.22); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(220,38,38,0.3); }
        .btn-secondary { background: rgba(17,24,39,0.07); color: var(--dark); }
        .btn-secondary:hover { background: rgba(17,24,39,0.12); }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--dark); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-danger { background: rgba(220,38,38,0.1); color: var(--primary); }
        .btn-danger:hover { background: var(--primary); color: var(--white); }
        .btn-success { background: rgba(22,163,74,0.1); color: #16A34A; }
        .btn-success:hover { background: #16A34A; color: var(--white); }
        .btn-sm { padding: .32rem .65rem; font-size: .76rem; border-radius: 7px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; justify-content: center; border-radius: 8px; }

        /* ── Form controls ──────────────────────────────────── */
        .form-group { margin-bottom: 1.15rem; }
        .form-label { display: flex; align-items: center; gap: .35rem; font-size: .86rem; font-weight: 600; color: var(--dark); margin-bottom: .42rem; }
        .form-label-opt { font-size: .72rem; font-weight: 500; color: var(--muted); background: rgba(107,114,128,0.1); border-radius: 50px; padding: .05rem .4rem; }
        .input-wrap {
            display: flex; align-items: center;
            background: var(--white); border: 1.5px solid rgba(17,24,39,0.1);
            border-radius: 12px; transition: all .22s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .input-wrap:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,38,38,0.08); }
        .input-wrap.has-error { border-color: #EF4444; box-shadow: 0 0 0 4px rgba(239,68,68,0.08); }
        .input-icon { padding: 0 .82rem; color: var(--primary); font-size: .88rem; flex-shrink: 0; min-height: 44px; display: flex; align-items: center; }
        .form-input {
            flex: 1; min-width: 0; border: none; outline: none; background: transparent;
            padding: .68rem .5rem .68rem 0; font-size: .9rem; color: var(--dark); font-family: inherit;
        }
        .form-input:focus, .form-input:focus-visible { outline: none; box-shadow: none; }
        .form-input::placeholder { color: rgba(17,24,39,0.28); }
        .form-select {
            width: 100%; border: 1.5px solid rgba(17,24,39,0.1); border-radius: 12px;
            padding: .68rem .9rem; font-size: .9rem; color: var(--dark);
            font-family: inherit; background: var(--white); outline: none;
            transition: all .22s ease; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%236B7280' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .75rem center; background-size: 16px;
        }
        .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,38,38,0.08); }
        .form-select.has-error { border-color: #EF4444; }
        .field-error { color: #EF4444; font-size: .78rem; margin-top: .3rem; display: flex; align-items: center; gap: .28rem; }
        .toggle-pwd { background: transparent; border: none; outline: none; color: var(--muted); cursor: pointer; padding: 0 .82rem; min-height: 44px; display: flex; align-items: center; font-size: .88rem; transition: color .2s; }
        .toggle-pwd:hover { color: var(--primary); }

        /* ── Mobile ─────────────────────────────────────────── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: none; }
            .main-wrap { margin-left: 0; }
            .hamburger { display: block; }
            .page-content { padding: 1.25rem; }
            .topbar { padding: .9rem 1.25rem; }
        }
        @media (max-width: 520px) {
            .page-content { padding: 1rem; }
        }

    </style>

    @yield('styles')
</head>
<body>

    <!-- ── Sidebar ── -->
    <aside class="sidebar" id="sidebar" aria-label="Admin navigation">
        <div class="sidebar-logo">
            <div class="logo-badge" aria-label="BAB'S RESTO">BR</div>
            <div>
                <div class="logo-text">BAB'S RESTO</div>
                <div class="logo-sub">Admin Panel</div>
            </div>
        </div>

        <nav class="nav-section" aria-label="Main navigation">
            <div class="nav-label">Main</div>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge-high"></i> Dashboard
            </a>

            <div class="nav-label" style="margin-top:.5rem">Management</div>

            <a href="{{ route('users.index') }}"
               class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> User Management
                @php $pending = \App\Models\StaffPasswordResetRequest::pending()->count() @endphp
                @if($pending > 0)
                    <span class="nav-badge">{{ $pending }}</span>
                @endif
            </a>

            <a href="{{ route('password-reset-requests.index') }}"
               class="nav-item {{ request()->routeIs('password-reset-requests.*') ? 'active' : '' }}">
                <i class="fas fa-key"></i> Reset Requests
                @if($pending > 0)
                    <span class="nav-badge">{{ $pending }}</span>
                @endif
            </a>

            <a href="{{ route('menu.index') }}"
               class="nav-item {{ request()->routeIs('menu.*') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i> Menu Catalog
            </a>

            <a href="{{ route('customers.index') }}"
               class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fas fa-user-group"></i> Customer Accounts
            </a>

            <a href="{{ route('inventory.index') }}"
               class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked"></i> Stock Inventory
            </a>

            <a href="{{ route('purchase-orders.index') }}"
               class="nav-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i> Purchase Orders
                @php $draftPos = \App\Models\ProcurementOrder::where('status','draft')->count() @endphp
                @if($draftPos > 0)
                    <span class="nav-badge">{{ $draftPos }}</span>
                @endif
            </a>

            <a href="{{ route('discounts.index') }}"
               class="nav-item {{ request()->routeIs('discounts.*') ? 'active' : '' }}">
                <i class="fas fa-tag"></i> Discounts
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar">{{ auth()->user()->initials }}</div>
                <div>
                    <div class="user-name">{{ Str::limit(auth()->user()->name, 18) }}</div>
                    <div class="user-role">{{ auth()->user()->role_display }}</div>
                </div>
            </div>
            <button type="button" class="btn-logout" onclick="openModal({
                    type: 'warn',
                    iconClass: 'fas fa-right-from-bracket',
                    title: 'Log Out?',
                    desc: 'Are you sure you want to log out of your account?',
                    action: '{{ route('logout') }}',
                    method: 'POST',
                    confirmText: 'Log Out',
                })">
                <i class="fas fa-right-from-bracket"></i> Sign Out
            </button>
        </div>
    </aside>

    <!-- ── Mobile overlay ── -->
    <div id="sidebarOverlay" onclick="closeSidebar()"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:190;backdrop-filter:blur(2px)"></div>

    <!-- ── Main ── -->
    <div class="main-wrap">

        <!-- Top bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="page-title">@yield('page-title', 'Dashboard')</div>
                    <div class="breadcrumb">@yield('breadcrumb')</div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">{{ auth()->user()->initials }}</div>
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <!-- Flash toasts -->
        <div class="toast-wrap" id="toastWrap" aria-live="polite">
            @if(session('success'))
                <div class="toast toast-success">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="toast toast-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if(session('info'))
                <div class="toast toast-info">
                    <i class="fas fa-circle-info"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif
        </div>

        <!-- Page content -->
        <main class="page-content">
            @yield('content')
        </main>
    </div>

    <!-- ── Confirmation modal (shared) ── -->
    <div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
        <div class="modal-box">
            <div class="modal-icon" id="modalIcon"><i id="modalIconInner" class="fas fa-triangle-exclamation"></i></div>
            <h3 class="modal-title" id="modalTitle">Are you sure?</h3>
            <p  class="modal-desc"  id="modalDesc">This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn-modal-cancel" onclick="closeModal()">Cancel</button>
                <form id="modalForm" method="POST" style="flex:1;display:flex">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn-modal-confirm" id="modalConfirmBtn" style="flex:1">Confirm</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Sidebar toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').style.display =
            document.getElementById('sidebar').classList.contains('open') ? 'block' : 'none';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').style.display = 'none';
    }

    // Auto-dismiss toasts
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toast').forEach(function (t) {
            setTimeout(function () {
                t.style.transition = 'opacity .4s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 400);
            }, 4000);
        });
    });

    // Shared confirmation modal
    function openModal(opts) {
        var modal   = document.getElementById('confirmModal');
        var icon    = document.getElementById('modalIcon');
        var iconEl  = document.getElementById('modalIconInner');
        var title   = document.getElementById('modalTitle');
        var desc    = document.getElementById('modalDesc');
        var form    = document.getElementById('modalForm');
        var btn     = document.getElementById('modalConfirmBtn');
        var method  = document.getElementById('modalFormMethod');

        icon.className  = 'modal-icon ' + (opts.type || 'warn');
        iconEl.className= opts.iconClass || 'fas fa-triangle-exclamation';
        title.textContent = opts.title || 'Are you sure?';
        desc.textContent  = opts.desc  || '';
        form.action       = opts.action;
        btn.textContent   = opts.confirmText || 'Confirm';
        btn.className     = 'btn-modal-confirm' + (opts.type === 'danger' ? ' btn-modal-danger' : '');
        if (opts.type === 'danger') btn.style.background = '#DC2626';

        // Set HTTP method
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.value = opts.method || 'PUT';

        modal.classList.add('open');
    }
    function closeModal() {
        document.getElementById('confirmModal').classList.remove('open');
    }
    document.getElementById('confirmModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
    </script>

    @yield('scripts')
</body>
</html>
