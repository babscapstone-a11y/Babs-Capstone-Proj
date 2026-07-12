@extends('layouts.admin')
@section('title', 'RTC Units')

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
.filter-select:focus{border-color:var(--primary)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}

.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.badge-available{background:#DCFCE7;color:#15803D}.badge-low{background:#FEF3C7;color:#B45309}.badge-out{background:#FEE2E2;color:#B91C1C}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}

/* Modal */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal{background:#fff;border-radius:20px;width:100%;max-width:480px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-hd h3 i{color:var(--primary)}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:var(--bg,#F8FAFC);cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:#fee2e2;color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary)}
.field .help{font-size:.75rem;color:var(--muted);margin-top:.25rem}
.conv-calc{background:#EFF6FF;border-radius:12px;padding:1rem 1.2rem;margin-top:.75rem;border:1px solid #BFDBFE}
.conv-calc .calc-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#1D4ED8;margin-bottom:.4rem}
.conv-result{font-size:1.5rem;font-weight:900;color:#1D4ED8}
.conv-result span{font-size:.8rem;font-weight:600}
.conv-flow{display:flex;align-items:center;gap:.75rem;margin-top:.6rem;flex-wrap:wrap}
.flow-box{background:#fff;border:1px solid #BFDBFE;border-radius:10px;padding:.5rem .85rem;text-align:center}
.flow-box .fl-val{font-size:.92rem;font-weight:700;color:#1D4ED8}
.flow-box .fl-lbl{font-size:.7rem;color:#93C5FD;font-weight:600}
.flow-arrow{color:#93C5FD;font-size:1rem}
.error-msg{background:#FEF2F2;border:1.5px solid #FECACA;color:#B91C1C;border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1.1rem}
</style>
@endsection

@section('content')
<div class="inv-page">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-utensils"></i> RTC Units</div>
            <div class="page-sub">Track ready-to-cook servings converted from raw meat stock</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <button class="btn btn-green" onclick="openLocalModal('convertModal')"><i class="fas fa-arrows-rotate"></i> Convert to RTC</button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-list"></i></div><div class="stat-value">{{ $totalRtc }}</div><div class="stat-label">Total RTC Items</div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $lowServings }}</div><div class="stat-label">Low Servings</div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $outOfServings }}</div><div class="stat-label">Out of Servings</div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-utensils"></i></div><div class="stat-value">{{ number_format($totalServings, 0) }}</div><div class="stat-label">Total RTC Servings</div></div>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('inventory.rtc-inventory') }}" class="filter-bar" id="liveFilterForm">
        <div class="search-wrap">
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search item, category…" class="search-input" autocomplete="off">
            <button type="button" class="search-clear" aria-label="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <select name="status" class="filter-select">
            <option value="">All Status</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Servings</option>
            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Servings</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('inventory.rtc-inventory') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card" id="results">
        @include('inventory._rtc-inventory-results', ['items' => $items])
    </div>

    {{-- Links --}}
    <div style="margin-top:1rem;display:flex;gap:1rem;font-size:.82rem;">
        <a href="{{ route('inventory.conversions.index') }}" style="color:var(--primary);font-weight:600"><i class="fas fa-history"></i> Conversion History</a>
    </div>
</div>

{{-- ── Convert Modal ── --}}
<div class="modal-backdrop" id="convertModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-arrows-rotate"></i> Convert Raw Meat → RTC Servings</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('convertModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.conversions.store') }}" id="convertForm">
            @csrf
            <div class="modal-body">
                @if($errors->has('inventory_item_id') || $errors->has('raw_quantity_used') || $errors->has('portion_size'))
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="error-msg" id="cvClientError" style="display:none"></div>
                <div class="field">
                    <label>RTC Item *</label>
                    <select name="inventory_item_id" id="cvItemId" required onchange="updateConvertCalc()">
                        <option value="">Select item…</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}"
                            data-qty="{{ $item->quantity }}"
                            data-unit="{{ $item->unit }}"
                            data-portion="{{ $item->portion_size ?? 0.25 }}"
                            data-punit="{{ $item->portion_unit ?? $item->unit }}">
                            {{ $item->item_name }} ({{ number_format($item->quantity,2) }} {{ $item->unit }} available)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem">
                    <div class="field">
                        <label>Raw Quantity to Use *</label>
                        <input type="number" name="raw_quantity_used" id="cvRawQty" step="0.01" min="0.01" required placeholder="0.00" oninput="updateConvertCalc()">
                    </div>
                    <div class="field">
                        <label>Portion Size (per serving) *</label>
                        <input type="number" name="portion_size" id="cvPortion" step="0.001" min="0.001" required placeholder="0.250" oninput="updateConvertCalc()">
                        <div class="help" id="cvPortionHelp"></div>
                    </div>
                </div>
                <div class="conv-calc" id="convCalc" style="display:none">
                    <div class="calc-label"><i class="fas fa-calculator"></i> Conversion Preview</div>
                    <div class="conv-result"><span id="cvUnits">0</span> <span>RTC Servings</span></div>
                    <div style="font-size:.78rem;color:#1D4ED8;margin-top:.3rem">
                        <span id="cvRawUsed">0</span> ÷ <span id="cvPortionShow">0</span> = <span id="cvUnitsShow">0</span> servings
                        &bull; Remaining raw: <span id="cvRemaining">0</span>
                    </div>
                </div>
                <div class="field" style="margin-top:1rem">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Optional notes…" style="resize:none"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('convertModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="proceedConvert()"><i class="fas fa-arrow-right"></i> Review Conversion</button>
            </div>
        </form>
    </div>
</div>

{{-- Convert Confirmation modal --}}
<div class="modal-backdrop" id="convertConfirmModal">
    <div class="modal" style="max-width:460px">
        <div class="modal-hd">
            <h3><i class="fas fa-clipboard-check"></i> Confirm Conversion</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('convertConfirmModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="field" style="margin-bottom:.6rem">
                <label style="margin-bottom:.15rem">Item</label>
                <div style="font-weight:700;color:var(--dark)" id="cvConfirmItem">—</div>
            </div>
            <div class="conv-calc">
                <div class="calc-label"><i class="fas fa-calculator"></i> Summary</div>
                <div class="conv-result"><span id="cvConfirmUnits">0</span> <span>RTC Servings</span></div>
                <div class="conv-flow">
                    <div class="flow-box"><div class="fl-val" id="cvConfirmRaw">0</div><div class="fl-lbl">Raw Used</div></div>
                    <div class="flow-arrow">÷</div>
                    <div class="flow-box"><div class="fl-val" id="cvConfirmPortion">0</div><div class="fl-lbl">Per Serving</div></div>
                    <div class="flow-arrow">=</div>
                    <div class="flow-box"><div class="fl-val" id="cvConfirmUnits2">0</div><div class="fl-lbl">Servings</div></div>
                </div>
                <div style="font-size:.75rem;color:#60A5FA;margin-top:.5rem">Remaining raw: <strong id="cvConfirmRemain">0</strong></div>
            </div>
            <p style="margin-top:1rem;font-size:.82rem;color:var(--muted)">This will deduct raw stock and add RTC servings. Please confirm the numbers above are correct.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="backToConvertForm()">Back</button>
            <button type="button" class="btn btn-primary" onclick="submitConvert()"><i class="fas fa-check"></i> Confirm &amp; Save</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openLocalModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeLocalModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-backdrop').forEach(el => el.addEventListener('click', e => { if(e.target===el) closeLocalModal(el.id); }));

document.addEventListener('DOMContentLoaded', function () {
    LiveTable.init({
        formSelector: '#liveFilterForm',
        resultsSelector: '#results',
        url: '{{ route('inventory.rtc-inventory') }}',
        searchFieldName: 'search',
        debounceMs: 300,
    });
});

function openConvertFor(id, name, unit, qty, portion, punit) {
    const sel = document.getElementById('cvItemId');
    sel.value = id;
    document.getElementById('cvPortion').value = portion;
    document.getElementById('cvPortionHelp').textContent = `Default: ${portion} ${punit} per serving`;
    updateConvertCalc();
    openLocalModal('convertModal');
}

function updateConvertCalc() {
    const sel = document.getElementById('cvItemId');
    const opt = sel.selectedOptions[0];
    const rawQty  = parseFloat(document.getElementById('cvRawQty').value) || 0;
    const portion = parseFloat(document.getElementById('cvPortion').value) || 0;
    const calc = document.getElementById('convCalc');
    if (!opt || !opt.value || !rawQty || !portion) { calc.style.display='none'; return; }
    const avail    = parseFloat(opt.dataset.qty) || 0;
    const unit     = opt.dataset.unit;
    const punit    = opt.dataset.punit;
    const units    = Math.floor(rawQty / portion);
    const remaining = avail - rawQty;
    document.getElementById('cvUnits').textContent = units;
    document.getElementById('cvRawUsed').textContent = rawQty.toFixed(3) + ' ' + unit;
    document.getElementById('cvPortionShow').textContent = portion.toFixed(3) + ' ' + punit;
    document.getElementById('cvUnitsShow').textContent = units;
    document.getElementById('cvRemaining').textContent = remaining.toFixed(3) + ' ' + unit;
    calc.style.display = '';
    document.getElementById('cvPortionHelp').textContent = opt.dataset.portion ? `Default: ${opt.dataset.portion} ${punit}/serving` : '';
}

function proceedConvert() {
    const errBox = document.getElementById('cvClientError');
    errBox.style.display = 'none';

    const sel = document.getElementById('cvItemId');
    const opt = sel.selectedOptions[0];
    const rawQty = parseFloat(document.getElementById('cvRawQty').value);
    const portion = parseFloat(document.getElementById('cvPortion').value);

    if (!opt || !opt.value) {
        errBox.textContent = 'Please select an RTC item.';
        errBox.style.display = '';
        return;
    }
    if (!rawQty || rawQty <= 0) {
        errBox.textContent = 'Please enter a raw quantity greater than 0.';
        errBox.style.display = '';
        return;
    }
    if (!portion || portion <= 0) {
        errBox.textContent = 'Please enter a portion size greater than 0.';
        errBox.style.display = '';
        return;
    }

    const avail = parseFloat(opt.dataset.qty) || 0;
    const unit  = opt.dataset.unit;
    const punit = opt.dataset.punit || unit;

    if (rawQty > avail) {
        errBox.textContent = 'Insufficient raw stock. Available: ' + avail.toFixed(2) + ' ' + unit + '.';
        errBox.style.display = '';
        return;
    }

    const units = Math.floor(rawQty / portion);
    if (units < 1) {
        errBox.textContent = 'This quantity is too small to produce even 1 RTC serving (need at least ' + portion.toFixed(3) + ' ' + punit + ').';
        errBox.style.display = '';
        return;
    }

    const remain = avail - rawQty;
    document.getElementById('cvConfirmItem').textContent = opt.text.split('(')[0].trim();
    document.getElementById('cvConfirmUnits').textContent = units;
    document.getElementById('cvConfirmUnits2').textContent = units;
    document.getElementById('cvConfirmRaw').textContent = rawQty.toFixed(3) + ' ' + unit;
    document.getElementById('cvConfirmPortion').textContent = portion.toFixed(3) + ' ' + punit;
    document.getElementById('cvConfirmRemain').textContent = remain.toFixed(3) + ' ' + unit;

    closeLocalModal('convertModal');
    openLocalModal('convertConfirmModal');
}

function backToConvertForm() {
    closeLocalModal('convertConfirmModal');
    openLocalModal('convertModal');
}

function submitConvert() {
    document.getElementById('convertForm').submit();
}

@if($errors->has('inventory_item_id') || $errors->has('raw_quantity_used') || $errors->has('portion_size'))
openLocalModal('convertModal');
@endif
</script>
@endsection
