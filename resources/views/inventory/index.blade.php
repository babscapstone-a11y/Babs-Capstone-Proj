@extends('layouts.admin')

@section('title', 'Stock Inventory – Dashboard')

@section('styles')
<style>
.inv-page { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; }
.page-title { font-size:1.5rem; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:.65rem; }
.page-title i { color:var(--primary); }
.page-sub { font-size:.83rem; color:var(--muted); margin-top:.25rem; }
.header-actions { display:flex; gap:.6rem; flex-wrap:wrap; }

/* stat cards */
.stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1.25rem; margin-bottom:2rem; }
.stat-card { background:#fff; border-radius:16px; padding:1.25rem 1.4rem; border:1px solid var(--border); box-shadow:var(--shadow-sm); position:relative; overflow:hidden; display:block; color:inherit; text-decoration:none; transition:transform .15s, box-shadow .15s; }
.stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(17,24,39,0.08); color:inherit; }
.stat-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; border-radius:0 16px 0 80px; opacity:.07; }
.stat-card.blue::before  { background:#3B82F6; }
.stat-card.green::before { background:#16A34A; }
.stat-card.amber::before { background:#F59E0B; }
.stat-card.red::before   { background:#DC2626; }
.stat-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:.9rem; }
.stat-icon.blue  { background:#EFF6FF; color:#2563EB; }
.stat-icon.green { background:#F0FDF4; color:#16A34A; }
.stat-icon.amber { background:#FFFBEB; color:#D97706; }
.stat-icon.red   { background:#FEF2F2; color:#DC2626; }
.stat-value { font-size:1.9rem; font-weight:800; color:var(--dark); line-height:1; }
.stat-label { font-size:.78rem; font-weight:600; color:var(--muted); margin-top:.3rem; text-transform:uppercase; letter-spacing:.04em; }

/* section */
.section-card { background:#fff; border-radius:16px; border:1px solid var(--border); box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:2rem; }
.section-hd { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.5rem; border-bottom:1px solid var(--border); }
.section-hd h2 { font-size:.95rem; font-weight:700; color:var(--dark); display:flex; align-items:center; gap:.5rem; }
.section-hd h2 i { color:var(--primary); }
.section-hd a { font-size:.8rem; font-weight:600; color:var(--primary); text-decoration:none; }
.section-hd a:hover { text-decoration:underline; }

/* recent activity */
.activity-list { padding:.5rem 0; }
.activity-item { display:flex; align-items:center; gap:.9rem; padding:.7rem 1.5rem; border-bottom:1px solid #F3F4F6; }
.activity-item:last-child { border-bottom:none; }
.activity-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
.activity-icon.green { background:#F0FDF4; color:#16A34A; }
.activity-icon.blue  { background:#EFF6FF; color:#2563EB; }
.activity-icon.amber { background:#FFFBEB; color:#D97706; }
.activity-body { flex:1; min-width:0; }
.activity-name { font-size:.83rem; font-weight:600; color:var(--dark); }
.activity-meta { font-size:.75rem; color:var(--muted); margin-top:.1rem; }
.activity-qty  { font-size:.83rem; font-weight:700; color:var(--primary); flex-shrink:0; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
@media(max-width:900px) { .two-col { grid-template-columns:1fr; } }

.btn { display:inline-flex; align-items:center; gap:.45rem; padding:.55rem 1.1rem; border-radius:10px; font-size:.83rem; font-weight:600; font-family:inherit; cursor:pointer; border:none; transition:all .18s; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-dk, #B91C1C); }
.btn-outline { background:#fff; border:1.5px solid var(--border); color:var(--dark); }
.btn-outline:hover { border-color:var(--primary); color:var(--primary); }
.btn-amber { background:#F59E0B; color:#fff; }
.btn-amber:hover { background:#D97706; }

/* Add Item modal */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.5); backdrop-filter:blur(3px); z-index:1000; display:none; align-items:center; justify-content:center; padding:1rem; }
.modal-backdrop.open { display:flex; animation:fadeIn .2s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.modal { background:#fff; border-radius:20px; width:100%; max-width:460px; box-shadow:0 24px 64px rgba(0,0,0,.18); animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both; }
@keyframes slideUp { from{opacity:0;transform:scale(.9) translateY(20px)} to{opacity:1;transform:scale(1) translateY(0)} }
.modal-hd { display:flex; align-items:center; justify-content:space-between; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); }
.modal-hd h3 { font-size:1rem; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:.5rem; margin:0; }
.modal-hd h3 i { color:var(--primary); }
.modal-close-btn { width:32px; height:32px; border-radius:8px; border:none; background:#F8FAFC; cursor:pointer; font-size:.9rem; color:var(--muted); display:flex; align-items:center; justify-content:center; }
.modal-close-btn:hover { background:#fee2e2; color:var(--primary); }
.modal-body { padding:1.5rem; }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; gap:.6rem; justify-content:flex-end; }
.field { margin-bottom:1.1rem; }
.field label { display:block; font-size:.8rem; font-weight:600; color:var(--dark); margin-bottom:.35rem; }
.field input, .field select { width:100%; padding:.6rem .9rem; border:1.5px solid var(--border); border-radius:10px; font-size:.84rem; font-family:inherit; color:var(--dark); outline:none; background:#fff; transition:border-color .18s; box-sizing:border-box; }
.field input:focus, .field select:focus { border-color:var(--primary); }
.type-toggle { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; margin-bottom:1.25rem; }
.type-option { display:flex; align-items:center; gap:.6rem; padding:.75rem .9rem; border:1.5px solid var(--border); border-radius:12px; cursor:pointer; transition:all .18s; }
.type-option i { font-size:1.05rem; color:var(--muted); }
.type-option .label { font-weight:700; font-size:.85rem; color:var(--dark); }
.type-option input[type=radio] { accent-color:var(--primary); }
.type-option.active { border-color:var(--primary); background:rgba(220,38,38,0.04); }
.type-option.active i { color:var(--primary); }
.error-msg { background:#FEF2F2; border:1.5px solid #FECACA; color:#B91C1C; border-radius:10px; padding:.7rem 1rem; font-size:.82rem; margin-bottom:1.1rem; }
.preview-box { background:#F8FAFC; border-radius:12px; padding:1rem 1.2rem; margin:.75rem 0 0; border:1px solid var(--border); }
.preview-row { display:flex; justify-content:space-between; font-size:.82rem; margin-bottom:.3rem; color:var(--dark); }
.preview-row:last-child { margin-bottom:0; font-weight:700; color:var(--primary); }
</style>
@endsection

@section('content')
<div class="inv-page">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-boxes-stacked"></i> Stock Inventory</div>
            <div class="page-sub">Monitor and manage RTC raw meat and beverage inventory</div>
        </div>
        <div class="header-actions">
            <a href="{{ route('inventory.restocking') }}" class="btn btn-amber">
                <i class="fas fa-cart-shopping"></i> Repurchase List
            </a>
            <button type="button" class="btn btn-outline" onclick="openLocalModal('addItemModal')">
                <i class="fas fa-plus"></i> Add Item
            </button>
            <button type="button" class="btn btn-primary" onclick="openLocalModal('stockInModal')">
                <i class="fas fa-plus"></i> Stock In
            </button>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="stat-grid">
        <a href="{{ route('inventory.rtc') }}" class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-drumstick-bite"></i></div>
            <div class="stat-value">{{ $totalRtc }}</div>
            <div class="stat-label">Total Raw Meat</div>
        </a>
        <a href="{{ route('inventory.rtc-inventory') }}" class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-utensils"></i></div>
            <div class="stat-value">{{ $totalRtcProducts }}</div>
            <div class="stat-label">Total RTC Items</div>
        </a>
        <a href="{{ route('inventory.beverages') }}" class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-bottle-water"></i></div>
            <div class="stat-value">{{ $totalBeverage }}</div>
            <div class="stat-label">Beverage Items</div>
        </a>
        <a href="{{ route('inventory.restocking', ['status' => 'low_stock']) }}" class="stat-card amber">
            <div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="stat-value">{{ $lowStock }}</div>
            <div class="stat-label">Low Stock</div>
        </a>
        <a href="{{ route('inventory.restocking', ['status' => 'out_of_stock']) }}" class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div>
            <div class="stat-value">{{ $outOfStock }}</div>
            <div class="stat-label">Out of Stock</div>
        </a>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;">
        <i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ── Recent Activity ── --}}
    <div class="two-col">
        {{-- Recent Stock-Ins --}}
        <div class="section-card">
            <div class="section-hd">
                <h2><i class="fas fa-arrow-down-to-bracket"></i> Recent Stock-In</h2>
                <a href="{{ route('inventory.stock-in.index') }}">View All →</a>
            </div>
            <div class="activity-list">
                @forelse($recentStockIns as $tx)
                <div class="activity-item">
                    <div class="activity-icon green"><i class="fas fa-plus"></i></div>
                    <div class="activity-body">
                        <div class="activity-name">{{ $tx->inventoryItem?->item_name ?? '—' }}</div>
                        <div class="activity-meta">{{ $tx->purchase_date?->format('M d, Y') }} &bull; {{ $tx->recorder?->name ?? 'Admin' }}</div>
                    </div>
                    <div class="activity-qty">+{{ number_format($tx->quantity_purchased, 2) }} {{ $tx->unit }}</div>
                </div>
                @empty
                <div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.83rem;">No stock-in transactions yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Conversions --}}
        <div class="section-card">
            <div class="section-hd">
                <h2><i class="fas fa-arrows-rotate"></i> Recent Conversions</h2>
                <a href="{{ route('inventory.conversions.index') }}">View All →</a>
            </div>
            <div class="activity-list">
                @forelse($recentConversions as $log)
                <div class="activity-item">
                    <div class="activity-icon blue"><i class="fas fa-arrows-rotate"></i></div>
                    <div class="activity-body">
                        <div class="activity-name">{{ $log->menuItem?->menu_name ?? '—' }}</div>
                        <div class="activity-meta">from {{ $log->inventoryItem?->item_name ?? '—' }} &bull; {{ $log->created_at->format('M d, Y') }} &bull; {{ $log->converter?->name ?? 'Admin' }}</div>
                    </div>
                    <div class="activity-qty">{{ number_format($log->rtc_units_produced, 0) }} srv.</div>
                </div>
                @empty
                <div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.83rem;">No conversions yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- Add Item modal --}}
<div class="modal-backdrop" id="addItemModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-plus"></i> Add Inventory Item</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('addItemModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.store') }}">
            @csrf
            <div class="modal-body">
                @if($errors->any())
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <div class="type-toggle">
                    <label class="type-option {{ old('item_type', 'rtc') === 'rtc' ? 'active' : '' }}" data-type="rtc">
                        <input type="radio" name="item_type" value="rtc" {{ old('item_type', 'rtc') === 'rtc' ? 'checked' : '' }}>
                        <i class="fas fa-drumstick-bite"></i>
                        <span class="label">Raw Meat</span>
                    </label>
                    <label class="type-option {{ old('item_type') === 'beverage' ? 'active' : '' }}" data-type="beverage">
                        <input type="radio" name="item_type" value="beverage" {{ old('item_type') === 'beverage' ? 'checked' : '' }}>
                        <i class="fas fa-bottle-water"></i>
                        <span class="label">Beverage</span>
                    </label>
                </div>

                <div class="field">
                    <label>Category Name *</label>
                    <input type="text" name="item_name" required value="{{ old('item_name') }}" placeholder="e.g. Pork, Chicken…">
                </div>
                <div class="field">
                    <label>Min Stock Level *</label>
                    <input type="number" name="min_stock_level" step="0.01" min="0" required value="{{ old('min_stock_level') }}">
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>Unit *</label>
                    <select name="unit" id="addUnitRaw" required>
                        <option value="">Select unit…</option>
                        <option value="Gram" {{ old('unit') === 'Gram' ? 'selected' : '' }}>Gram</option>
                        <option value="Kilogram" {{ old('unit') === 'Kilogram' ? 'selected' : '' }}>Kilogram</option>
                    </select>
                    <select name="unit" id="addUnitBev" required style="display:none" disabled>
                        <option value="">Select unit…</option>
                        <option value="Box" {{ old('unit') === 'Box' ? 'selected' : '' }}>Box</option>
                        <option value="Piece" {{ old('unit') === 'Piece' ? 'selected' : '' }}>Piece</option>
                        <option value="Case" {{ old('unit') === 'Case' ? 'selected' : '' }}>Case</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('addItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</button>
            </div>
        </form>
    </div>
</div>

{{-- Stock-In modal --}}
<div class="modal-backdrop" id="stockInModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-arrow-down-to-bracket"></i> New Stock-In Transaction</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('stockInModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.stock-in.store') }}" id="stockInForm">
            @csrf
            <div class="modal-body">
                @if($errors->has('inventory_item_id') || $errors->has('quantity_purchased') || $errors->has('unit') || $errors->has('total_cost') || $errors->has('purchase_date'))
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="error-msg" id="siClientError" style="display:none"></div>

                <div class="type-toggle" id="siTypeToggle">
                    <label class="type-option active" data-type="rtc">
                        <input type="radio" name="si_item_type" value="rtc" checked>
                        <i class="fas fa-drumstick-bite"></i>
                        <span class="label">Raw Meat</span>
                    </label>
                    <label class="type-option" data-type="beverage">
                        <input type="radio" name="si_item_type" value="beverage">
                        <i class="fas fa-bottle-water"></i>
                        <span class="label">Beverage</span>
                    </label>
                </div>

                <div class="field">
                    <label>Inventory Item *</label>
                    <select name="inventory_item_id" id="siItemId" required onchange="onSIItemChange()">
                        <option value="">Select item…</option>
                        @foreach($rtcItems as $item)
                        <option class="si-opt-raw" value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost_price }}">{{ $item->item_name }}</option>
                        @endforeach
                        @foreach($beverageItems as $item)
                        <option class="si-opt-bev" value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost_price }}" style="display:none">{{ $item->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem">
                    <div class="field">
                        <label>Quantity Purchased *</label>
                        <input type="number" name="quantity_purchased" id="siQty" step="0.01" min="0.01" required placeholder="0.00" oninput="updateSIPreview()">
                    </div>
                    <div class="field">
                        <label>Unit *</label>
                        <select name="unit" id="siUnit" required onchange="updateSIPreview()">
                            <option value="">Select item first…</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem">
                    <div class="field">
                        <label>Total Cost</label>
                        <input type="number" name="total_cost" id="siCost" step="0.01" min="0" placeholder="0.00" oninput="updateSIPreview()">
                    </div>
                    <div class="field">
                        <label>Purchase Date *</label>
                        <input type="date" name="purchase_date" id="siDate" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="preview-box" id="siPreview" style="display:none">
                    <div class="preview-row"><span>Previous Quantity</span><span id="siPrevQty">—</span></div>
                    <div class="preview-row"><span>Purchased</span><span id="siPurchased">—</span></div>
                    <div class="preview-row"><span>New Total</span><span id="siNewQty">—</span></div>
                    <div class="preview-row" id="siCostRow" style="display:none"><span>Total Cost</span><span id="siTotalCost">—</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('stockInModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="proceedStockIn()"><i class="fas fa-arrow-right"></i> Review Stock-In</button>
            </div>
        </form>
    </div>
</div>

{{-- Stock-In Confirmation modal --}}
<div class="modal-backdrop" id="stockInConfirmModal">
    <div class="modal" style="max-width:420px">
        <div class="modal-hd">
            <h3><i class="fas fa-clipboard-check"></i> Confirm Stock-In</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('stockInConfirmModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="preview-box">
                <div class="preview-row"><span>Item</span><span id="siConfirmItem">—</span></div>
                <div class="preview-row"><span>Previous Quantity</span><span id="siConfirmPrev">—</span></div>
                <div class="preview-row"><span>Purchased</span><span id="siConfirmPurchased">—</span></div>
                <div class="preview-row"><span>New Total</span><span id="siConfirmNew">—</span></div>
                <div class="preview-row" id="siConfirmCostRow" style="display:none"><span>Total Cost</span><span id="siConfirmTotalCost">—</span></div>
            </div>
            <p style="margin-top:1rem;font-size:.82rem;color:var(--muted)">Double-check the item and quantity above. This will update the live inventory count.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="backToStockInForm()">Back</button>
            <button type="button" class="btn btn-primary" onclick="submitStockIn()"><i class="fas fa-check"></i> Confirm &amp; Save</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openLocalModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeLocalModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-backdrop').forEach(el => el.addEventListener('click', e => { if (e.target === el) closeLocalModal(el.id); }));

(function(){
    var options = document.querySelectorAll('#addItemModal .type-option');
    var unitRaw = document.getElementById('addUnitRaw');
    var unitBev = document.getElementById('addUnitBev');

    function toggleUnitSelect(type) {
        if (type === 'beverage') {
            unitRaw.style.display = 'none'; unitRaw.disabled = true;
            unitBev.style.display = ''; unitBev.disabled = false;
        } else {
            unitRaw.style.display = ''; unitRaw.disabled = false;
            unitBev.style.display = 'none'; unitBev.disabled = true;
        }
    }

    options.forEach(function(opt){
        opt.addEventListener('click', function(){
            opt.querySelector('input[type=radio]').checked = true;
            options.forEach(o => o.classList.toggle('active', o === opt));
            toggleUnitSelect(opt.dataset.type);
        });
    });

    var checked = document.querySelector('#addItemModal input[name=item_type]:checked');
    toggleUnitSelect(checked ? checked.value : 'rtc');
})();

(function(){
    var options = document.querySelectorAll('#siTypeToggle .type-option');
    options.forEach(function(opt){
        opt.addEventListener('click', function(){
            opt.querySelector('input[type=radio]').checked = true;
            options.forEach(o => o.classList.toggle('active', o === opt));
            filterSIItemsByType(opt.dataset.type);
        });
    });
})();

function filterSIItemsByType(type) {
    document.querySelectorAll('#siItemId option.si-opt-raw').forEach(o => o.style.display = type === 'rtc' ? '' : 'none');
    document.querySelectorAll('#siItemId option.si-opt-bev').forEach(o => o.style.display = type === 'beverage' ? '' : 'none');
    document.getElementById('siItemId').value = '';
    populateSIUnitOptions('');
    document.getElementById('siCost').value = '';
    updateSIPreview();
}

const SI_UNIT_FAMILIES = {
    'Gram': ['Gram', 'Kilogram'],
    'Kilogram': ['Gram', 'Kilogram'],
    'Piece': ['Piece'],
    'Box': ['Box'],
    'Case': ['Case'],
};

function populateSIUnitOptions(baseUnit) {
    const sel = document.getElementById('siUnit');
    sel.innerHTML = '';
    if (!baseUnit) {
        sel.innerHTML = '<option value="">Select item first…</option>';
        return;
    }
    const options = SI_UNIT_FAMILIES[baseUnit] || [baseUnit];
    options.forEach(function(u) {
        const o = document.createElement('option');
        o.value = u;
        o.textContent = u;
        if (u === baseUnit) o.selected = true;
        sel.appendChild(o);
    });
}

@if($errors->has('item_name') || $errors->has('unit') || $errors->has('min_stock_level'))
openLocalModal('addItemModal');
@elseif($errors->has('inventory_item_id') || $errors->has('quantity_purchased') || $errors->has('unit') || $errors->has('total_cost') || $errors->has('purchase_date'))
openLocalModal('stockInModal');
@endif

function onSIItemChange() {
    const sel = document.getElementById('siItemId');
    const opt = sel.selectedOptions[0];
    populateSIUnitOptions(opt && opt.value ? (opt.dataset.unit || '') : '');
    updateSIPreview();
}

const SI_UNIT_CONVERSION = {
    'Gram->Kilogram': 0.001,
    'Kilogram->Gram': 1000,
};

function siConvert(qty, fromUnit, toUnit) {
    if (fromUnit === toUnit) return qty;
    const factor = SI_UNIT_CONVERSION[fromUnit + '->' + toUnit];
    return factor !== undefined ? qty * factor : qty;
}

function updateSIPreview() {
    const sel = document.getElementById('siItemId');
    const opt = sel.selectedOptions[0];
    const qty = parseFloat(document.getElementById('siQty').value) || 0;
    const preview = document.getElementById('siPreview');
    if (!opt || !opt.value || !qty) { preview.style.display = 'none'; return; }
    const baseUnit = opt.dataset.unit;
    const enteredUnit = document.getElementById('siUnit').value || baseUnit;
    const prevBase = parseFloat(opt.dataset.qty) || 0;
    const purchasedInBase = siConvert(qty, enteredUnit, baseUnit);
    const newQtyBase = prevBase + purchasedInBase;
    document.getElementById('siPrevQty').textContent = prevBase.toFixed(2) + ' ' + baseUnit;
    document.getElementById('siPurchased').textContent = '+' + qty.toFixed(2) + ' ' + enteredUnit;
    document.getElementById('siNewQty').textContent = newQtyBase.toFixed(2) + ' ' + baseUnit;

    const totalCost = parseFloat(document.getElementById('siCost').value);
    const costRow = document.getElementById('siCostRow');
    if (totalCost > 0) {
        const perUnit = totalCost / qty;
        document.getElementById('siTotalCost').textContent = '₱' + totalCost.toFixed(2) + ' (₱' + perUnit.toFixed(2) + '/' + enteredUnit + ')';
        costRow.style.display = '';
    } else {
        costRow.style.display = 'none';
    }
    preview.style.display = '';
}

function proceedStockIn() {
    const errBox = document.getElementById('siClientError');
    errBox.style.display = 'none';

    const sel = document.getElementById('siItemId');
    const opt = sel.selectedOptions[0];
    const qty = parseFloat(document.getElementById('siQty').value);
    const unit = document.getElementById('siUnit').value.trim();
    const cost = document.getElementById('siCost').value;
    const date = document.getElementById('siDate').value;

    if (!opt || !opt.value) {
        errBox.textContent = 'Please select an inventory item.';
        errBox.style.display = '';
        return;
    }
    if (!qty || qty <= 0) {
        errBox.textContent = 'Please enter a quantity greater than 0.';
        errBox.style.display = '';
        return;
    }
    if (!unit) {
        errBox.textContent = 'Please enter a unit.';
        errBox.style.display = '';
        return;
    }
    if (cost !== '' && parseFloat(cost) < 0) {
        errBox.textContent = 'Total cost cannot be negative.';
        errBox.style.display = '';
        return;
    }
    if (!date) {
        errBox.textContent = 'Please select a purchase date.';
        errBox.style.display = '';
        return;
    }

    const baseUnit = opt.dataset.unit;
    const prevBase = parseFloat(opt.dataset.qty) || 0;
    const purchasedInBase = siConvert(qty, unit, baseUnit);
    const newQtyBase = prevBase + purchasedInBase;
    const costVal = parseFloat(cost);

    document.getElementById('siConfirmItem').textContent = opt.text.trim();
    document.getElementById('siConfirmPrev').textContent = prevBase.toFixed(2) + ' ' + baseUnit;
    document.getElementById('siConfirmPurchased').textContent = '+' + qty.toFixed(2) + ' ' + unit;
    document.getElementById('siConfirmNew').textContent = newQtyBase.toFixed(2) + ' ' + baseUnit;

    const confirmCostRow = document.getElementById('siConfirmCostRow');
    if (costVal > 0) {
        const perUnit = costVal / qty;
        document.getElementById('siConfirmTotalCost').textContent = '₱' + costVal.toFixed(2) + ' (₱' + perUnit.toFixed(2) + '/' + unit + ')';
        confirmCostRow.style.display = '';
    } else {
        confirmCostRow.style.display = 'none';
    }

    closeLocalModal('stockInModal');
    openLocalModal('stockInConfirmModal');
}

function backToStockInForm() {
    closeLocalModal('stockInConfirmModal');
    openLocalModal('stockInModal');
}

function submitStockIn() {
    document.getElementById('stockInForm').submit();
}
</script>
@endsection
