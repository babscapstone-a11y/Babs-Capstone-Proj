@extends('layouts.admin')

@section('title', $menu->menu_name)
@section('page-title', 'Menu Catalog')

@section('breadcrumb')
    <a href="{{ route('menu.index') }}" style="color:var(--primary);text-decoration:none">Menu Catalog</a>
    <i class="fas fa-chevron-right" style="font-size:.65rem;margin:0 .35rem;color:var(--muted)"></i>
    <span>{{ Str::limit($menu->menu_name, 40) }}</span>
@endsection

@section('styles')
<style>
    .show-layout { display: grid; grid-template-columns: 300px 1fr; gap: 1.25rem; align-items: start; }

    /* Left panel */
    .item-profile-card {
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 18px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(17,24,39,0.07);
        position: sticky; top: 90px;
    }
    .item-hero {
        background: linear-gradient(145deg, var(--dark) 0%, #1F2937 60%, #2D1010 100%);
        padding: 1.75rem 1.25rem 1.5rem;
        text-align: center; position: relative; overflow: hidden;
    }
    .item-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse 70% 70% at 80% 30%, rgba(220,38,38,0.2) 0%, transparent 60%);
        pointer-events: none;
    }
    .item-hero-img {
        width: 100px; height: 100px; border-radius: 18px;
        object-fit: cover; border: 3px solid rgba(255,255,255,0.15);
        position: relative; z-index: 1; margin-bottom: .85rem;
    }
    .item-hero-placeholder {
        width: 100px; height: 100px; border-radius: 18px;
        background: rgba(255,255,255,0.08); border: 2px solid rgba(255,255,255,0.12);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: rgba(255,255,255,0.35);
        margin: 0 auto .85rem; position: relative; z-index: 1;
    }
    .item-hero-name {
        font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0 0 .5rem;
        position: relative; z-index: 1;
    }
    .item-hero-price {
        font-size: 1.5rem; font-weight: 800; color: var(--accent);
        position: relative; z-index: 1; margin-bottom: .65rem;
    }
    .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem; border-radius: 50px; font-size: .7rem; font-weight: 700; }
    .badge-active   { background: rgba(22,163,74,0.15);  color: #15803D; border: 1px solid rgba(22,163,74,0.3); }
    .badge-inactive { background: rgba(220,38,38,0.12);  color: #B91C1C; border: 1px solid rgba(220,38,38,0.25); }
    .badge-avail    { background: rgba(22,163,74,0.10);  color: #15803D; border: 1px solid rgba(22,163,74,0.2); }
    .badge-unavail  { background: rgba(107,114,128,0.1); color: #4B5563; border: 1px solid rgba(107,114,128,0.2); }
    .badge-food     { background: rgba(37,99,235,0.10);  color: #2563EB; border: 1px solid rgba(37,99,235,0.2); }
    .badge-beverage { background: rgba(139,92,246,0.10); color: #7C3AED; border: 1px solid rgba(139,92,246,0.2); }

    .item-details { padding: 1.2rem 1.25rem; }
    .detail-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: .6rem 0; border-bottom: 1px solid var(--border);
        font-size: .82rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--muted); font-weight: 600; }
    .detail-value { color: var(--dark); font-weight: 600; text-align: right; max-width: 60%; }

    /* Quick actions */
    .quick-actions { padding: 1rem 1.25rem; border-top: 1px solid var(--border); }
    .quick-actions-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .6rem; }
    .action-btn {
        display: flex; align-items: center; gap: .55rem;
        padding: .62rem .9rem; border-radius: 10px;
        font-size: .83rem; font-weight: 600;
        border: 1.5px solid; cursor: pointer; font-family: inherit;
        text-decoration: none; transition: all .18s; width: 100%;
        margin-bottom: .45rem; box-sizing: border-box;
    }
    .action-btn:last-child { margin-bottom: 0; }
    .action-edit       { color: #D97706; border-color: rgba(245,158,11,0.3); background: rgba(245,158,11,0.06); }
    .action-edit:hover { background: rgba(245,158,11,0.12); }
    .action-deact      { color: #B91C1C; border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .action-deact:hover{ background: rgba(220,38,38,0.12); }
    .action-act        { color: #15803D; border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
    .action-act:hover  { background: rgba(22,163,74,0.12); }

    /* Right content */
    .content-stack { display: flex; flex-direction: column; gap: 1.1rem; }
    .info-card {
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 16px; overflow: hidden;
        box-shadow: 0 2px 10px rgba(17,24,39,0.04);
    }
    .info-card-header {
        padding: .85rem 1.2rem; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .55rem;
        font-size: .82rem; font-weight: 700; color: var(--dark);
    }
    .info-card-header i { color: var(--primary); }
    .info-card-body { padding: 1.2rem 1.25rem; }

    .desc-text { font-size: .88rem; color: var(--dark); line-height: 1.65; }
    .desc-none { font-size: .85rem; color: var(--muted); font-style: italic; }

    /* RTC card */
    .rtc-display {
        background: linear-gradient(135deg, rgba(245,158,11,0.07), rgba(249,115,22,0.04));
        border: 1.5px solid rgba(245,158,11,0.25);
        border-radius: 14px; padding: 1.2rem 1.3rem;
        display: flex; align-items: center; gap: 1.1rem;
    }
    .rtc-icon-big {
        width: 52px; height: 52px; border-radius: 13px;
        background: rgba(245,158,11,0.12); color: #D97706;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }
    .rtc-name  { font-size: 1rem; font-weight: 700; color: var(--dark); }
    .rtc-meta  { font-size: .8rem; color: var(--muted); margin-top: .2rem; }
    .rtc-qty   { margin-left: auto; text-align: right; flex-shrink: 0; }
    .rtc-qty-val { font-size: 1.3rem; font-weight: 800; color: var(--dark); }
    .rtc-qty-unit { font-size: .72rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    .anim-1 { animation: fadeUp .4s ease both; }
    .anim-2 { animation: fadeUp .4s .06s ease both; }

    @media (max-width: 850px) {
        .show-layout { grid-template-columns: 1fr; }
        .item-profile-card { position: static; }
    }
</style>
@endsection

@section('content')

<div class="show-layout">

    {{-- Left: Profile Card --}}
    <div class="anim-1">
        <div class="item-profile-card">
            <div class="item-hero">
                @if($menu->image && Storage::disk('public')->exists($menu->image))
                    <img src="{{ Storage::url($menu->image) }}" class="item-hero-img" alt="{{ $menu->menu_name }}">
                @else
                    <div class="item-hero-placeholder">
                        <i class="fas fa-{{ $menu->item_type === 'beverage' ? 'glass-water' : 'bowl-food' }}"></i>
                    </div>
                @endif
                <div class="item-hero-name">{{ $menu->menu_name }}</div>
                <div class="item-hero-price">₱{{ number_format($menu->price, 2) }}</div>
                <div style="display:flex;gap:.4rem;justify-content:center;flex-wrap:wrap;position:relative;z-index:1">
                    @if($menu->is_active)
                        <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Active</span>
                    @else
                        <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.4rem"></i> Inactive</span>
                    @endif
                    @if($menu->is_available)
                        <span class="badge badge-avail"><i class="fas fa-check" style="font-size:.55rem"></i> Available</span>
                    @else
                        <span class="badge badge-unavail"><i class="fas fa-minus" style="font-size:.55rem"></i> Unavailable</span>
                    @endif
                    @if($menu->item_type === 'food')
                        <span class="badge badge-food"><i class="fas fa-bowl-food" style="font-size:.55rem"></i> Food</span>
                    @else
                        <span class="badge badge-beverage"><i class="fas fa-glass-water" style="font-size:.55rem"></i> Beverage</span>
                    @endif
                </div>
            </div>

            <div class="item-details">
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-hashtag" style="color:var(--primary)"></i> Item ID</span>
                    <span class="detail-value">#{{ str_pad($menu->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-folder" style="color:var(--accent)"></i> Category</span>
                    <span class="detail-value">{{ $menu->category?->category_name ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-plus" style="color:#2563EB"></i> Created</span>
                    <span class="detail-value">{{ $menu->created_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-pen" style="color:#16A34A"></i> Updated</span>
                    <span class="detail-value">{{ $menu->updated_at->format('M d, Y') }}</span>
                </div>
            </div>

            @can('update', $menu)
            <div class="quick-actions">
                <div class="quick-actions-title">Quick Actions</div>
                <a href="{{ route('menu.edit', $menu) }}" class="action-btn action-edit">
                    <i class="fas fa-pen"></i> Edit Item
                </a>
                @can('toggleStatus', $menu)
                <button type="button"
                    onclick="openToggleModal({{ $menu->id }}, '{{ addslashes($menu->menu_name) }}', {{ $menu->is_active ? 'true' : 'false' }})"
                    class="action-btn {{ $menu->is_active ? 'action-deact' : 'action-act' }}">
                    <i class="fas fa-{{ $menu->is_active ? 'ban' : 'check' }}"></i>
                    {{ $menu->is_active ? 'Deactivate Item' : 'Activate Item' }}
                </button>
                @endcan
            </div>
            @endcan
        </div>
    </div>

    {{-- Right: Detail Cards --}}
    <div class="content-stack anim-2">

        {{-- Description --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-align-left"></i> Description
            </div>
            <div class="info-card-body">
                @if($menu->description)
                    <p class="desc-text">{{ $menu->description }}</p>
                @else
                    <p class="desc-none">No description provided.</p>
                @endif
            </div>
        </div>

        {{-- Pricing & Status --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-peso-sign"></i> Pricing & Status
            </div>
            <div class="info-card-body">
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
                    <div style="text-align:center;padding:1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--primary)">₱{{ number_format($menu->price, 2) }}</div>
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-top:.3rem">Selling Price</div>
                    </div>
                    <div style="text-align:center;padding:1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        @if($menu->is_available)
                            <div style="font-size:1.2rem;color:#16A34A"><i class="fas fa-check-circle"></i></div>
                            <div style="font-size:.9rem;font-weight:700;color:#16A34A">Available</div>
                        @else
                            <div style="font-size:1.2rem;color:#6B7280"><i class="fas fa-minus-circle"></i></div>
                            <div style="font-size:.9rem;font-weight:700;color:#6B7280">Unavailable</div>
                        @endif
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-top:.3rem">Availability</div>
                    </div>
                    <div style="text-align:center;padding:1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        @if($menu->is_active)
                            <div style="font-size:1.2rem;color:#16A34A"><i class="fas fa-toggle-on"></i></div>
                            <div style="font-size:.9rem;font-weight:700;color:#16A34A">Active</div>
                        @else
                            <div style="font-size:1.2rem;color:#DC2626"><i class="fas fa-toggle-off"></i></div>
                            <div style="font-size:.9rem;font-weight:700;color:#DC2626">Inactive</div>
                        @endif
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-top:.3rem">Status</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RTC Raw Material --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-drumstick-bite"></i> RTC Raw Material (Ready-to-Cook)
            </div>
            <div class="info-card-body">
                @if($menu->item_type === 'beverage')
                    <div style="display:flex;align-items:center;gap:.65rem;background:rgba(139,92,246,0.06);border:1.5px solid rgba(139,92,246,0.18);border-radius:12px;padding:1rem 1.1rem;font-size:.85rem;color:#6D28D9">
                        <i class="fas fa-glass-water" style="font-size:1.1rem;flex-shrink:0"></i>
                        <div>
                            <strong>Beverage Item</strong>
                            <div style="font-size:.78rem;color:#7C3AED;margin-top:.1rem">Beverages do not require RTC raw material assignment.</div>
                        </div>
                    </div>
                @elseif($menu->rtcItem)
                    <div class="rtc-display">
                        <div class="rtc-icon-big"><i class="fas fa-drumstick-bite"></i></div>
                        <div>
                            <div class="rtc-name">{{ $menu->rtcItem->item_name }}</div>
                            <div class="rtc-meta">Inventory tracked RTC material</div>
                        </div>
                        <div class="rtc-qty">
                            <div class="rtc-qty-val">{{ rtrim(rtrim(number_format($menu->rtc_quantity, 4), '0'), '.') }}</div>
                            <div class="rtc-qty-unit">{{ $menu->rtc_unit }} / serving</div>
                        </div>
                    </div>
                    <div style="margin-top:.85rem;font-size:.78rem;color:var(--muted);background:var(--bg);border-radius:10px;padding:.65rem .9rem;border:1px solid var(--border)">
                        <i class="fas fa-circle-info" style="color:var(--accent);margin-right:.35rem"></i>
                        When an order containing this item is completed, <strong>{{ rtrim(rtrim(number_format($menu->rtc_quantity, 4), '0'), '.') }} {{ $menu->rtc_unit }}</strong>
                        of <strong>{{ $menu->rtcItem->item_name }}</strong> will be automatically deducted from inventory.
                    </div>
                @else
                    <div style="text-align:center;padding:1.75rem;color:var(--muted)">
                        <i class="fas fa-drumstick-bite" style="font-size:2rem;margin-bottom:.75rem;display:block;opacity:.35"></i>
                        <div style="font-size:.88rem;font-weight:600;color:var(--dark);margin-bottom:.35rem">No RTC Material Assigned</div>
                        <div style="font-size:.8rem">No raw material tracking configured for this item.</div>
                        @can('update', $menu)
                        <a href="{{ route('menu.edit', $menu) }}" style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.75rem;font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">
                            <i class="fas fa-plus"></i> Assign RTC material
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        {{-- Timestamps --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-clock"></i> Record Information
            </div>
            <div class="info-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div style="padding:.9rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.4rem">Date Created</div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--dark)">{{ $menu->created_at->format('F d, Y') }}</div>
                        <div style="font-size:.75rem;color:var(--muted)">{{ $menu->created_at->format('h:i A') }}</div>
                    </div>
                    <div style="padding:.9rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.4rem">Last Updated</div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--dark)">{{ $menu->updated_at->format('F d, Y') }}</div>
                        <div style="font-size:.75rem;color:var(--muted)">{{ $menu->updated_at->format('h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Back link --}}
        <div style="display:flex;justify-content:flex-end;gap:.65rem">
            <a href="{{ route('menu.index') }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-weight:600;color:var(--dark);text-decoration:none;background:#fff;transition:border-color .18s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                <i class="fas fa-arrow-left"></i> Back to Catalog
            </a>
            @can('update', $menu)
            <a href="{{ route('menu.edit', $menu) }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;background:linear-gradient(90deg,var(--primary),#F97316);border:none;border-radius:10px;font-size:.83rem;font-weight:700;color:#fff;text-decoration:none;transition:opacity .18s" onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                <i class="fas fa-pen"></i> Edit Item
            </a>
            @endcan
        </div>

    </div>{{-- /content-stack --}}
</div>

{{-- Toggle Modal --}}
<div id="toggleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:18px;padding:2rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="text-align:center;margin-bottom:1.25rem">
            <div id="toggleIcon" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto .9rem"></div>
            <h3 id="toggleTitle" style="font-size:1.05rem;font-weight:700;color:var(--dark);margin:0 0 .5rem"></h3>
            <p id="toggleBody" style="font-size:.85rem;color:var(--muted);margin:0"></p>
        </div>
        <form id="toggleForm" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;gap:.75rem;justify-content:center">
                <button type="button" onclick="closeToggleModal()" style="flex:1;padding:.65rem;border:1.5px solid var(--border);border-radius:10px;background:transparent;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="toggleSubmit" style="flex:1;padding:.65rem;border:none;border-radius:10px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer">Confirm</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openToggleModal(id, name, isActive) {
    var icon   = document.getElementById('toggleIcon');
    var title  = document.getElementById('toggleTitle');
    var body   = document.getElementById('toggleBody');
    var form   = document.getElementById('toggleForm');
    var submit = document.getElementById('toggleSubmit');
    if (isActive) {
        icon.style.background = 'rgba(220,38,38,0.10)'; icon.style.color = '#DC2626';
        icon.innerHTML = '<i class="fas fa-ban"></i>';
        title.textContent = 'Deactivate "' + name + '"?';
        body.textContent  = 'This item will be hidden from the ordering page and POS.';
        submit.style.background = 'linear-gradient(90deg,#DC2626,#F97316)';
        submit.textContent = 'Deactivate';
    } else {
        icon.style.background = 'rgba(22,163,74,0.10)'; icon.style.color = '#16A34A';
        icon.innerHTML = '<i class="fas fa-check"></i>';
        title.textContent = 'Activate "' + name + '"?';
        body.textContent  = 'This item will become visible on the ordering page and POS.';
        submit.style.background = 'linear-gradient(90deg,#16A34A,#059669)';
        submit.textContent = 'Activate';
    }
    form.action = '/menu/' + id + '/toggle-status';
    document.getElementById('toggleModal').style.display = 'flex';
}
function closeToggleModal() {
    document.getElementById('toggleModal').style.display = 'none';
}
document.getElementById('toggleModal').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
