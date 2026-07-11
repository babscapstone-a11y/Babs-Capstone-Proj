@extends('layouts.admin')
@section('title', 'Beverages')

@section('styles')
<style>
.inv-page{padding:2rem;max-width:1400px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:1.1rem;margin-bottom:2rem}
.stat-card{background:#fff;border-radius:14px;padding:1.1rem 1.25rem;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07)}
.stat-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.75rem}
.stat-value{font-size:1.6rem;font-weight:800;color:var(--dark);line-height:1}
.stat-label{font-size:.75rem;font-weight:600;color:var(--muted);margin-top:.25rem;text-transform:uppercase;letter-spacing:.04em}
.stat-icon.blue{background:#EFF6FF;color:#2563EB}.stat-icon.green{background:#F0FDF4;color:#16A34A}.stat-icon.amber{background:#FFFBEB;color:#D97706}.stat-icon.red{background:#FEF2F2;color:#DC2626}
.filter-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem}
.search-wrap{position:relative;flex:1;min-width:220px}
.search-input{width:100%;padding:.55rem 2.3rem .55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.search-input:focus{border-color:var(--primary)}
.search-clear{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--muted);cursor:pointer;padding:.25rem;display:none}
.search-wrap.has-value .search-clear{display:block}
.search-wrap.has-value .search-clear:hover{color:var(--primary)}
.results-count{font-size:.8rem;color:var(--muted);padding:.85rem 1.2rem 0}
#results.is-loading{opacity:.5;transition:opacity .15s}
.filter-select{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;cursor:pointer}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.btn-amber{background:#F59E0B;color:#fff}.btn-amber:hover{background:#D97706}
.btn-purple{background:#7C3AED;color:#fff}.btn-purple:hover{background:#6D28D9}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.badge-available{background:#DCFCE7;color:#15803D}.badge-low{background:#FEF3C7;color:#B45309}.badge-out{background:#FEE2E2;color:#B91C1C}
.progress-bar{height:6px;border-radius:3px;background:#F3F4F6;overflow:hidden;width:80px;margin-top:.3rem}
.progress-fill{height:100%;border-radius:3px}
.progress-green{background:#16A34A}.progress-amber{background:#F59E0B}.progress-red{background:#DC2626}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-hd h3 i{color:var(--primary)}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:#F8FAFC;cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:#fee2e2;color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary)}
.preview-box{background:#F8FAFC;border-radius:12px;padding:1rem 1.2rem;margin:.75rem 0;border:1px solid var(--border)}
.preview-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;color:var(--dark)}
.preview-row:last-child{margin-bottom:0;font-weight:700;color:var(--primary)}
.adj-type-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem}
.adj-type-btn{padding:.55rem .8rem;border-radius:10px;border:1.5px solid var(--border);background:#fff;cursor:pointer;font-size:.81rem;font-weight:600;font-family:inherit;color:var(--dark);transition:all .15s;text-align:center}
.adj-type-btn.selected{border-color:var(--primary);background:#FEF2F2;color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">

    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-bottle-water"></i> Beverages</div>
            <div class="page-sub">Track beverage stock and manage adjustments</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.restocking') }}" class="btn btn-outline"><i class="fas fa-cart-shopping"></i> Repurchase List</a>
            <button class="btn btn-purple" onclick="openModal('adjustModal')"><i class="fas fa-pen-to-square"></i> Adjust</button>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-list"></i></div><div class="stat-value">{{ $totalBev }}</div><div class="stat-label">Total Beverages</div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $lowStock }}</div><div class="stat-label">Low Stock</div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $outOfStock }}</div><div class="stat-label">Out of Stock</div></div>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.beverages') }}" class="filter-bar" id="liveFilterForm">
        <div class="search-wrap">
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search beverage, category…" class="search-input" autocomplete="off">
            <button type="button" class="search-clear" aria-label="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('inventory.beverages') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card" id="results">
        @include('inventory._beverages-results', ['items' => $items])
    </div>

    <div style="margin-top:1rem;display:flex;gap:1rem;font-size:.82rem">
        <a href="{{ route('inventory.adjustments.index') }}" style="color:var(--primary);font-weight:600"><i class="fas fa-history"></i> Adjustment History</a>
        <a href="{{ route('inventory.stock-in.index') }}?type=beverage" style="color:var(--primary);font-weight:600"><i class="fas fa-history"></i> Stock-In History</a>
    </div>
</div>

{{-- Adjust Modal --}}
<div class="modal-backdrop" id="adjustModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-pen-to-square"></i> Inventory Adjustment</h3>
            <button class="modal-close-btn" onclick="closeModal('adjustModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" onsubmit="return confirmAdjust()">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Beverage Item *</label>
                    <select name="inventory_item_id" id="adjItemId" required onchange="updateAdjPreview()">
                        <option value="">Select beverage…</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}">{{ $item->item_name }} ({{ number_format($item->quantity,0) }} {{ $item->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="adjustment_type" id="adjTypeHidden" value="damaged">
                <div class="field">
                    <label>Adjustment Type *</label>
                    <div class="adj-type-grid">
                        <button type="button" class="adj-type-btn selected" onclick="setAdjType('damaged', this)">🔴 Damaged</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('expired', this)">⏰ Expired</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('missing', this)">❓ Missing</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('correction', this)">✏️ Correction</button>
                    </div>
                </div>
                <div class="field">
                    <label>Adjustment Quantity *<br><small style="font-weight:400;color:var(--muted)">Use negative to deduct (e.g. -5), positive to add (e.g. +3)</small></label>
                    <input type="number" name="quantity_adjusted" id="adjQty" step="1" required placeholder="-5" oninput="updateAdjPreview()">
                </div>
                <div class="field">
                    <label>Reason *</label>
                    <input type="text" name="reason" required placeholder="Brief reason for adjustment…">
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" style="resize:none" placeholder="Additional details…"></textarea>
                </div>
                <div class="preview-box" id="adjPreview" style="display:none">
                    <div class="preview-row"><span>Before Adjustment</span><span id="adjBefore">—</span></div>
                    <div class="preview-row"><span>Adjustment</span><span id="adjChange">—</span></div>
                    <div class="preview-row"><span>After Adjustment</span><span id="adjAfter">—</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('adjustModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Adjustment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(el => el.addEventListener('click', e => { if(e.target===el) closeModal(el.id); }));

document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('inventory.beverages') }}',
        searchFieldName: 'search',
        debounceMs: 300,
    });
});

function setAdjType(type, btn) {
    document.getElementById('adjTypeHidden').value = type;
    document.querySelectorAll('.adj-type-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

function openAdjustFor(id, name, unit, qty) {
    document.getElementById('adjItemId').value = id;
    updateAdjPreview();
    openModal('adjustModal');
}

function updateAdjPreview() {
    const sel = document.getElementById('adjItemId');
    const opt = sel.selectedOptions[0];
    const adj = parseFloat(document.getElementById('adjQty').value) || 0;
    const preview = document.getElementById('adjPreview');
    if (!opt?.value || adj === 0) { preview.style.display='none'; return; }
    const before = parseFloat(opt.dataset.qty) || 0;
    const unit = opt.dataset.unit;
    const after = Math.max(0, before + adj);
    document.getElementById('adjBefore').textContent = before.toFixed(0) + ' ' + unit;
    document.getElementById('adjChange').textContent = (adj >= 0 ? '+' : '') + adj.toFixed(0) + ' ' + unit;
    document.getElementById('adjAfter').textContent = after.toFixed(0) + ' ' + unit;
    preview.style.display = '';
}

function confirmAdjust() {
    const sel = document.getElementById('adjItemId');
    const opt = sel.selectedOptions[0];
    const adj = parseFloat(document.getElementById('adjQty').value) || 0;
    if (!opt?.value) return true;
    const before = parseFloat(opt.dataset.qty) || 0;
    const after = Math.max(0, before + adj);
    return confirm(`Adjustment Confirmation\n\nBeverage: ${opt.text.split('(')[0].trim()}\nBefore: ${before} ${opt.dataset.unit}\nAdjustment: ${adj >= 0 ? '+' : ''}${adj} ${opt.dataset.unit}\nAfter: ${after} ${opt.dataset.unit}\n\nAre you sure you want to confirm this adjustment?`);
}
</script>
@endsection
