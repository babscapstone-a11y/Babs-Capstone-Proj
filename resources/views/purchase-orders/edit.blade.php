@extends('layouts.admin')
@section('title', 'Edit Purchase Order')
@section('page-title', 'Edit Purchase Order')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    <span class="breadcrumb-sep">/</span> {{ $po->po_number }}
@endsection

@section('styles')
<style>
.po-page{max-width:1200px;margin:0 auto}
.po-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.po-title{font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.po-title i{color:var(--primary)}
.info-bar{background:#fff;border-radius:14px;border:1px solid var(--border);padding:1rem 1.4rem;margin-bottom:1.5rem;display:flex;gap:2rem;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.info-item{display:flex;flex-direction:column;gap:.15rem}
.info-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.info-value{font-size:.92rem;font-weight:700;color:var(--dark)}
.badge-po-draft{background:#FEF3C7;color:#B45309;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-out{background:#FEE2E2;color:#B91C1C;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-po-low{background:#FEF3C7;color:#B45309;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-rtc{background:#EFF6FF;color:#1D4ED8;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-bev{background:#F5F3FF;color:#6D28D9;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase;display:inline-block}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.25rem}
.card-hd{padding:.9rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;background:#FAFBFC}
.card-hd h3{font-size:.9rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.card-hd h3 i{color:var(--primary)}
.tbl-wrap{overflow-x:auto}
.po-table{width:100%;border-collapse:collapse;font-size:.83rem}
.po-table th{padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.po-table td{padding:.85rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.po-table tr:last-child td{border-bottom:none}
.po-table tr:hover td{background:#FAFAFA}
.qty-input{width:90px;padding:.45rem .65rem;border:1.5px solid var(--border);border-radius:9px;font-size:.86rem;font-family:inherit;font-weight:700;color:var(--dark);outline:none;text-align:center;background:#fff}
.qty-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(220,38,38,.1)}
.qty-input.changed{border-color:#16A34A;background:#F0FDF4}
.notes-area{width:100%;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:12px;font-size:.85rem;font-family:inherit;color:var(--dark);outline:none;resize:vertical;min-height:90px}
.notes-area:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(220,38,38,.08)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff;box-shadow:0 3px 10px rgba(220,38,38,.2)}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.action-bar{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;padding:1.1rem 1.4rem;border-top:1px solid var(--border);background:#FAFBFC}
.divider{flex:1}
.ro-val{font-size:.85rem;color:var(--muted)}
.changed-hint{font-size:.72rem;color:#16A34A;display:none}
.qty-wrap{display:flex;flex-direction:column;align-items:center;gap:.2rem}
</style>
@endsection

@section('content')
<div class="po-page">

    <div class="po-header">
        <div>
            <div class="po-title"><i class="fas fa-file-pen"></i> {{ $po->po_number }}</div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.25rem">Review and edit quantities before finalizing</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-outline"><i class="fas fa-eye"></i> View</a>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Info Bar --}}
    <div class="info-bar">
        <div class="info-item"><div class="info-label">PO Number</div><div class="info-value">{{ $po->po_number }}</div></div>
        <div class="info-item"><div class="info-label">Status</div><div class="info-value"><span class="badge-po-draft"><i class="fas fa-circle" style="font-size:.45rem"></i> Draft</span></div></div>
        <div class="info-item"><div class="info-label">Total Items</div><div class="info-value">{{ $po->items->count() }}</div></div>
        <div class="info-item"><div class="info-label">Created</div><div class="info-value">{{ $po->created_at->format('M d, Y h:i A') }}</div></div>
        <div class="info-item"><div class="info-label">Prepared By</div><div class="info-value">{{ $po->preparedBy?->name ?? 'Admin' }}</div></div>
    </div>

    @if($errors->any())
    <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.25rem;font-size:.83rem;color:#B91C1C"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('purchase-orders.update', $po) }}" id="editForm">
        @csrf @method('PUT')

        {{-- Notes --}}
        <div class="card">
            <div class="card-hd"><h3><i class="fas fa-note-sticky"></i> Purchase Order Notes</h3></div>
            <div style="padding:1.25rem 1.4rem">
                <textarea name="notes" class="notes-area" placeholder="Add notes or instructions for this purchase order (optional)…">{{ old('notes', $po->notes) }}</textarea>
            </div>
        </div>

        {{-- RTC Items --}}
        @php $rtcItems = $po->items->where('item_type', 'rtc'); @endphp
        @if($rtcItems->isNotEmpty())
        <div class="card">
            <div class="card-hd">
                <h3><i class="fas fa-drumstick-bite"></i> RTC Raw Meat Items</h3>
                <span style="font-size:.78rem;color:var(--muted)">{{ $rtcItems->count() }} item(s)</span>
            </div>
            <div class="tbl-wrap">
                <table class="po-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Recommended</th>
                            <th>Qty to Purchase *</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rtcItems as $i => $item)
                        <tr>
                            <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                            <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                            <td class="ro-val">{{ $item->category ?? '—' }}</td>
                            <td class="ro-val">{{ number_format($item->current_stock,2) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->threshold,2) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->quantity_recommended,2) }} {{ $item->unit }}</td>
                            <td>
                                <div class="qty-wrap">
                                    <input type="number" name="quantities[{{ $item->id }}]"
                                           value="{{ old('quantities.'.$item->id, number_format($item->quantity_to_purchase,2,'.','')) }}"
                                           class="qty-input" step="0.01" min="0.01" required
                                           data-original="{{ number_format($item->quantity_to_purchase,2,'.','') }}"
                                           onchange="markChanged(this)" oninput="markChanged(this)">
                                    <span class="changed-hint" id="hint-{{ $item->id }}"><i class="fas fa-check-circle"></i> Modified</span>
                                </div>
                            </td>
                            <td class="ro-val">{{ $item->unit }}</td>
                            <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Beverage Items --}}
        @php $bevItems = $po->items->where('item_type', 'beverage'); @endphp
        @if($bevItems->isNotEmpty())
        <div class="card">
            <div class="card-hd">
                <h3><i class="fas fa-bottle-water"></i> Beverage Items</h3>
                <span style="font-size:.78rem;color:var(--muted)">{{ $bevItems->count() }} item(s)</span>
            </div>
            <div class="tbl-wrap">
                <table class="po-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Recommended</th>
                            <th>Qty to Purchase *</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bevItems as $i => $item)
                        <tr>
                            <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                            <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                            <td class="ro-val">{{ $item->category ?? '—' }}</td>
                            <td class="ro-val">{{ number_format($item->current_stock,0) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->threshold,0) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->quantity_recommended,0) }} {{ $item->unit }}</td>
                            <td>
                                <div class="qty-wrap">
                                    <input type="number" name="quantities[{{ $item->id }}]"
                                           value="{{ old('quantities.'.$item->id, number_format($item->quantity_to_purchase,0,'.','')) }}"
                                           class="qty-input" step="1" min="1" required
                                           data-original="{{ number_format($item->quantity_to_purchase,0,'.','') }}"
                                           onchange="markChanged(this)" oninput="markChanged(this)">
                                    <span class="changed-hint" id="hint-{{ $item->id }}"><i class="fas fa-check-circle"></i> Modified</span>
                                </div>
                            </td>
                            <td class="ro-val">{{ $item->unit }}</td>
                            <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Action bar --}}
        <div class="card">
            <div class="action-bar">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                <div class="divider"></div>
                <button type="submit" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary)">
                    <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
                <button type="button" class="btn btn-green" onclick="doFinalize()">
                    <i class="fas fa-check-double"></i> Finalize Purchase Order
                </button>
            </div>
        </div>
    </form>

    {{-- Finalize hidden form --}}
    <form method="POST" action="{{ route('purchase-orders.finalize', $po) }}" id="finalizeForm">@csrf</form>

</div>
@endsection

@section('scripts')
<script>
function markChanged(input) {
    const orig = input.dataset.original;
    const cur = parseFloat(input.value) || 0;
    const hintId = input.closest('tr').querySelector('.changed-hint');
    const isChanged = Math.abs(cur - parseFloat(orig)) > 0.001;
    input.classList.toggle('changed', isChanged);
    if (hintId) hintId.style.display = isChanged ? 'block' : 'none';
}

function doFinalize() {
    if (!confirm('Are you sure you want to finalize this Purchase Order?\n\n"' + {{ Js::from($po->po_number) }} + '"\n\nOnce finalized, it will become read-only and cannot be edited.')) return;
    // Save first, then finalize
    document.getElementById('finalizeForm').submit();
}
</script>
@endsection
