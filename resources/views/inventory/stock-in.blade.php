@extends('layouts.admin')
@section('title', 'Stock-In Transactions')

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
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:#FAFAFA}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-rtc{background:#EFF6FF;color:#1D4ED8}.badge-bev{background:#F5F3FF;color:#6D28D9}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:20px;width:100%;max-width:500px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
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
.preview-box{background:#F8FAFC;border-radius:12px;padding:1rem 1.2rem;margin:.75rem 0;border:1px solid var(--border)}
.preview-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem}
.preview-row:last-child{margin-bottom:0;font-weight:700;color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-arrow-down-to-bracket"></i> Stock-In Transactions</div>
            <div class="page-sub">History of all inventory stock-in records</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('siModal')"><i class="fas fa-plus"></i> New Stock-In</button>
    </div>

    @if(session('success'))
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:#166534;font-weight:500;"><i class="fas fa-check-circle" style="color:#16A34A;"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.stock-in.index') }}" class="filter-bar">
        <div class="search-wrap"><i class="fas fa-search"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search item…" class="search-input"></div>
        <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="rtc" {{ request('type')==='rtc' ? 'selected' : '' }}>RTC</option>
            <option value="beverage" {{ request('type')==='beverage' ? 'selected' : '' }}>Beverage</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="filter-date" title="From date">
        <input type="date" name="to" value="{{ request('to') }}" class="filter-date" title="To date">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['q','type','from','to']))
        <a href="{{ route('inventory.stock-in.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
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
                        <th>Previous Qty</th>
                        <th>Purchased</th>
                        <th>New Qty</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem">#{{ $tx->id }}</td>
                        <td><div style="font-weight:700">{{ $tx->inventoryItem?->item_name ?? '—' }}</div></td>
                        <td><span class="badge {{ $tx->po_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($tx->po_type) }}</span></td>
                        <td style="color:var(--muted)">{{ number_format($tx->previous_quantity, 2) }} {{ $tx->unit }}</td>
                        <td><span style="color:#16A34A;font-weight:700">+{{ number_format($tx->quantity_purchased, 2) }} {{ $tx->unit }}</span></td>
                        <td style="font-weight:700">{{ number_format($tx->new_quantity, 2) }} {{ $tx->unit }}</td>
                        <td>{{ $tx->supplier ?? '—' }}</td>
                        <td>{{ $tx->purchase_date?->format('M d, Y') }}</td>
                        <td>{{ $tx->recorder?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="empty-row"><i class="fas fa-inbox" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No stock-in transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>

<div class="modal-backdrop" id="siModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-arrow-down-to-bracket"></i> New Stock-In Transaction</h3>
            <button class="modal-close-btn" onclick="closeModal('siModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.stock-in.store') }}" onsubmit="return confirmSI()">
            @csrf
            <div class="modal-body">
                <div class="field">
                    <label>Inventory Item *</label>
                    <select name="inventory_item_id" id="newSiItem" required onchange="updateSIPreview()">
                        <option value="">All RTC items…</option>
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
                <div class="field">
                    <label>Quantity Purchased *</label>
                    <input type="number" name="quantity_purchased" id="newSiQty" step="0.01" min="0.01" required placeholder="0.00" oninput="updateSIPreview()">
                </div>
                <div class="field">
                    <label>Purchase Date *</label>
                    <input type="date" name="purchase_date" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="field">
                    <label>Supplier (Optional)</label>
                    <input type="text" name="supplier" placeholder="Supplier name…">
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" style="resize:none" placeholder="Optional notes…"></textarea>
                </div>
                <div class="preview-box" id="newSiPreview" style="display:none">
                    <div class="preview-row"><span>Previous Quantity</span><span id="newSiPrev">—</span></div>
                    <div class="preview-row"><span>Purchased</span><span id="newSiPurchased">—</span></div>
                    <div class="preview-row"><span>New Total</span><span id="newSiNew">—</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('siModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Stock-In</button>
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
function updateSIPreview(){
    const sel=document.getElementById('newSiItem');const opt=sel.selectedOptions[0];
    const qty=parseFloat(document.getElementById('newSiQty').value)||0;
    const preview=document.getElementById('newSiPreview');
    if(!opt?.value||!qty){preview.style.display='none';return;}
    const prev=parseFloat(opt.dataset.qty)||0;const unit=opt.dataset.unit;
    document.getElementById('newSiPrev').textContent=prev.toFixed(2)+' '+unit;
    document.getElementById('newSiPurchased').textContent='+'+qty.toFixed(2)+' '+unit;
    document.getElementById('newSiNew').textContent=(prev+qty).toFixed(2)+' '+unit;
    preview.style.display='';
}
function confirmSI(){
    const sel=document.getElementById('newSiItem');const opt=sel.selectedOptions[0];
    const qty=parseFloat(document.getElementById('newSiQty').value)||0;
    if(!opt?.value||!qty)return true;
    const prev=parseFloat(opt.dataset.qty)||0;
    return confirm('Stock-In Confirmation\n\nItem: '+opt.text.split('(')[0].trim()+'\nPrevious: '+prev.toFixed(2)+' '+opt.dataset.unit+'\nPurchased: +'+qty.toFixed(2)+' '+opt.dataset.unit+'\nNew Total: '+(prev+qty).toFixed(2)+' '+opt.dataset.unit+'\n\nAre you sure you want to confirm this stock-in transaction?');
}
</script>
@endsection
