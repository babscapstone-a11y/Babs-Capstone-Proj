{{--
    Stock Inventory tabs (same look as the Reports tabs). Shown on the overview and on each
    page a tab opens, with the current one underlined. $active: overview | raw | rtc |
    beverages | low_stock | out_of_stock (none when the full Repurchase List is open).
--}}
@php
    $active = $active ?? null;
    $tabs = [
        'overview'     => ['Overview',       'fa-boxes-stacked',      route('inventory.index'), null],
        'raw'          => ['Raw Meat',       'fa-drumstick-bite',     route('inventory.rtc'), \App\Models\InventoryItem::rtc()->count()],
        'rtc'          => ['RTC Items',      'fa-utensils',           route('inventory.rtc-inventory'), \App\Models\MenuItem::rtcTracked()->count()],
        'beverages'    => ['Beverage Items', 'fa-bottle-water',       route('inventory.beverages'), \App\Models\InventoryItem::beverage()->count()],
        'low_stock'    => ['Low Stock',      'fa-triangle-exclamation', route('inventory.restocking', ['status' => 'low_stock']), \App\Models\InventoryItem::lowStock()->count()],
        'out_of_stock' => ['Out of Stock',   'fa-circle-xmark',       route('inventory.restocking', ['status' => 'out_of_stock']), \App\Models\InventoryItem::outOfStock()->count()],
    ];
@endphp
<style>
    .inv-tabs { display: flex; gap: .25rem; margin-bottom: 1.5rem; border-bottom: 1.5px solid var(--border); overflow-x: auto; overflow-y: hidden; }
    .inv-tab {
        display: inline-flex; align-items: center; gap: .5rem; padding: .7rem 1.1rem;
        border-bottom: 2.5px solid transparent; border-radius: 10px 10px 0 0; white-space: nowrap;
        font-size: .9rem; font-weight: 600; color: var(--muted); text-decoration: none; transition: color .15s, background .15s;
    }
    .inv-tab i { font-size: .9rem; }
    .inv-tab:hover { color: var(--dark); background: rgba(var(--ink-rgb),0.04); }
    .inv-tab.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 700; }
    .inv-tab-count {
        font-size: .72rem; font-weight: 700; min-width: 1.5rem; padding: .08rem .45rem; border-radius: 50px; text-align: center;
        background: rgba(var(--ink-rgb),0.07); color: var(--muted);
    }
    .inv-tab.active .inv-tab-count { background: var(--primary); color: #fff; }
    .inv-tab-count.warn   { background: var(--amber-50); color: var(--amber-600); }
    .inv-tab-count.danger { background: var(--red-50); color: var(--red-600); }
</style>
<nav class="inv-tabs no-print" aria-label="Stock inventory">
    @foreach($tabs as $key => [$label, $icon, $url, $count])
        <a href="{{ $url }}" class="inv-tab {{ $active === $key ? 'active' : '' }}" @if($active === $key) aria-current="page" @endif>
            <i class="fas {{ $icon }}"></i> {{ $label }}
            @if($count !== null)
                <span class="inv-tab-count {{ $active !== $key && $count > 0 ? ($key === 'low_stock' ? 'warn' : ($key === 'out_of_stock' ? 'danger' : '')) : '' }}">{{ $count }}</span>
            @endif
        </a>
    @endforeach
</nav>
