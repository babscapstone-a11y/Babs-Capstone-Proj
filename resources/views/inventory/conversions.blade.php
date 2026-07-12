@extends('layouts.admin')
@section('title', 'RTC Conversion History')

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
.filter-date{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:20px;width:100%;max-width:500px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-hd h3 i{color:#2563EB}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:#F8FAFC;cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:#fee2e2;color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#2563EB}
.error-msg{background:#FEF2F2;border:1.5px solid #FECACA;color:#B91C1C;border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1.1rem}
.conv-calc{background:#EFF6FF;border-radius:12px;padding:1rem 1.2rem;margin-top:.75rem;border:1px solid #BFDBFE}
.conv-calc .calc-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#1D4ED8;margin-bottom:.4rem}
.conv-result{font-size:1.5rem;font-weight:900;color:#1D4ED8}
.conv-flow{display:flex;align-items:center;gap:.75rem;margin-top:.6rem;flex-wrap:wrap}
.flow-box{background:#fff;border:1px solid #BFDBFE;border-radius:10px;padding:.5rem .85rem;text-align:center}
.flow-box .fl-val{font-size:.92rem;font-weight:700;color:#1D4ED8}
.flow-box .fl-lbl{font-size:.7rem;color:#93C5FD;font-weight:600}
.flow-arrow{color:#93C5FD;font-size:1rem}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-arrows-rotate"></i> RTC Conversion History</div>
            <div class="page-sub">Raw meat → RTC serving conversion records</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <button class="btn btn-blue" onclick="openLocalModal('cvModal')"><i class="fas fa-arrows-rotate"></i> New Conversion</button>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.conversions.index') }}" class="filter-bar">
        <div class="search-wrap"><i class="fas fa-search"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search item…" class="search-input"></div>
        <input type="date" name="from" value="{{ request('from') }}" class="filter-date" title="From date">
        <input type="date" name="to"   value="{{ request('to') }}"   class="filter-date" title="To date">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['q','from','to']))
        <a href="{{ route('inventory.conversions.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Raw Used</th>
                        <th>Portion Size</th>
                        <th>RTC Produced</th>
                        <th>Prev Raw Stock</th>
                        <th>Remaining Raw</th>
                        <th>Prev Servings</th>
                        <th>New Servings</th>
                        <th>By</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem">#{{ $log->id }}</td>
                        <td><div style="font-weight:700">{{ $log->inventoryItem?->item_name ?? '—' }}</div></td>
                        <td style="color:#DC2626;font-weight:700">{{ number_format($log->raw_quantity_used,3) }} {{ $log->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($log->portion_size,3) }}/srv</td>
                        <td><span style="font-weight:800;color:#1D4ED8;font-size:.95rem">{{ number_format($log->rtc_units_produced,0) }}</span> <span style="font-size:.75rem;color:var(--muted)">srv</span></td>
                        <td style="color:var(--muted)">{{ number_format($log->previous_raw_stock,3) }} {{ $log->unit }}</td>
                        <td style="font-weight:600">{{ number_format($log->remaining_raw_stock,3) }} {{ $log->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($log->previous_rtc_servings,0) }}</td>
                        <td style="font-weight:700;color:#16A34A">{{ number_format($log->new_rtc_servings,0) }}</td>
                        <td>{{ $log->converter?->name ?? 'Admin' }}</td>
                        <td style="font-size:.78rem;color:var(--muted)">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="empty-row"><i class="fas fa-arrows-rotate" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No conversions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">{{ $logs->links() }}</div>
        @endif
    </div>
</div>

<div class="modal-backdrop" id="cvModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-arrows-rotate"></i> Convert Raw Meat → RTC Servings</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('cvModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.conversions.store') }}" id="cvForm">
            @csrf
            <div class="modal-body">
                @if($errors->has('inventory_item_id') || $errors->has('raw_quantity_used') || $errors->has('portion_size'))
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="error-msg" id="cvClientError" style="display:none"></div>
                <div class="field">
                    <label>RTC Item *</label>
                    <select name="inventory_item_id" id="cvItem" required onchange="updateCalc()">
                        <option value="">Select RTC item…</option>
                        @foreach($rtcItems as $item)
                        <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}" data-unit="{{ $item->unit }}" data-portion="{{ $item->portion_size ?? 0.25 }}" data-punit="{{ $item->portion_unit ?? $item->unit }}">
                            {{ $item->item_name }} ({{ number_format($item->quantity,2) }} {{ $item->unit }} available)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem">
                    <div class="field">
                        <label>Raw Qty to Use *</label>
                        <input type="number" name="raw_quantity_used" id="cvRaw" step="0.01" min="0.01" required placeholder="0.00" oninput="updateCalc()">
                    </div>
                    <div class="field">
                        <label>Portion Size/serving *</label>
                        <input type="number" name="portion_size" id="cvPortion" step="0.001" min="0.001" required placeholder="0.250" oninput="updateCalc()">
                    </div>
                </div>
                <div class="conv-calc" id="cvCalc" style="display:none">
                    <div class="calc-label"><i class="fas fa-calculator"></i> Preview</div>
                    <div class="conv-result"><span id="cvUnits">0</span> <span style="font-size:.8rem">RTC Servings</span></div>
                    <div class="conv-flow">
                        <div class="flow-box"><div class="fl-val" id="cvRawShow">0</div><div class="fl-lbl">Raw Used</div></div>
                        <div class="flow-arrow">÷</div>
                        <div class="flow-box"><div class="fl-val" id="cvPortShow">0</div><div class="fl-lbl">Per Serving</div></div>
                        <div class="flow-arrow">=</div>
                        <div class="flow-box"><div class="fl-val" id="cvUnitsShow">0</div><div class="fl-lbl">Servings</div></div>
                    </div>
                    <div style="font-size:.75rem;color:#60A5FA;margin-top:.5rem">Remaining raw: <strong id="cvRemain">0</strong></div>
                </div>
                <div class="field" style="margin-top:1rem">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" style="resize:none" placeholder="Optional notes…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('cvModal')">Cancel</button>
                <button type="button" class="btn btn-blue" onclick="proceedConvert()"><i class="fas fa-arrow-right"></i> Review Conversion</button>
            </div>
        </form>
    </div>
</div>

{{-- Conversion Confirmation modal --}}
<div class="modal-backdrop" id="cvConfirmModal">
    <div class="modal" style="max-width:460px">
        <div class="modal-hd">
            <h3><i class="fas fa-clipboard-check"></i> Confirm Conversion</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('cvConfirmModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="field" style="margin-bottom:.6rem">
                <label style="margin-bottom:.15rem">Item</label>
                <div style="font-weight:700;color:var(--dark)" id="cvConfirmItem">—</div>
            </div>
            <div class="conv-calc">
                <div class="calc-label"><i class="fas fa-calculator"></i> Summary</div>
                <div class="conv-result"><span id="cvConfirmUnits">0</span> <span style="font-size:.8rem">RTC Servings</span></div>
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
            <button type="button" class="btn btn-blue" onclick="submitConvert()"><i class="fas fa-check"></i> Confirm &amp; Save</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openLocalModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeLocalModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.modal-backdrop').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)closeLocalModal(el.id);}));
function updateCalc(){
    const sel=document.getElementById('cvItem');const opt=sel.selectedOptions[0];
    const raw=parseFloat(document.getElementById('cvRaw').value)||0;
    const portion=parseFloat(document.getElementById('cvPortion').value)||0;
    const calc=document.getElementById('cvCalc');
    if(!opt?.value||!raw||!portion){calc.style.display='none';return;}
    const avail=parseFloat(opt.dataset.qty)||0;const unit=opt.dataset.unit;const punit=opt.dataset.punit||unit;
    const units=Math.floor(raw/portion);const remain=avail-raw;
    document.getElementById('cvUnits').textContent=units;
    document.getElementById('cvRawShow').textContent=raw.toFixed(3)+' '+unit;
    document.getElementById('cvPortShow').textContent=portion.toFixed(3)+' '+punit;
    document.getElementById('cvUnitsShow').textContent=units;
    document.getElementById('cvRemain').textContent=remain.toFixed(3)+' '+unit;
    calc.style.display='';
    // Auto-fill portion from item default
    if(document.getElementById('cvPortion').value===''&&opt.dataset.portion)
        document.getElementById('cvPortion').value=opt.dataset.portion;
}
document.getElementById('cvItem').addEventListener('change',function(){
    const opt=this.selectedOptions[0];
    if(opt?.dataset?.portion)document.getElementById('cvPortion').value=opt.dataset.portion;
});
function proceedConvert(){
    const errBox=document.getElementById('cvClientError');
    errBox.style.display='none';

    const sel=document.getElementById('cvItem');const opt=sel.selectedOptions[0];
    const raw=parseFloat(document.getElementById('cvRaw').value);
    const portion=parseFloat(document.getElementById('cvPortion').value);

    if(!opt||!opt.value){errBox.textContent='Please select an RTC item.';errBox.style.display='';return;}
    if(!raw||raw<=0){errBox.textContent='Please enter a raw quantity greater than 0.';errBox.style.display='';return;}
    if(!portion||portion<=0){errBox.textContent='Please enter a portion size greater than 0.';errBox.style.display='';return;}

    const avail=parseFloat(opt.dataset.qty)||0;
    const unit=opt.dataset.unit;const punit=opt.dataset.punit||unit;

    if(raw>avail){errBox.textContent='Insufficient raw stock. Available: '+avail.toFixed(2)+' '+unit+'.';errBox.style.display='';return;}

    const units=Math.floor(raw/portion);
    if(units<1){errBox.textContent='This quantity is too small to produce even 1 RTC serving (need at least '+portion.toFixed(3)+' '+punit+').';errBox.style.display='';return;}

    const remain=avail-raw;
    document.getElementById('cvConfirmItem').textContent=opt.text.split('(')[0].trim();
    document.getElementById('cvConfirmUnits').textContent=units;
    document.getElementById('cvConfirmUnits2').textContent=units;
    document.getElementById('cvConfirmRaw').textContent=raw.toFixed(3)+' '+unit;
    document.getElementById('cvConfirmPortion').textContent=portion.toFixed(3)+' '+punit;
    document.getElementById('cvConfirmRemain').textContent=remain.toFixed(3)+' '+unit;

    closeLocalModal('cvModal');
    openLocalModal('cvConfirmModal');
}

function backToConvertForm(){
    closeLocalModal('cvConfirmModal');
    openLocalModal('cvModal');
}

function submitConvert(){
    document.getElementById('cvForm').submit();
}

@if($errors->has('inventory_item_id') || $errors->has('raw_quantity_used') || $errors->has('portion_size'))
openLocalModal('cvModal');
@endif
</script>
@endsection
