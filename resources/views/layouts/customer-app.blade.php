<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Bab's Resto")</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:    #DC2626;
            --primary-dk: #B91C1C;
            --accent:     #F59E0B;
            --dark:       #111827;
            --text:       #1F2937;
            --muted:      #6B7280;
            --border:     #E5E7EB;
            --bg:         #F3F4F6;
            --white:      #FFFFFF;
            --success:    #16A34A;
            --radius:     14px;
            --shadow-sm:  0 1px 3px rgba(0,0,0,.08);
            --shadow-md:  0 4px 16px rgba(0,0,0,.10);
            --shadow-lg:  0 12px 40px rgba(0,0,0,.14);
            --nav-h:      64px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            padding-top: var(--nav-h);
        }
        a { text-decoration: none; color: inherit; }
        img { display: block; max-width: 100%; }
        button { font-family: inherit; }

        /* ══ NAV ══════════════════════════════════════════ */
        .app-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            height: var(--nav-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .nav-inner {
            max-width: 1280px; margin: 0 auto;
            height: 100%; display: flex; align-items: center; gap: 1rem;
            padding: 0 1.5rem;
        }
        .nav-logo {
            display: flex; align-items: center; gap: .6rem;
            flex-shrink: 0; text-decoration: none;
        }
        .nav-logo-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dk));
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: #fff; flex-shrink: 0;
        }
        .nav-logo-wrap { line-height: 1.2; }
        .nav-logo-text { font-weight: 800; font-size: 1rem; color: var(--dark); }
        .nav-logo-sub  { font-size: .6rem; font-weight: 500; color: var(--muted); letter-spacing: .05em; }

        .nav-spacer { flex: 1; }

        .nav-actions { display: flex; align-items: center; gap: .5rem; }

        /* Cart button */
        .nav-cart-btn {
            position: relative;
            width: 42px; height: 42px; border-radius: 50%;
            background: #FEF2F2; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 1rem;
            transition: background .2s; text-decoration: none;
        }
        .nav-cart-btn:hover { background: #FEE2E2; }
        .nav-cart-badge {
            position: absolute; top: -3px; right: -3px;
            background: var(--primary); color: #fff;
            font-size: .6rem; font-weight: 700;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--white);
            line-height: 1;
        }
        .nav-cart-badge.hidden { display: none; }

        /* Profile dropdown */
        .nav-profile { position: relative; }
        .nav-profile-btn {
            display: flex; align-items: center; gap: .55rem;
            padding: .4rem .85rem .4rem .4rem;
            border-radius: 50px; border: 1.5px solid var(--border);
            background: var(--white); cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
        }
        .nav-profile-btn:hover { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(220,38,38,.08); }
        .nav-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dk));
            color: #fff; font-size: .75rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .nav-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .nav-profile-name { font-size: .83rem; font-weight: 600; color: var(--dark); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .nav-profile-btn i.fa-chevron-down { font-size: .6rem; color: var(--muted); transition: transform .2s; }

        .nav-dropdown {
            position: absolute; top: calc(100% + .5rem); right: 0;
            min-width: 200px; background: var(--white);
            border: 1px solid var(--border); border-radius: 12px;
            box-shadow: var(--shadow-lg); z-index: 500;
            opacity: 0; pointer-events: none;
            transform: translateY(-8px);
            transition: opacity .2s, transform .2s;
            overflow: hidden;
        }
        .nav-dropdown.open { opacity: 1; pointer-events: all; transform: translateY(0); }
        .nav-dropdown-header {
            padding: .85rem 1rem;
            background: #FEF2F2;
            border-bottom: 1px solid var(--border);
        }
        .nav-dropdown-header .ddh-name { font-size: .88rem; font-weight: 700; color: var(--dark); }
        .nav-dropdown-header .ddh-email { font-size: .72rem; color: var(--muted); margin-top: .1rem; }
        .nav-dropdown-item {
            display: flex; align-items: center; gap: .65rem;
            padding: .7rem 1rem; font-size: .84rem; color: var(--text);
            transition: background .15s; cursor: pointer; border: none; background: none;
            width: 100%; text-align: left; font-family: inherit;
        }
        .nav-dropdown-item:hover { background: var(--bg); }
        .nav-dropdown-item i { width: 16px; text-align: center; color: var(--muted); font-size: .85rem; }
        .nav-dropdown-item.danger { color: var(--primary); }
        .nav-dropdown-item.danger i { color: var(--primary); }
        .nav-dropdown-divider { height: 1px; background: var(--border); }

        /* ══ TOAST ════════════════════════════════════════ */
        #toastContainer {
            position: fixed; top: calc(var(--nav-h) + 1rem); right: 1.5rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
            pointer-events: none; max-width: 320px;
        }
        .toast {
            display: flex; align-items: center; gap: .65rem;
            background: var(--dark); color: #fff;
            padding: .8rem 1.1rem; border-radius: 12px;
            font-size: .83rem; font-weight: 500;
            box-shadow: var(--shadow-lg); pointer-events: all;
            animation: toastIn .3s cubic-bezier(.34,1.56,.64,1) both;
        }
        .toast.success { background: var(--success); }
        .toast.error   { background: var(--primary); }
        .toast.info    { background: #3B82F6; }
        @keyframes toastIn  { from { opacity:0; transform:translateX(60px) } to { opacity:1; transform:translateX(0) } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0) }   to { opacity:0; transform:translateX(60px) } }
        .toast.hiding { animation: toastOut .3s ease forwards; }

        /* ══ PAGE WRAPPER ════════════════════════════════ */
        .page-wrap {
            max-width: 1280px; margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        @media (max-width: 680px) {
            .page-wrap { padding: 1.25rem 1rem; }
            .nav-profile-name { display: none; }
        }

        @yield('layout-styles')
    </style>
    @yield('styles')
</head>
<body>

{{-- Navigation --}}
<nav class="app-nav">
    <div class="nav-inner">
        <a href="{{ route('catalog.index') }}" class="nav-logo">
            <div class="nav-logo-icon"><i class="fas fa-utensils"></i></div>
            <div class="nav-logo-wrap">
                <div class="nav-logo-text">BAB'S RESTO</div>
                <div class="nav-logo-sub">ONLINE ORDERING</div>
            </div>
        </a>

        <div class="nav-spacer"></div>

        <div class="nav-actions">
            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="nav-cart-btn" title="View Cart">
                <i class="fas fa-shopping-cart"></i>
                @php $navCartCount = $cartCount ?? 0; @endphp
                <span class="nav-cart-badge {{ $navCartCount > 0 ? '' : 'hidden' }}" id="navCartBadge">{{ $navCartCount > 99 ? '99+' : $navCartCount }}</span>
            </a>

            {{-- Profile dropdown --}}
            <div class="nav-profile">
                <button class="nav-profile-btn" id="profileDropBtn" onclick="toggleDropdown()">
                    <div class="nav-avatar">
                        @php $navCustomer = auth()->user()->customer; @endphp
                        @if($navCustomer?->profile_picture_url)
                            <img src="{{ $navCustomer->profile_picture_url }}" alt="">
                        @else
                            {{ $navCustomer?->initials ?? auth()->user()->name[0] ?? 'U' }}
                        @endif
                    </div>
                    <span class="nav-profile-name">{{ $navCustomer?->first_name ?? auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="nav-dropdown" id="profileDropdown">
                    <div class="nav-dropdown-header">
                        <div class="ddh-name">{{ $navCustomer?->full_name ?? auth()->user()->name }}</div>
                        <div class="ddh-email">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('account.index') }}" class="nav-dropdown-item">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="{{ route('catalog.index') }}" class="nav-dropdown-item">
                        <i class="fas fa-utensils"></i> Browse Menu
                    </a>
                    <div class="nav-dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-dropdown-item danger">
                            <i class="fas fa-right-from-bracket"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Toast container --}}
<div id="toastContainer" aria-live="polite" aria-atomic="true"></div>

{{-- Flash toasts --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast({{ Js::from(session('success')) }}, 'success'));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast({{ Js::from(session('error')) }}, 'error'));</script>
@endif
@if(session('info'))
<script>document.addEventListener('DOMContentLoaded', () => showToast({{ Js::from(session('info')) }}, 'info'));</script>
@endif

@yield('content')

<script>
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

function toggleDropdown() {
    const dd = document.getElementById('profileDropdown');
    dd.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const btn = document.getElementById('profileDropBtn');
    const dd  = document.getElementById('profileDropdown');
    if (dd && !btn.contains(e.target) && !dd.contains(e.target)) {
        dd.classList.remove('open');
    }
});
</script>
@yield('scripts')
</body>
</html>
