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
    .widget-body.has-chart {
        padding: 1.25rem 1.2rem;
        display: block;
        text-align: initial;
    }
    .chart-box { position: relative; height: 220px; }
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

    /* ── Restaurant service control ─────────────────────────── */
    .service-status-bar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
        border-radius: 14px; padding: 1rem 1.3rem;
        margin-bottom: 1.75rem; border: 1.5px solid var(--border);
    }
    .service-status-bar.is-open { background: rgba(22,163,74,0.06); border-color: rgba(22,163,74,0.25); }
    .service-status-bar.is-down { background: rgba(217,119,6,0.07); border-color: rgba(217,119,6,0.3); }
    .service-status-text { display: flex; align-items: center; gap: .75rem; font-size: .86rem; color: var(--dark); }
    .service-status-text .ss-icon {
        width: 40px; height: 40px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; flex-shrink: 0;
    }
    .service-status-actions { display: flex; gap: .6rem; flex-wrap: wrap; }

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
    .anim-7 { animation: fadeUp .5s .48s ease both; }

    /* ── Cancellation review widget ──────────────────────────── */
    .cancel-table-wrap { overflow-x: auto; }
    .cancel-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .cancel-table thead th {
        background: var(--bg); padding: .65rem .9rem; text-align: left;
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
        color: var(--muted); border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .cancel-table td { padding: .7rem .9rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .cancel-table tbody tr:last-child td { border-bottom: none; }
    .cancel-table tbody tr:hover { background: #FAFAFA; }

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

{{-- Restaurant Service Control --}}
<div class="service-status-bar anim-2 {{ $activeDowntime ? 'is-down' : 'is-open' }}">
    <div class="service-status-text">
        <div class="ss-icon" style="background:{{ $activeDowntime ? 'rgba(217,119,6,0.14)' : 'rgba(22,163,74,0.12)' }};color:{{ $activeDowntime ? '#D97706' : '#16A34A' }}">
            <i class="fas {{ $activeDowntime ? 'fa-store-slash' : 'fa-store' }}"></i>
        </div>
        <div>
            @if($activeDowntime)
                <strong>Restaurant temporarily unavailable</strong> — service resumes
                <strong>{{ $activeDowntime->ends_at->format('M d, h:i A') }}</strong>
                ({{ $activeDowntime->ends_at->diffForHumans() }})
                @if($activeDowntime->reason)
                    <div style="color:var(--muted);font-size:.78rem;margin-top:.15rem">{{ $activeDowntime->reason }}</div>
                @endif
                <div style="color:var(--muted);font-size:.75rem;margin-top:.15rem">
                    Customers cannot add items to their cart or place new orders until service resumes.
                </div>
            @else
                <strong>Restaurant is open</strong> — accepting and preparing orders normally.
            @endif
        </div>
    </div>
    <div class="service-status-actions">
        @if($activeDowntime)
            <button type="button" class="btn btn-secondary btn-sm" onclick="openDowntimeModal()">
                <i class="fas fa-pen"></i> Edit
            </button>
            <button type="button" class="btn btn-success btn-sm"
                onclick="openModal({
                    type: 'warn', iconClass: 'fas fa-store',
                    title: 'Resume Service Now?',
                    desc: 'Customers will immediately see the restaurant as open, and new orders will go back to normal prep estimates.',
                    action: '{{ route('downtime.end') }}',
                    method: 'PUT', confirmText: 'Resume Service',
                })">
                <i class="fas fa-play"></i> End Downtime Now
            </button>
        @else
            <button type="button" class="btn btn-secondary btn-sm" style="color:#D97706;border-color:rgba(217,119,6,.3)" onclick="openDowntimeModal()">
                <i class="fas fa-store-slash"></i> Set Downtime
            </button>
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

{{-- Order Cancellation Review (REQ047–REQ050) --}}
<div class="anim-4" style="margin-bottom:1.75rem">
    <div class="section-heading">
        <span><i class="fas fa-ban" style="margin-right:.4rem;color:var(--primary)"></i> Order Cancellation Review</span>
        <a href="{{ route('cancellations.index') }}" style="font-size:.75rem;color:var(--primary);font-weight:600;text-transform:none;letter-spacing:0">
            View All <i class="fas fa-arrow-right" style="font-size:.65rem"></i>
        </a>
    </div>

    <div class="stats-grid" style="margin-bottom:1.1rem">
        <div class="stat-card" style="--card-accent: linear-gradient(90deg, #F59E0B, #F97316)">
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-label">Pending Cancellation Requests</div>
            <div class="stat-value" style="color:#D97706">{{ $pendingCancellations }}</div>
        </div>
        <div class="stat-card" style="--card-accent: linear-gradient(90deg, #16A34A, #059669)">
            <div class="stat-icon" style="background:rgba(22,163,74,0.10);color:#16A34A"><i class="fas fa-circle-check"></i></div>
            <div class="stat-label">Approved Today</div>
            <div class="stat-value" style="color:#16A34A">{{ $approvedCancellationsToday }}</div>
        </div>
        <div class="stat-card" style="--card-accent: linear-gradient(90deg, #DC2626, #F97316)">
            <div class="stat-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)"><i class="fas fa-circle-xmark"></i></div>
            <div class="stat-label">Rejected Today</div>
            <div class="stat-value" style="color:var(--primary)">{{ $rejectedCancellationsToday }}</div>
        </div>
        <div class="stat-card" style="--card-accent: linear-gradient(90deg, #6B7280, #111827)">
            <div class="stat-icon" style="background:rgba(107,114,128,0.12);color:#374151"><i class="fas fa-ban"></i></div>
            <div class="stat-label">Cancelled Orders</div>
            <div class="stat-value" style="color:#374151">{{ $cancelledOrders }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Requests</h2>
        </div>
        @if($recentCancellationRequests->isEmpty())
            <div style="padding:2.5rem 1.5rem;text-align:center;color:var(--muted);font-size:.85rem">
                <i class="fas fa-ban" style="font-size:2rem;display:block;margin-bottom:.65rem;opacity:.25"></i>
                No cancellation requests yet.
            </div>
        @else
            <div class="cancel-table-wrap">
                <table class="cancel-table">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Order Status</th>
                            <th>Request Date</th>
                            <th>Review Status</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentCancellationRequests as $cr)
                        <tr>
                            <td style="font-weight:600;font-size:.78rem">{{ $cr->request_number ?? '#'.$cr->id }}</td>
                            <td>{{ $cr->order?->order_number ?? '—' }}</td>
                            <td>{{ $cr->customer?->full_name ?? 'Unknown' }}</td>
                            <td>
                                @if($cr->order)
                                <span class="badge" style="background:{{ $cr->order->status_color }}1a;color:{{ $cr->order->status_color }}">{{ $cr->order->status_name }}</span>
                                @else — @endif
                            </td>
                            <td style="font-size:.78rem;color:var(--muted);white-space:nowrap">{{ $cr->created_at->format('M d, Y h:i A') }}</td>
                            <td><span class="badge {{ $cr->review_status_badge_class }}">{{ $cr->review_status_label }}</span></td>
                            <td>
                                <div style="display:flex;gap:.35rem;justify-content:flex-end;flex-wrap:wrap">
                                    <a href="{{ route('cancellations.show', $cr) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Review</a>
                                    @if($cr->isPending())
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="openModal({
                                            type: 'warn', iconClass: 'fas fa-circle-check',
                                            title: 'Approve Cancellation?',
                                            desc: 'Are you sure you want to approve this cancellation request?',
                                            action: '{{ route('cancellations.approve', $cr) }}',
                                            method: 'PUT', confirmText: 'Approve',
                                        })"><i class="fas fa-check"></i> Approve</button>
                                    <a href="{{ route('cancellations.show', $cr) }}" class="btn btn-danger btn-sm"><i class="fas fa-xmark"></i> Reject</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Quick Access Modules (REQ007) --}}
<div class="anim-5">
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
<div class="anim-6">
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
                <span style="margin-left:auto;font-size:.7rem;color:var(--muted);font-weight:500">Last 7 days</span>
            </div>
            @if(array_sum($salesChartData) > 0)
                <div class="widget-body has-chart">
                    <div class="chart-box"><canvas id="salesOverviewChart"></canvas></div>
                </div>
            @else
                <div class="widget-body">
                    <div class="widget-placeholder-icon" style="background:rgba(220,38,38,0.08);color:var(--primary)">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <div class="widget-placeholder-title">No Sales Data Yet</div>
                    <div class="widget-placeholder-desc">
                        Sales analytics will appear here once transactions are recorded through the Order and Payment modules.
                    </div>
                    <span class="coming-soon-pill"><i class="fas fa-hourglass-half" style="font-size:.6rem"></i> No Data</span>
                </div>
            @endif
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

{{-- Set Downtime Modal --}}
@php
    $downtimeErrors = $errors->hasAny(['downtime_date', 'downtime_time', 'reason']);
    $selectedDate   = old('downtime_date', $activeDowntime?->ends_at?->format('Y-m-d'));
    $selectedTime   = old('downtime_time', $activeDowntime?->ends_at?->format('H:i'));
@endphp
<div class="modal-overlay {{ $downtimeErrors ? 'open' : '' }}" id="downtimeModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon warn"><i class="fas fa-store-slash"></i></div>
        <h3 class="modal-title">Set Restaurant Downtime</h3>
        <p class="modal-desc">
            Customers will be blocked from adding items to their cart or
            checking out until service resumes.
        </p>
        <form id="downtimeForm" method="POST" action="{{ route('downtime.store') }}" novalidate>
            @csrf
            <label style="display:block;font-size:.78rem;font-weight:700;color:var(--dark);margin-bottom:.35rem">Unavailable until</label>
            <div style="display:flex;gap:.6rem">
                <select name="downtime_date" id="downtimeDate" required onchange="hideDowntimeJsError();updateDowntimePreview()"
                        class="form-select {{ $errors->has('downtime_date') ? 'has-error' : '' }}"
                        style="flex:1.3;height:40px;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:0 .6rem;font-size:.85rem;font-family:inherit;color:var(--dark);background:#fff">
                    <option value="" disabled {{ $selectedDate ? '' : 'selected' }}>Date…</option>
                    @for($i = 0; $i < 14; $i++)
                        @php($optDate = now()->addDays($i))
                        <option value="{{ $optDate->format('Y-m-d') }}" {{ $selectedDate === $optDate->format('Y-m-d') ? 'selected' : '' }}>
                            {{ $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $optDate->format('D')) }} — {{ $optDate->format('M d') }}
                        </option>
                    @endfor
                </select>
                <select name="downtime_time" id="downtimeTime" required onchange="hideDowntimeJsError();updateDowntimePreview()"
                        class="form-select {{ $errors->has('downtime_time') ? 'has-error' : '' }}"
                        style="flex:1;height:40px;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:0 .6rem;font-size:.85rem;font-family:inherit;color:var(--dark);background:#fff">
                    <option value="" disabled {{ $selectedTime ? '' : 'selected' }}>Time…</option>
                    @for($m = 0; $m < 1440; $m += 30)
                        @php($optTime = \Illuminate\Support\Carbon::createFromTime(0, 0)->addMinutes($m))
                        <option value="{{ $optTime->format('H:i') }}" {{ $selectedTime === $optTime->format('H:i') ? 'selected' : '' }}>
                            {{ $optTime->format('g:i A') }}
                        </option>
                    @endfor
                </select>
            </div>
            @error('downtime_date')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            @error('downtime_time')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror

            <div id="downtimePreview" style="display:none;margin-top:.7rem;padding:.55rem .8rem;background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;font-size:.78rem;color:#92400E"></div>
            <div id="downtimeJsError" class="field-error" style="display:none;margin-top:.5rem"><i class="fas fa-circle-exclamation"></i> <span></span></div>

            <label for="downtimeReason" style="display:block;font-size:.78rem;font-weight:700;color:var(--dark);margin:.85rem 0 .35rem">Reason (optional — shown to customers who try to order)</label>
            <textarea name="reason" id="downtimeReason" rows="2" maxlength="255" placeholder="e.g. We're temporarily closed for staff shortage…"
                      class="{{ $errors->has('reason') ? 'has-error' : '' }}"
                      style="width:100%;border:1.5px solid rgba(17,24,39,0.1);border-radius:10px;padding:.55rem .85rem;font-size:.85rem;color:var(--dark);font-family:inherit;resize:vertical;outline:none;min-height:60px">{{ old('reason', $activeDowntime->reason ?? '') }}</textarea>
            <div class="hint" style="font-size:.72rem;color:var(--muted);margin-top:.3rem">If left blank, customers will just see a generic "temporarily unavailable" message.</div>
            @error('reason')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror

            <div class="modal-actions" style="margin-top:1.25rem">
                <button type="button" class="btn-modal-cancel" onclick="closeDowntimeModal()">Cancel</button>
                <button type="submit" class="btn-modal-confirm" style="background:#D97706">
                    <i class="fas fa-store-slash"></i> Confirm Downtime
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Footer note --}}
<div class="anim-7" style="text-align:center;padding:.75rem 0 .25rem;color:var(--muted);font-size:.78rem">
    BAB'S RESTO v1.0 &mdash; Web-Based Online Ordering, POS &amp; Inventory Management System &middot; &copy; {{ date('Y') }}
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
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

var activeDowntimeDate   = @json($activeDowntime?->ends_at?->format('Y-m-d'));
var activeDowntimeTime   = @json($activeDowntime?->ends_at?->format('H:i'));
var activeDowntimeReason = @json($activeDowntime->reason ?? '');

function openDowntimeModal() {
    hideDowntimeJsError();

    // Editing an already-active downtime: show its current date/time so
    // the admin can see what they're changing, not blank dropdowns.
    document.getElementById('downtimeDate').value = activeDowntimeDate || '';
    document.getElementById('downtimeTime').value = activeDowntimeTime || '';
    document.getElementById('downtimeReason').value = activeDowntimeReason;

    updateDowntimePreview();
    document.getElementById('downtimeModal').classList.add('open');
}
function closeDowntimeModal() { document.getElementById('downtimeModal').classList.remove('open'); }
document.getElementById('downtimeModal').addEventListener('click', function (e) {
    if (e.target === this) closeDowntimeModal();
});

function hideDowntimeJsError() { document.getElementById('downtimeJsError').style.display = 'none'; }

function updateDowntimePreview() {
    var dateSelect = document.getElementById('downtimeDate');
    var timeSelect = document.getElementById('downtimeTime');
    var preview    = document.getElementById('downtimePreview');

    if (dateSelect.value && timeSelect.value) {
        var resumeDate = new Date(dateSelect.value + 'T' + timeSelect.value);
        if (! isNaN(resumeDate.getTime())) {
            preview.style.display = 'block';
            preview.innerHTML = '<i class="fas fa-clock"></i> Service will resume <strong>' +
                resumeDate.toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) +
                '</strong>';
            return;
        }
    }
    preview.style.display = 'none';
}

document.getElementById('downtimeForm').addEventListener('submit', function (e) {
    var dateSelect = document.getElementById('downtimeDate');
    var timeSelect = document.getElementById('downtimeTime');
    var jsError    = document.getElementById('downtimeJsError');
    var message    = null;

    if (! dateSelect.value) {
        message = 'Please choose a date.';
    } else if (! timeSelect.value) {
        message = 'Please choose a time.';
    } else if (new Date(dateSelect.value + 'T' + timeSelect.value).getTime() <= Date.now()) {
        message = 'That date and time has already passed — please choose a time in the future.';
    }

    if (message) {
        e.preventDefault();
        jsError.querySelector('span').textContent = message;
        jsError.style.display = 'flex';
    } else {
        hideDowntimeJsError();
    }
});

@if(array_sum($salesChartData) > 0)
new Chart(document.getElementById('salesOverviewChart'), {
    type: 'line',
    data: {
        labels: @json($salesChartLabels),
        datasets: [{
            label: 'Net Sales (₱)',
            data: @json($salesChartData),
            borderColor: '#DC2626',
            backgroundColor: 'rgba(220,38,38,0.08)',
            fill: true, tension: 0.35, pointBackgroundColor: '#DC2626', pointRadius: 3,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
@endif
</script>
@endsection
