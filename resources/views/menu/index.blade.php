@extends('layouts.admin')

@section('title', 'Menu Catalog')
@section('page-title', 'Menu Catalog')

@section('breadcrumb')
    <span>All Items</span>
@endsection

@section('styles')
<style>
    /* ── Stats row ──────────────────────────────────────────── */
    .menu-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: .85rem;
        margin-bottom: 1.5rem;
    }
    .menu-stat {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        display: flex; align-items: center; gap: .8rem;
        box-shadow: 0 2px 8px rgba(17,24,39,0.04);
    }
    .menu-stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0;
    }
    .menu-stat-val  { font-size: 1.4rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .menu-stat-lbl  { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }

    /* ── Filters ────────────────────────────────────────────── */
    .filter-bar {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 1rem 1.2rem;
        margin-bottom: 1.25rem;
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    }
    .filter-bar input, .filter-bar select {
        height: 38px; padding: 0 .8rem;
        border: 1.5px solid var(--border);
        border-radius: 9px; font-size: .83rem;
        font-family: inherit; color: var(--dark);
        background: var(--bg); outline: none;
        transition: border-color .18s;
    }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--primary); }
    .filter-bar input[type=text] { min-width: 200px; flex: 1; }
    .filter-bar .btn-filter {
        height: 38px; padding: 0 1rem;
        background: var(--primary); color: #fff;
        border: none; border-radius: 9px;
        font-size: .83rem; font-weight: 600;
        font-family: inherit; cursor: pointer;
        display: flex; align-items: center; gap: .4rem;
    }
    .filter-bar .btn-reset {
        height: 38px; padding: 0 .9rem;
        background: transparent; color: var(--muted);
        border: 1.5px solid var(--border); border-radius: 9px;
        font-size: .83rem; font-family: inherit; cursor: pointer;
        display: flex; align-items: center; gap: .4rem;
    }
    .filter-bar .btn-reset:hover { border-color: var(--primary); color: var(--primary); }

    /* ── Table ──────────────────────────────────────────────── */
    .menu-table-wrap {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(17,24,39,0.05);
    }
    .menu-table-header {
        padding: 1rem 1.3rem;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid var(--border);
    }
    .menu-table-header h2 { font-size: .95rem; font-weight: 700; color: var(--dark); margin: 0; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    thead th {
        background: var(--bg); padding: .65rem .9rem;
        text-align: left; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--muted); white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #FAFAFA; }
    td { padding: .7rem .9rem; color: var(--dark); vertical-align: middle; }

    .menu-thumb {
        width: 46px; height: 46px; border-radius: 10px;
        object-fit: cover; border: 1.5px solid var(--border);
        flex-shrink: 0;
    }
    .menu-thumb-placeholder {
        width: 46px; height: 46px; border-radius: 10px;
        background: var(--bg); border: 1.5px solid var(--border);
        display: flex; align-items: center; justify-content: center;
        color: var(--muted); font-size: .8rem; flex-shrink: 0;
    }
    .item-name-cell { display: flex; align-items: center; gap: .65rem; }
    .item-name-val  { font-weight: 600; color: var(--dark); }
    .item-cat-val   { font-size: .75rem; color: var(--muted); margin-top: .1rem; }

    /* Badges */
    .badge-type-food      { background:rgba(37,99,235,0.10); color:#2563EB; border:1px solid rgba(37,99,235,0.2); }
    .badge-type-beverage  { background:rgba(139,92,246,0.10); color:#7C3AED; border:1px solid rgba(139,92,246,0.2); }
    .badge-avail-yes      { background:rgba(22,163,74,0.10);  color:#15803D; border:1px solid rgba(22,163,74,0.2); }
    .badge-avail-no       { background:rgba(107,114,128,0.10);color:#4B5563; border:1px solid rgba(107,114,128,0.2); }
    .badge-active         { background:rgba(22,163,74,0.10);  color:#15803D; border:1px solid rgba(22,163,74,0.2); }
    .badge-inactive       { background:rgba(220,38,38,0.10);  color:#B91C1C; border:1px solid rgba(220,38,38,0.2); }
    .badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .22rem .65rem; border-radius: 50px;
        font-size: .7rem; font-weight: 700; white-space: nowrap;
    }

    /* Action buttons */
    .action-group { display: flex; align-items: center; gap: .35rem; flex-wrap: nowrap; }
    .btn-action {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .32rem .7rem; border-radius: 8px;
        font-size: .75rem; font-weight: 600;
        border: 1.5px solid; cursor: pointer;
        font-family: inherit; transition: all .18s;
        text-decoration: none; white-space: nowrap;
    }
    .btn-view     { color:#2563EB; border-color:rgba(37,99,235,0.3); background:rgba(37,99,235,0.06); }
    .btn-view:hover { background:rgba(37,99,235,0.12); color:#1D4ED8; }
    .btn-edit     { color:#D97706; border-color:rgba(245,158,11,0.3); background:rgba(245,158,11,0.06); }
    .btn-edit:hover { background:rgba(245,158,11,0.12); color:#B45309; }
    .btn-deactivate { color:#B91C1C; border-color:rgba(220,38,38,0.3); background:rgba(220,38,38,0.06); }
    .btn-deactivate:hover { background:rgba(220,38,38,0.12); }
    .btn-activate   { color:#15803D; border-color:rgba(22,163,74,0.3); background:rgba(22,163,74,0.06); }
    .btn-activate:hover { background:rgba(22,163,74,0.12); }

    /* Price */
    .price-val { font-weight: 700; color: var(--primary); }

    /* RTC */
    .rtc-cell  { font-size: .78rem; color: var(--dark); }
    .rtc-none  { color: var(--muted); font-style: italic; font-size: .75rem; }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 3.5rem 2rem;
        color: var(--muted);
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
    .empty-state p  { font-size: .85rem; margin-bottom: 1.25rem; }

    @keyframes fadeUp {
        from { opacity:0; transform:translateY(16px); }
        to   { opacity:1; transform:none; }
    }
    .anim-1 { animation: fadeUp .45s ease both; }
    .anim-2 { animation: fadeUp .45s .08s ease both; }
    .anim-3 { animation: fadeUp .45s .16s ease both; }

    @media (max-width:900px) {
        .menu-stats { grid-template-columns: repeat(3,1fr); }
    }
    @media (max-width:600px) {
        .menu-stats { grid-template-columns: repeat(2,1fr); }
    }
</style>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="menu-stats anim-1">
    <div class="menu-stat">
        <div class="menu-stat-icon" style="background:rgba(220,38,38,0.10);color:var(--primary)">
            <i class="fas fa-utensils"></i>
        </div>
        <div>
            <div class="menu-stat-val">{{ $totalItems }}</div>
            <div class="menu-stat-lbl">Total Items</div>
        </div>
    </div>
    <div class="menu-stat">
        <div class="menu-stat-icon" style="background:rgba(22,163,74,0.10);color:#16A34A">
            <i class="fas fa-toggle-on"></i>
        </div>
        <div>
            <div class="menu-stat-val" style="color:#16A34A">{{ $activeItems }}</div>
            <div class="menu-stat-lbl">Active</div>
        </div>
    </div>
    <div class="menu-stat">
        <div class="menu-stat-icon" style="background:rgba(37,99,235,0.10);color:#2563EB">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <div class="menu-stat-val" style="color:#2563EB">{{ $availableItems }}</div>
            <div class="menu-stat-lbl">Available</div>
        </div>
    </div>
    <div class="menu-stat">
        <div class="menu-stat-icon" style="background:rgba(139,92,246,0.10);color:#7C3AED">
            <i class="fas fa-bowl-food"></i>
        </div>
        <div>
            <div class="menu-stat-val" style="color:#7C3AED">{{ $foodCount }}</div>
            <div class="menu-stat-lbl">Food Items</div>
        </div>
    </div>
    <div class="menu-stat">
        <div class="menu-stat-icon" style="background:rgba(14,165,233,0.10);color:#0EA5E9">
            <i class="fas fa-glass-water"></i>
        </div>
        <div>
            <div class="menu-stat-val" style="color:#0EA5E9">{{ $beverageCount }}</div>
            <div class="menu-stat-lbl">Beverages</div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('menu.index') }}" class="filter-bar anim-2">
    <input type="text" name="search" placeholder="Search menu items…" value="{{ request('search') }}">

    <select name="category_id">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                {{ $cat->category_name }}
            </option>
        @endforeach
    </select>

    <select name="item_type">
        <option value="">Food & Beverage</option>
        <option value="food"     @selected(request('item_type') === 'food')>Food Only</option>
        <option value="beverage" @selected(request('item_type') === 'beverage')>Beverages Only</option>
    </select>

    <select name="is_active">
        <option value="">All Statuses</option>
        <option value="1" @selected(request('is_active') === '1')>Active</option>
        <option value="0" @selected(request('is_active') === '0')>Inactive</option>
    </select>

    <select name="is_available">
        <option value="">All Availability</option>
        <option value="1" @selected(request('is_available') === '1')>Available</option>
        <option value="0" @selected(request('is_available') === '0')>Unavailable</option>
    </select>

    <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
    <a href="{{ route('menu.index') }}" class="btn-reset"><i class="fas fa-rotate-left"></i> Reset</a>
</form>

{{-- Table --}}
<div class="menu-table-wrap anim-3">
    <div class="menu-table-header">
        <h2><i class="fas fa-list" style="color:var(--primary);margin-right:.4rem"></i>
            Menu Items
            @if($menuItems->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $menuItems->total() }})</span>
            @endif
        </h2>
        @can('create', App\Models\MenuItem::class)
        <a href="{{ route('menu.create') }}" style="display:inline-flex;align-items:center;gap:.4rem;background:linear-gradient(90deg,var(--primary),#F97316);color:#fff;padding:.5rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:700;text-decoration:none;transition:opacity .18s" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <i class="fas fa-plus"></i> Add Menu Item
        </a>
        @endcan
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:56px">Image</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>RTC Material</th>
                    <th>RTC Qty</th>
                    <th>Availability</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                <tr>
                    <td>
                        @if($item->image && Storage::disk('public')->exists($item->image))
                            <img src="{{ Storage::url($item->image) }}" class="menu-thumb" alt="{{ $item->menu_name }}">
                        @else
                            <div class="menu-thumb-placeholder"><i class="fas fa-image"></i></div>
                        @endif
                    </td>
                    <td>
                        <div class="item-name-cell">
                            <div>
                                <div class="item-name-val">{{ $item->menu_name }}</div>
                                @if($item->description)
                                    <div class="item-cat-val">{{ Str::limit($item->description, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $item->category?->category_name ?? '—' }}</td>
                    <td>
                        @if($item->item_type === 'food')
                            <span class="badge badge-type-food"><i class="fas fa-bowl-food"></i> Food</span>
                        @else
                            <span class="badge badge-type-beverage"><i class="fas fa-glass-water"></i> Beverage</span>
                        @endif
                    </td>
                    <td class="price-val">₱{{ number_format($item->price, 2) }}</td>
                    <td>
                        @if($item->rtcItem)
                            <div class="rtc-cell">
                                <div style="font-weight:600">{{ $item->rtcItem->item_name }}</div>
                                <div style="font-size:.72rem;color:var(--muted)">{{ $item->rtcItem->unit }}</div>
                            </div>
                        @else
                            <span class="rtc-none">{{ $item->item_type === 'beverage' ? 'N/A' : '—' }}</span>
                        @endif
                    </td>
                    <td>
                        @if($item->rtc_quantity)
                            <span class="rtc-cell">{{ rtrim(rtrim(number_format($item->rtc_quantity, 4), '0'), '.') }}
                                <span style="color:var(--muted);font-size:.72rem">{{ $item->rtc_unit }}</span>
                            </span>
                        @else
                            <span class="rtc-none">—</span>
                        @endif
                    </td>
                    <td>
                        @if($item->is_available)
                            <span class="badge badge-avail-yes"><i class="fas fa-circle" style="font-size:.45rem"></i> Available</span>
                        @else
                            <span class="badge badge-avail-no"><i class="fas fa-circle" style="font-size:.45rem"></i> Unavailable</span>
                        @endif
                    </td>
                    <td>
                        @if($item->is_active)
                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.45rem"></i> Active</span>
                        @else
                            <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.45rem"></i> Inactive</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;font-size:.75rem;color:var(--muted)">
                        {{ $item->created_at->format('M d, Y') }}
                    </td>
                    <td>
                        <div class="action-group" style="justify-content:flex-end">
                            <a href="{{ route('menu.show', $item) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @can('update', $item)
                            <a href="{{ route('menu.edit', $item) }}" class="btn-action btn-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @endcan
                            @can('toggleStatus', $item)
                            <button type="button"
                                class="btn-action {{ $item->is_active ? 'btn-deactivate' : 'btn-activate' }}"
                                onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->menu_name) }}', {{ $item->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-{{ $item->is_active ? 'ban' : 'check' }}"></i>
                                {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <i class="fas fa-utensils" style="color:var(--muted)"></i>
                            <h3>No Menu Items Found</h3>
                            <p>{{ request()->hasAny(['search','category_id','item_type','is_active','is_available']) ? 'Try adjusting your filters.' : 'Start by adding your first menu item.' }}</p>
                            @can('create', App\Models\MenuItem::class)
                            <a href="{{ route('menu.create') }}" style="display:inline-flex;align-items:center;gap:.4rem;background:linear-gradient(90deg,var(--primary),#F97316);color:#fff;padding:.55rem 1.2rem;border-radius:10px;font-size:.83rem;font-weight:700;text-decoration:none">
                                <i class="fas fa-plus"></i> Add First Menu Item
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($menuItems->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $menuItems->firstItem() }}–{{ $menuItems->lastItem() }} of {{ $menuItems->total() }} items
        </span>
        {{ $menuItems->links() }}
    </div>
    @endif
</div>

{{-- Toggle Status Modal --}}
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
    var modal  = document.getElementById('toggleModal');
    var icon   = document.getElementById('toggleIcon');
    var title  = document.getElementById('toggleTitle');
    var body   = document.getElementById('toggleBody');
    var form   = document.getElementById('toggleForm');
    var submit = document.getElementById('toggleSubmit');

    if (isActive) {
        icon.style.background = 'rgba(220,38,38,0.10)';
        icon.style.color      = '#DC2626';
        icon.innerHTML        = '<i class="fas fa-ban"></i>';
        title.textContent     = 'Deactivate "' + name + '"?';
        body.textContent      = 'This item will be hidden from the ordering page and cannot be selected in POS. It will remain in the database for historical reports.';
        submit.style.background = 'linear-gradient(90deg,#DC2626,#F97316)';
        submit.textContent    = 'Deactivate';
    } else {
        icon.style.background = 'rgba(22,163,74,0.10)';
        icon.style.color      = '#16A34A';
        icon.innerHTML        = '<i class="fas fa-check"></i>';
        title.textContent     = 'Activate "' + name + '"?';
        body.textContent      = 'This item will become visible on the ordering page and available in POS.';
        submit.style.background = 'linear-gradient(90deg,#16A34A,#059669)';
        submit.textContent    = 'Activate';
    }

    form.action = '/menu/' + id + '/toggle-status';
    modal.style.display = 'flex';
}
function closeToggleModal() {
    document.getElementById('toggleModal').style.display = 'none';
}
document.getElementById('toggleModal').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
