@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <span>Overview</span>
@endsection

@section('styles')
<style>
    /* ── Welcome banner ─────────────────────────────────────── */
    .welcome-banner {
        background: linear-gradient(135deg, var(--dark) 0%, #1F2937 55%, #2D1515 100%);
        border-radius: 20px;
        padding: 2rem 2.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 60% 80% at 90% 50%, rgba(220,38,38,0.22) 0%, transparent 60%),
            radial-gradient(ellipse 40% 60% at 10% 80%, rgba(245,158,11,0.10) 0%, transparent 60%);
        pointer-events: none;
    }
    .welcome-text { position: relative; z-index: 1; }
    .welcome-greeting {
        font-size: 1.6rem; font-weight: 800; color: #fff;
        line-height: 1.2; margin: 0 0 .4rem;
    }
    .welcome-greeting span { color: var(--accent); }
    .welcome-meta {
        display: flex; align-items: center; gap: 1.25rem;
        color: rgba(255,255,255,0.55); font-size: .82rem;
    }
    .welcome-meta i { color: var(--accent); }
    .welcome-badge {
        position: relative; z-index: 1;
        background: rgba(255,255,255,0.07);
        border: 1.5px solid rgba(255,255,255,0.12);
        border-radius: 14px;
        padding: 1.1rem 1.5rem;
        text-align: center;
        min-width: 130px;
        flex-shrink: 0;
    }
    .welcome-badge .wb-val {
        font-size: 1.8rem; font-weight: 800; color: #fff; line-height: 1;
    }
    .welcome-badge .wb-label {
        font-size: .72rem; font-weight: 600; color: rgba(255,255,255,0.45);
        text-transform: uppercase; letter-spacing: .07em; margin-top: .3rem;
    }

    /* ── Stats grid ─────────────────────────────────────────── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.35rem 1.4rem;
        border: 1.5px solid var(--border);
        box-shadow: 0 2px 12px rgba(17,24,39,0.05);
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease;
        cursor: default;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(17,24,39,0.10);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 16px 16px 0 0;
        background: var(--card-accent, linear-gradient(90deg, var(--primary), #F97316));
    }
    .stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem; margin-bottom: .9rem;
    }
    .stat-label {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--muted); margin-bottom: .3rem;
    }
    .stat-value {
        font-size: 1.75rem; font-weight: 800; color: var(--dark);
        line-height: 1; margin-bottom: .4rem;
    }
    .stat-note {
        font-size: .75rem; color: var(--muted); line-height: 1.45;
    }
    .stat-placeholder { font-style: italic; }

    /* ── Module cards ───────────────────────────────────────── */
    .section-heading {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--muted);
        margin-bottom: .85rem; padding-bottom: .5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .module-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: .75rem;
        transition: border-color .2s, box-shadow .2s, transform .2s;
        position: relative;
        overflow: hidden;
    }
    .module-card:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 28px rgba(220,38,38,0.12);
        transform: translateY(-2px);
    }
    .module-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 60% 50% at 100% 0%, rgba(220,38,38,0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    .module-icon {
        width: 52px; height: 52px; border-radius: 14px;
        background: linear-gradient(135deg, rgba(220,38,38,0.12), rgba(249,115,22,0.08));
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: var(--primary);
    }
    .module-name {
        font-size: .95rem; font-weight: 700; color: var(--dark);
    }
    .module-desc {
        font-size: .82rem; color: var(--muted); line-height: 1.55; flex: 1;
    }
    .module-btn {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .58rem 1.1rem; border-radius: 10px;
        background: linear-gradient(90deg, var(--primary), #F97316);
        color: #fff; font-size: .82rem; font-weight: 700;
        border: none; cursor: pointer; font-family: inherit;
        text-decoration: none;
        transition: opacity .18s, transform .18s;
        width: fit-content;
    }
    .module-btn:hover { opacity: .9; transform: translateX(2px); color: #fff; }

    /* ── Widget placeholders ────────────────────────────────── */
    .widgets-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .widget-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(17,24,39,0.04);
    }
    .widget-header {
        padding: .9rem 1.2rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .6rem;
    }
    .widget-header-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem;
    }
    .widget-title { font-size: .88rem; font-weight: 700; color: var(--dark); }
    .widget-body {
        padding: 2.5rem 1.5rem;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        text-align: center; min-height: 180px;
    }
    .widget-placeholder-icon {
        width: 56px; height: 56px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; margin: 0 auto .9rem;
    }
    .widget-placeholder-title {
        font-size: .88rem; font-weight: 600; color: var(--dark); margin-bottom: .4rem;
    }
    .widget-placeholder-desc {
        font-size: .78rem; color: var(--muted); line-height: 1.55; max-width: 220px;
    }
    .coming-soon-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        background: rgba(245,158,11,0.10); border: 1px solid rgba(245,158,11,0.25);
        color: #D97706; border-radius: 50px; font-size: .68rem; font-weight: 700;
        padding: .2rem .6rem; margin-top: .75rem; letter-spacing: .04em;
        text-transform: uppercase;
    }

    /* ── System status bar ──────────────────────────────────── */
    .status-bar {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: .9rem 1.3rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        font-size: .8rem;
        color: var(--muted);
        margin-bottom: 1.75rem;
    }
    .status-item { display: flex; align-items: center; gap: .5rem; }
    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        animation: statusPulse 2s ease infinite;
    }
    .status-dot.online  { background: #16A34A; box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
    .status-dot.warning { background: #D97706; }
    @keyframes statusPulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
        50%      { box-shadow: 0 0 0 6px rgba(22,163,74,0); }
    }
    .status-label { font-weight: 600; color: var(--dark); }

    /* ── Fade-in animations ─────────────────────────────────── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: none; }
    }
    .anim-1 { animation: fadeUp .5s ease both; }
    .anim-2 { animation: fadeUp .5s .08s ease both; }
    .anim-3 { animation: fadeUp .5s .16s ease both; }
    .anim-4 { animation: fadeUp .5s .24s ease both; }
    .anim-5 { animation: fadeUp .5s .32s ease both; }
    .anim-6 { animation: fadeUp .5s .40s ease both; }

    /* ── Responsive ─────────────────────────────────────────── */
    @media (max-width: 1100px) {
        .stats-grid    { grid-template-columns: repeat(2, 1fr); }
        .widgets-grid  { grid-template-columns: 1fr; }
    }
    @media (max-width: 700px) {
        .stats-grid    { grid-template-columns: 1fr; }
        .welcome-badge { display: none; }
        .welcome-greeting { font-size: 1.25rem; }
        .status-bar    { gap: 1rem; }
    }
</style>
@endsection

@section('content')

{{-- Welcome Banner --}}
<div class="welcome-banner anim-1">
    <div class="welcome-text">
        <div class="welcome-greeting">
            Welcome back, <span>{{ explode(' ', auth()->user()->name)[0] }}</span>! 👋
        </div>
        <div class="welcome-meta">
            <span><i class="fas fa-calendar-days"></i> <span id="liveDate"></span></span>
            <span><i class="fas fa-clock"></i> <span id="liveTime"></span></span>
        </div>
        <div style="margin-top:.85rem;display:flex;gap:.55rem;flex-wrap:wrap">
            <span style="display:inline-flex;align-items:center;gap:.35rem;background:rgba(22,163,74,0.18);border:1px solid rgba(22,163,74,0.3);color:#86EFAC;border-radius:50px;font-size:.72rem;font-weight:600;padding:.22rem .7rem">
                <span style="width:6px;height:6px;border-radius:50%;background:#4ADE80;display:inline-block"></span>
                System Online
            </span>
            <span style="display:inline-flex;align-items:center;gap:.35rem;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FCD34D;border-radius:50px;font-size:.72rem;font-weight:600;padding:.22rem .7rem">
                <i class="fas fa-wrench" style="font-size:.6rem"></i>
                Modules in Development
            </span>
        </div>
    </div>
    <div style="display:flex;gap:.75rem;position:relative;z-index:1;flex-shrink:0">
        <div class="welcome-badge">
            <div class="wb-val">{{ $totalStaff }}</div>
            <div class="wb-label">Staff Total</div>
        </div>
        <div class="welcome-badge">
            <div class="wb-val">{{ $activeStaff }}</div>
            <div class="wb-label">Active Staff</div>
        </div>
        @if($pendingResets > 0)
        <div class="welcome-badge" style="border-color:rgba(220,38,38,0.35);background:rgba(220,38,38,0.12)">
            <div class="wb-val" style="color:#FCA5A5">{{ $pendingResets }}</div>
            <div class="wb-label">Pending Resets</div>
        </div>
        @endif
    </div>
</div>

{{-- System Status Bar --}}
<div class="status-bar anim-2">
    <div class="status-item">
        <span class="status-dot online"></span>
        <span class="status-label">System Status:</span> Online
    </div>
    <div class="status-item">
        <span class="status-dot online"></span>
        <span class="status-label">User Management:</span> Active
    </div>
    <div class="status-item">
        <span class="status-dot online"></span>
        <span class="status-label">Menu Catalog:</span> Active
    </div>
    <div class="status-item">
        <span class="status-dot online"></span>
        <span class="status-label">Customer Accounts:</span> Active
    </div>
    <div class="status-item">
        <span class="status-dot warning"></span>
        <span class="status-label">Order Module:</span> In Development
    </div>
    <div class="status-item">
        <span class="status-dot warning"></span>
        <span class="status-label">Inventory Module:</span> In Development
    </div>
    <div class="status-item">
        <span class="status-dot warning"></span>
        <span class="status-label">POS Module:</span> In Development
    </div>
</div>

{{-- Summary Stats --}}
<div class="stats-grid anim-3">

    {{-- Today's Sales (REQ004 – placeholder) --}}
    <div class="stat-card" style="--card-accent: linear-gradient(90deg, #DC2626, #F97316)">
        <div class="stat-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)">
            <i class="fas fa-peso-sign"></i>
        </div>
        <div class="stat-label">Today's Sales</div>
        <div class="stat-value" style="color:var(--primary)">₱0.00</div>
        <div class="stat-note stat-placeholder">
            Sales data will appear once the Order and Payment modules are implemented.
        </div>
    </div>

    {{-- Active Orders (REQ005 – placeholder) --}}
    <div class="stat-card" style="--card-accent: linear-gradient(90deg, #2563EB, #06B6D4)">
        <div class="stat-icon" style="background:rgba(37,99,235,0.10);color:#2563EB">
            <i class="fas fa-fire-flame-curved"></i>
        </div>
        <div class="stat-label">Active Orders</div>
        <div class="stat-value" style="color:#2563EB">0</div>
        <div class="stat-note stat-placeholder">
            Order statistics will become available after the Order Management Module is completed.
        </div>
    </div>

    {{-- Completed Orders (REQ005 – placeholder) --}}
    <div class="stat-card" style="--card-accent: linear-gradient(90deg, #16A34A, #059669)">
        <div class="stat-icon" style="background:rgba(22,163,74,0.10);color:#16A34A">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="stat-label">Completed Orders</div>
        <div class="stat-value" style="color:#16A34A">0</div>
        <div class="stat-note stat-placeholder">
            Order statistics will become available after the Order Management Module is completed.
        </div>
    </div>

    {{-- Staff Accounts (REQ007 – real data) --}}
    <div class="stat-card" style="--card-accent: linear-gradient(90deg, #F59E0B, #F97316)">
        <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-label">Staff Accounts</div>
        <div class="stat-value" style="color:#D97706">{{ $totalStaff }}</div>
        <div class="stat-note">
            <span style="color:#16A34A;font-weight:600">{{ $activeStaff }} active</span>
            · {{ $totalStaff - $activeStaff }} inactive
            @if($pendingResets > 0)
                · <a href="{{ route('password-reset-requests.index') }}" style="color:var(--primary);font-weight:600">{{ $pendingResets }} reset pending</a>
            @endif
        </div>
    </div>

</div>

{{-- Quick Access Modules (REQ007) --}}
<div class="anim-4">
    <div class="section-heading">
        <span><i class="fas fa-th-large" style="margin-right:.4rem;color:var(--primary)"></i> Available Modules</span>
        <span style="font-size:.7rem;color:var(--muted);font-weight:500;text-transform:none;letter-spacing:0">
            More modules will appear as development progresses
        </span>
    </div>
    <div class="modules-grid">

        {{-- User Management (only active module) --}}
        <div class="module-card">
            <div class="module-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="module-name">User Management</div>
            <div class="module-desc">
                Create, update, activate, deactivate, and manage internal staff accounts including Administrators, Cashiers, Kitchen Staff, and Table Servers.
            </div>
            <a href="{{ route('users.index') }}" class="module-btn">
                <i class="fas fa-arrow-right"></i> Open Module
            </a>
        </div>

        {{-- Menu Catalog Management --}}
        <div class="module-card">
            <div class="module-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(37,99,235,0.08));color:#7C3AED">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="module-name">Menu Catalog</div>
            <div class="module-desc">
                Add, update, and manage all food and beverage items. Set pricing, categories, availability, and link RTC raw material requirements per serving.
            </div>
            @if($totalMenuItems > 0)
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:-.1rem">
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.18);color:#15803D;border-radius:50px;font-size:.68rem;font-weight:700;padding:.15rem .55rem">
                    <i class="fas fa-check" style="font-size:.5rem"></i> {{ $availableMenuItems }} available
                </span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.18);color:#7C3AED;border-radius:50px;font-size:.68rem;font-weight:700;padding:.15rem .55rem">
                    {{ $totalMenuItems }} total
                </span>
            </div>
            @endif
            <a href="{{ route('menu.index') }}" class="module-btn" style="background:linear-gradient(90deg,#7C3AED,#2563EB)">
                <i class="fas fa-arrow-right"></i> Open Module
            </a>
        </div>

        {{-- Customer Account Management --}}
        <div class="module-card">
            <div class="module-icon" style="background:linear-gradient(135deg,rgba(14,165,233,0.12),rgba(6,182,212,0.08));color:#0EA5E9">
                <i class="fas fa-user-group"></i>
            </div>
            <div class="module-name">Customer Accounts</div>
            <div class="module-desc">
                View and manage all registered customer accounts. Search, filter, activate or deactivate accounts, and monitor registration activity.
            </div>
            @if($totalCustomers > 0)
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:-.1rem">
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.18);color:#15803D;border-radius:50px;font-size:.68rem;font-weight:700;padding:.15rem .55rem">
                    <i class="fas fa-check" style="font-size:.5rem"></i> {{ $activeCustomers }} active
                </span>
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(14,165,233,0.08);border:1px solid rgba(14,165,233,0.18);color:#0369A1;border-radius:50px;font-size:.68rem;font-weight:700;padding:.15rem .55rem">
                    {{ $totalCustomers }} total
                </span>
            </div>
            @endif
            <a href="{{ route('customers.index') }}" class="module-btn" style="background:linear-gradient(90deg,#0EA5E9,#06B6D4)">
                <i class="fas fa-arrow-right"></i> Open Module
            </a>
        </div>

    </div>
</div>

{{-- Dashboard Widgets (placeholder charts) --}}
<div class="anim-5">
    <div class="section-heading">
        <span><i class="fas fa-chart-line" style="margin-right:.4rem;color:var(--primary)"></i> Analytics &amp; Monitoring</span>
    </div>
    <div class="widgets-grid">

        {{-- Sales Overview --}}
        <div class="widget-card">
            <div class="widget-header">
                <div class="widget-header-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)">
                    <i class="fas fa-chart-area"></i>
                </div>
                <div class="widget-title">Sales Overview</div>
            </div>
            <div class="widget-body">
                <div class="widget-placeholder-icon" style="background:rgba(220,38,38,0.08);color:var(--primary)">
                    <i class="fas fa-chart-area"></i>
                </div>
                <div class="widget-placeholder-title">No Sales Data Yet</div>
                <div class="widget-placeholder-desc">
                    Sales analytics will be available once transactions are recorded through the Order and Payment modules.
                </div>
                <span class="coming-soon-pill"><i class="fas fa-hourglass-half" style="font-size:.6rem"></i> Coming Soon</span>
            </div>
        </div>

        {{-- Orders Overview --}}
        <div class="widget-card">
            <div class="widget-header">
                <div class="widget-header-icon" style="background:rgba(37,99,235,0.10);color:#2563EB">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="widget-title">Orders Overview</div>
            </div>
            <div class="widget-body">
                <div class="widget-placeholder-icon" style="background:rgba(37,99,235,0.08);color:#2563EB">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="widget-placeholder-title">No Order Data Available</div>
                <div class="widget-placeholder-desc">
                    Order statistics and trends will appear here after the Order Management Module is completed.
                </div>
                <span class="coming-soon-pill"><i class="fas fa-hourglass-half" style="font-size:.6rem"></i> Coming Soon</span>
            </div>
        </div>

        {{-- Inventory Overview --}}
        <div class="widget-card">
            <div class="widget-header">
                <div class="widget-header-icon" style="background:rgba(22,163,74,0.10);color:#16A34A">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div class="widget-title">Inventory Status</div>
            </div>
            <div class="widget-body">
                <div class="widget-placeholder-icon" style="background:rgba(22,163,74,0.08);color:#16A34A">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div class="widget-placeholder-title">No Inventory Records</div>
                <div class="widget-placeholder-desc">
                    Inventory monitoring will be available after the Inventory Management Module is implemented.
                </div>
                <span class="coming-soon-pill"><i class="fas fa-hourglass-half" style="font-size:.6rem"></i> Coming Soon</span>
            </div>
        </div>

    </div>
</div>

{{-- Footer note --}}
<div class="anim-6" style="text-align:center;padding:.75rem 0 .25rem;color:var(--muted);font-size:.78rem">
    BAB'S RESTO v1.0 &mdash; Web-Based Online Ordering, POS &amp; Inventory Management System &middot; &copy; {{ date('Y') }}
</div>

@endsection

@section('scripts')
<script>
function updateClock() {
    var now  = new Date();
    var opts = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    var d = document.getElementById('liveDate');
    var t = document.getElementById('liveTime');
    if (d) d.textContent = now.toLocaleDateString('en-US', opts);
    if (t) t.textContent = now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}
updateClock();
setInterval(updateClock, 1000);
</script>
@endsection
