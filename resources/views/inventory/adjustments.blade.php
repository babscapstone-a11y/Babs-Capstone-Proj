@extends('layouts.admin')
@section('title', 'Inventory Adjustments')

@section('styles')
<style>
.inv-page{padding:2rem;max-width:1400px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.filter-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem}
.search-wrap{position:relative;flex:1;min-width:220px}
.search-wrap i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;pointer-events:none}
.search-input{width:100%;padding:.55rem .9rem .55rem 2.3rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.search-input:focus{border-color:var(--primary)}
.filter-select,.filter-date{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-purple{background:#7C3AED;color:#fff}.btn-purple:hover{background:#6D28D9}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-damaged{background:#FEF2F2;color:#B91C1C}.badge-expired{background:#FEF3C7;color:#92400E}.badge-missing{background:#F5F3FF;color:#6D28D9}.badge-correction{background:#F0FDF4;color:#15803D}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:20px;width:100%;max-width:480px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:#F8FAFC;cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:#fee2e2;color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary)}
.adj-type-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.adj-type-btn{padding:.55rem .8rem;border-radius:10px;border:1.5px solid var(--border);background:#fff;cursor:pointer;font-size:.81rem;font-weight:600;font-family:inherit;color:var(--dark);transition:all .15s;text-align:center}
.adj-type-btn.selected{border-color:var(--primary);background:#FEF2F2;color:var(--primary)}
.preview-box{background:#F8FAFC;border-radius:12px;padding:1rem 1.2rem;margin:.75rem 0;border:1px solid var(--border)}
.preview-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem}
.preview-row:last-child{margin-bottom:0;font-weight:700;color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-pen-to-square"></i> Inventory Adjustments</div>
            <div class="page-sub">Manual corrections, damaged, expired, and missing stock records</div>
        </div>
        <button class="btn btn-purple" onclick="openModal('adjModal')"><i class="fas fa-plus"></i> New Adjustment</button>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.adjustments.index') }}" class="filter-bar">
        <div class="search-wrap"><i class="fas fa-search"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search item…" class="search-input"></div>
        <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="damaged"    {{ request('type')==='damaged'    ? 'selected' : '' }}>Damaged</option>
            <option value="expired"    {{ request('type')==='expired'    ? 'selected' : '' }}>Expired</option>
            <option value="missing"    {{ request('type')==='missing'    ? 'selected' : '' }}>Missing</option>
            <option value="correction" {{ request('type')==='correction' ? 'selected' : '' }}>Correction</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="filter-date" title="From date">
        <input type="date" name="to"   value="{{ request('to') }}"   class="filter-date" title="To date">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['q','type','from','to']))
        <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Before</th>
                        <th>Adjustment</th>
                        <th>After</th>
                        <th>Reason</th>
                        <th>Adjusted By</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    @php
                        $typeClass = match($adj->adjustment_type) {
                            'damaged'    => 'badge-damaged',
                            'expired'    => 'badge-expired',
                            'missing'    => 'badge-missing',
                            'correction' => 'badge-correction',
                            default      => ''
                        };
                        $isNeg = (float)$adj->quantity_adjusted < 0;
                    @endphp
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem">#{{ $adj->id }}</td>
                        <td>
                            <div style="font-weight:700">{{ $adj->inventoryItem?->item_name ?? '—' }}</div>
                            <div style="font-size:.72rem;color:var(--muted)">{{ $adj->inventoryItem?->item_type === 'rtc' ? 'RTC' : 'Beverage' }}</div>
                        </td>
                        <td><span class="badge {{ $typeClass }}">{{ $adj->getTypeLabel() }}</span></td>
                        <td style="color:var(--muted)">{{ number_format($adj->quantity_before,2) }} {{ $adj->inventoryItem?->unit }}</td>
                        <td style="font-weight:700;color:{{ $isNeg ? '#DC2626' : '#16A34A' }}">
                            {{ $isNeg ? '' : '+' }}{{ number_format($adj->quantity_adjusted,2) }} {{ $adj->inventoryItem?->unit }}
                        </td>
                        <td style="font-weight:700">{{ number_format($adj->quantity_after,2) }} {{ $adj->inventoryItem?->unit }}</td>
                        <td style="font-size:.8rem">{{ $adj->reason }}</td>
                        <td>{{ $adj->adjuster?->name ?? 'Admin' }}</td>
                        <td style="font-size:.78rem;color:var(--muted)">{{ $adj->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="empty-row"><i class="fas fa-pen-to-square" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No adjustments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">{{ $adjustments->links() }}</div>
        @endif
    </div>
</div>

<div class="modal-backdrop" id="adjModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-pen-to-square"></i> New Inventory Adjustment</h3>
            <button class="modal-close-btn" onclick="closeModal('adjModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" onsubmit="return confirmAdj()">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Inventory Item *</label>
                    <select name="inventory_item_id" id="adjItem" required onchange="updateAdjPreview()">
                        <option value="">Select item…</option>
                        <optgroup label="RTC Raw Meat">
                        @foreach($rtcItems as $item)
                        <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}">{{ $item->item_name }} ({{ number_format($item->quantity,2) }} {{ $item->unit }})</option>
                        @endforeach
                        </optgroup>
                        <optgroup label="Beverages">
                        @foreach($beverageItems as $item)
                        <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}">{{ $item->item_name }} ({{ number_format($item->quantity,0) }} {{ $item->unit }})</option>
                        @endforeach
                        </optgroup>
                    </select>
                </div>
                <input type="hidden" name="adjustment_type" id="adjTypeHidden" value="damaged">
                <div class="field">
                    <label>Adjustment Type *</label>
                    <div class="adj-type-grid">
                        <button type="button" class="adj-type-btn selected" onclick="setAdjType('damaged',this)">🔴 Damaged</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('expired',this)">⏰ Expired</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('missing',this)">❓ Missing</button>
                        <button type="button" class="adj-type-btn" onclick="setAdjType('correction',this)">✏️ Correction</button>
                    </div>
                </div>
                <div class="field">
                    <label>Quantity Adjustment * <small style="font-weight:400;color:var(--muted)">(negative to deduct)</small></label>
                    <input type="number" name="quantity_adjusted" id="adjQty" step="0.01" required placeholder="-5" oninput="updateAdjPreview()">
                </div>
                <div class="field">
                    <label>Reason *</label>
                    <input type="text" name="reason" required placeholder="Brief reason…">
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" style="resize:none"></textarea>
                </div>
                <div class="preview-box" id="adjPreview" style="display:none">
                    <div class="preview-row"><span>Before</span><span id="adjBefore">—</span></div>
                    <div class="preview-row"><span>Adjustment</span><span id="adjChange">—</span></div>
                    <div class="preview-row"><span>After</span><span id="adjAfter">—</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('adjModal')">Cancel</button>
                <button type="submit" class="btn btn-purple"><i class="fas fa-check"></i> Confirm Adjustment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.modal-backdrop').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)closeModal(el.id);}));
function setAdjType(type,btn){
    document.getElementById('adjTypeHidden').value=type;
    document.querySelectorAll('.adj-type-btn').forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
}
function updateAdjPreview(){
    const sel=document.getElementById('adjItem');const opt=sel.selectedOptions[0];
    const adj=parseFloat(document.getElementById('adjQty').value)||0;
    const preview=document.getElementById('adjPreview');
    if(!opt?.value||adj===0){preview.style.display='none';return;}
    const before=parseFloat(opt.dataset.qty)||0;const unit=opt.dataset.unit;
    const after=Math.max(0,before+adj);
    document.getElementById('adjBefore').textContent=before.toFixed(2)+' '+unit;
    document.getElementById('adjChange').textContent=(adj>=0?'+':'')+adj.toFixed(2)+' '+unit;
    document.getElementById('adjAfter').textContent=after.toFixed(2)+' '+unit;
    preview.style.display='';
}
function confirmAdj(){
    const sel=document.getElementById('adjItem');const opt=sel.selectedOptions[0];
    const adj=parseFloat(document.getElementById('adjQty').value)||0;
    if(!opt?.value)return true;
    const before=parseFloat(opt.dataset.qty)||0;const after=Math.max(0,before+adj);
    return confirm('Adjustment Confirmation\n\nItem: '+opt.text.split('(')[0].trim()+'\nBefore: '+before.toFixed(2)+' '+opt.dataset.unit+'\nAdjustment: '+(adj>=0?'+':'')+adj.toFixed(2)+' '+opt.dataset.unit+'\nAfter: '+after.toFixed(2)+' '+opt.dataset.unit+'\n\nAre you sure you want to confirm this adjustment?');
}
</script>
@endsection
