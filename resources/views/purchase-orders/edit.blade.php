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
.badge-po-ok{background:#F0FDF4;color:#15803D;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
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

.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:#fff;border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 64px rgba(0,0,0,.18)}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.modal-hd h3 i{color:var(--primary)}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:#F8FAFC;cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:#FEE2E2;color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;box-sizing:border-box}
.field input:focus,.field select:focus{border-color:var(--primary)}
.field-hint{font-size:.76rem;color:var(--muted);margin-top:.3rem}
.error-msg{background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;padding:.7rem 1rem;font-size:.8rem;color:#B91C1C;margin-bottom:1.1rem}

/* Actual purchase (receiving) columns */
.po-table th.rcv-col{background:#EFF6FF;color:#1D4ED8}
.po-table td.rcv-col{background:#F8FBFF}
.rcv-input{width:100px}
.rcv-input:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.peso-wrap{display:flex;align-items:center;gap:.25rem;font-weight:700;color:var(--muted)}
.unit-cost-hint{font-size:.7rem;color:var(--muted);text-align:center;min-height:1em}
.badge-rcv-pending,.badge-rcv-match,.badge-rcv-short,.badge-rcv-over,.badge-rcv-none{padding:.2rem .55rem;border-radius:50px;font-size:.66rem;font-weight:700;text-transform:uppercase;display:inline-block;white-space:nowrap}
.badge-rcv-pending{background:#F3F4F6;color:#6B7280}
.badge-rcv-match{background:#DCFCE7;color:#15803D}
.badge-rcv-short{background:#FEF3C7;color:#B45309}
.badge-rcv-over{background:#DBEAFE;color:#1D4ED8}
.badge-rcv-none{background:#FEE2E2;color:#B91C1C}
.steps{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem}
.step{flex:1;min-width:200px;background:#fff;border:1px solid var(--border);border-radius:12px;padding:.75rem 1rem;font-size:.78rem;color:var(--muted);display:flex;gap:.65rem;align-items:flex-start}
.step-num{width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;font-weight:800;font-size:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step strong{color:var(--dark);display:block;font-size:.8rem}
.summary-row{display:flex;gap:2rem;flex-wrap:wrap;padding:1rem 1.4rem;font-size:.85rem}
.summary-row .lbl{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.summary-row .val{font-size:1.1rem;font-weight:800;color:var(--dark)}
</style>
@endsection

@section('content')
<div class="po-page">

    <div class="po-header">
        <div>
            <div class="po-title"><i class="fas fa-file-pen"></i> {{ $po->po_number }}</div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.25rem">Plan the order, print it, then record what was actually bought</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <button type="button" class="btn btn-primary" onclick="openLocalModal('addPoItemModal')"><i class="fas fa-plus"></i> Add Item</button>
            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank" class="btn btn-outline" title="Save your changes first so the printout is up to date"><i class="fas fa-print"></i> Print</a>
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

    {{-- Workflow guide --}}
    <div class="steps">
        <div class="step"><span class="step-num">1</span><div><strong>Plan the order</strong>Adjust quantities, add or remove items, then Save.</div></div>
        <div class="step"><span class="step-num">2</span><div><strong>Print &amp; buy</strong>Print the PO and bring it when buying the items.</div></div>
        <div class="step"><span class="step-num">3</span><div><strong>Record the purchase</strong>Enter the quantity bought and amount paid per item (0 if not bought).</div></div>
        <div class="step"><span class="step-num">4</span><div><strong>Finalize &amp; stock in</strong>Finalize, then click Record Stock-In to update inventory.</div></div>
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
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Recommended</th>
                            <th>Qty to Purchase *</th>
                            <th>Status</th>
                            <th class="rcv-col">Qty Bought</th>
                            <th class="rcv-col">Amount Paid</th>
                            <th class="rcv-col">Check</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rtcItems as $i => $item)
                        @php $rtcMin = max((float) $item->threshold, 0.01); @endphp
                        <tr>
                            <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                            <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                            <td class="ro-val">{{ number_format($item->current_stock,2) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->threshold,2) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->quantity_recommended,2) }} {{ $item->unit }}</td>
                            <td>
                                <div class="qty-wrap">
                                    <input type="number" name="quantities[{{ $item->id }}]"
                                           value="{{ old('quantities.'.$item->id, number_format($item->quantity_to_purchase,2,'.','')) }}"
                                           class="qty-input" step="0.01" min="{{ number_format($rtcMin,2,'.','') }}" required
                                           data-original="{{ number_format($item->quantity_to_purchase,2,'.','') }}"
                                           title="Must be at least the minimum threshold ({{ number_format($rtcMin,2) }} {{ $item->unit }})"
                                           onchange="markChanged(this)" oninput="markChanged(this)">
                                    <span class="field-hint" style="margin:0">Min: {{ number_format($rtcMin,2) }} {{ $item->unit }}</span>
                                    <span class="changed-hint" id="hint-{{ $item->id }}"><i class="fas fa-check-circle"></i> Modified</span>
                                </div>
                            </td>
                            <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                            @include('purchase-orders.partials.receiving-cells', ['item' => $item, 'step' => '0.01', 'decimals' => 2])
                            <td>
                                <button type="button" class="act-btn act-danger" title="Remove item" aria-label="Remove item" onclick="openModal({
                                        type: 'danger',
                                        iconClass: 'fas fa-trash',
                                        title: 'Remove Item?',
                                        desc: 'Remove ' + {{ Js::from($item->item_name) }} + ' from this purchase order?',
                                        action: '{{ route('purchase-orders.items.destroy', [$po, $item]) }}',
                                        method: 'DELETE',
                                        confirmText: 'Remove'
                                    })"><i class="fas fa-trash"></i></button>
                            </td>
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
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Recommended</th>
                            <th>Qty to Purchase *</th>
                            <th>Status</th>
                            <th class="rcv-col">Qty Bought</th>
                            <th class="rcv-col">Amount Paid</th>
                            <th class="rcv-col">Check</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bevItems as $i => $item)
                        @php $bevMin = max((float) $item->threshold, 1); @endphp
                        <tr>
                            <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                            <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                            <td class="ro-val">{{ number_format($item->current_stock,0) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->threshold,0) }} {{ $item->unit }}</td>
                            <td class="ro-val">{{ number_format($item->quantity_recommended,0) }} {{ $item->unit }}</td>
                            <td>
                                <div class="qty-wrap">
                                    <input type="number" name="quantities[{{ $item->id }}]"
                                           value="{{ old('quantities.'.$item->id, number_format($item->quantity_to_purchase,0,'.','')) }}"
                                           class="qty-input" step="1" min="{{ number_format($bevMin,0,'.','') }}" required
                                           data-original="{{ number_format($item->quantity_to_purchase,0,'.','') }}"
                                           title="Must be at least the minimum threshold ({{ number_format($bevMin,0) }} {{ $item->unit }})"
                                           onchange="markChanged(this)" oninput="markChanged(this)">
                                    <span class="field-hint" style="margin:0">Min: {{ number_format($bevMin,0) }} {{ $item->unit }}</span>
                                    <span class="changed-hint" id="hint-{{ $item->id }}"><i class="fas fa-check-circle"></i> Modified</span>
                                </div>
                            </td>
                            <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                            @include('purchase-orders.partials.receiving-cells', ['item' => $item, 'step' => '1', 'decimals' => 0])
                            <td>
                                <button type="button" class="act-btn act-danger" title="Remove item" aria-label="Remove item" onclick="openModal({
                                        type: 'danger',
                                        iconClass: 'fas fa-trash',
                                        title: 'Remove Item?',
                                        desc: 'Remove ' + {{ Js::from($item->item_name) }} + ' from this purchase order?',
                                        action: '{{ route('purchase-orders.items.destroy', [$po, $item]) }}',
                                        method: 'DELETE',
                                        confirmText: 'Remove'
                                    })"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($rtcItems->isEmpty() && $bevItems->isEmpty())
        <div class="card">
            <div style="padding:2.5rem;text-align:center;color:var(--muted);font-size:.85rem">
                <i class="fas fa-boxes-stacked" style="font-size:1.6rem;display:block;margin-bottom:.6rem;opacity:.35"></i>
                No items on this purchase order yet.<br>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:.9rem" onclick="openLocalModal('addPoItemModal')"><i class="fas fa-plus"></i> Add Item</button>
            </div>
        </div>
        @endif

        {{-- Purchase summary + action bar --}}
        <div class="card">
            <div class="card-hd"><h3><i class="fas fa-receipt"></i> Actual Purchase Summary</h3></div>
            <div class="summary-row">
                <div><div class="lbl">Items Recorded</div><div class="val"><span id="sumRecorded">0</span> / {{ $po->items->count() }}</div></div>
                <div><div class="lbl">Items Bought</div><div class="val" id="sumBought">0</div></div>
                <div><div class="lbl">Total Amount Paid</div><div class="val" style="color:var(--primary)">₱<span id="sumPaid">0.00</span></div></div>
            </div>
            <div class="action-bar">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                <div class="divider"></div>
                <button type="submit" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary)">
                    <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
                <button type="button" class="btn btn-green" onclick="doFinalize()">
                    <i class="fas fa-check-double"></i> Save &amp; Finalize
                </button>
            </div>
        </div>
        <input type="hidden" name="intent" id="formIntent" value="save">
    </form>

</div>

{{-- Add Item modal --}}
<div class="modal-backdrop" id="addPoItemModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-plus"></i> Add Item to Purchase Order</h3>
            <button class="modal-close-btn" onclick="closeLocalModal('addPoItemModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('purchase-orders.items.store', $po) }}">
            @csrf
            <div class="modal-body">
                @if($errors->has('inventory_item_id') || $errors->has('quantity_to_purchase'))
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> {{ $errors->first('inventory_item_id') ?: $errors->first('quantity_to_purchase') }}</div>
                @endif

                <div class="field">
                    <label>Inventory Item *</label>
                    <select name="inventory_item_id" id="addPoItemSelect" required onchange="onAddPoItemChange()" {{ $availableItems->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Select item…</option>
                        @forelse($availableItems as $ai)
                        <option value="{{ $ai->id }}" data-unit="{{ $ai->unit }}" data-stock="{{ number_format($ai->quantity,2) }}"
                                data-threshold="{{ number_format(max((float) $ai->reorder_level, 0.01),2,'.','') }}"
                                {{ (string) old('inventory_item_id') === (string) $ai->id ? 'selected' : '' }}>
                            {{ $ai->item_name }} ({{ $ai->item_type === 'rtc' ? 'RTC Raw Meat' : 'Beverage' }})
                        </option>
                        @empty
                        <option value="" disabled>No additional inventory items available</option>
                        @endforelse
                    </select>
                    <div class="field-hint" id="addPoItemStockHint">&nbsp;</div>
                </div>
                <div class="field" style="margin-bottom:0">
                    <label>Quantity to Purchase *</label>
                    <input type="number" name="quantity_to_purchase" id="addPoItemQty" step="0.01" min="0.01" required
                           value="{{ old('quantity_to_purchase') }}" placeholder="0.00" {{ $availableItems->isEmpty() ? 'disabled' : '' }}>
                    <div class="field-hint">Unit: <span id="addPoItemUnit">—</span> &nbsp;|&nbsp; Min: <span id="addPoItemMin">—</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('addPoItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" {{ $availableItems->isEmpty() ? 'disabled' : '' }}><i class="fas fa-plus"></i> Add Item</button>
            </div>
        </form>
    </div>
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
    var missing = document.querySelectorAll('.js-rcv-badge.badge-rcv-pending').length;
    if (missing > 0) {
        openModal({
            type: 'warn',
            iconClass: 'fas fa-circle-exclamation',
            title: 'Purchase Not Fully Recorded',
            desc: missing + ' item(s) still need the quantity bought and amount paid. Enter 0 bought for items that were not purchased.',
            confirmText: 'OK',
            onConfirm: function () {}
        });
        return;
    }
    if (!document.getElementById('editForm').reportValidity()) return;

    openModal({
        type: 'warn',
        iconClass: 'fas fa-check-double',
        title: 'Finalize Purchase Order?',
        desc: 'Your entries will be saved and "' + {{ Js::from($po->po_number) }} + '" will become read-only. You can then record the stock-in.',
        confirmText: 'Save & Finalize',
        onConfirm: function () {
            document.getElementById('formIntent').value = 'finalize';
            document.getElementById('editForm').submit();
        }
    });
}

/* ── Actual purchase: live check of bought vs ordered ── */
var RCV_CLASSES = ['badge-rcv-pending', 'badge-rcv-match', 'badge-rcv-short', 'badge-rcv-over', 'badge-rcv-none'];

function receivingStatus(row) {
    var orderedEl = row.querySelector('input[name^="quantities"]');
    var recvEl    = row.querySelector('.js-received');
    var paidEl    = row.querySelector('.js-paid');
    if (recvEl.value === '') return ['badge-rcv-pending', 'Pending'];

    var received = parseFloat(recvEl.value) || 0;
    var ordered  = parseFloat(orderedEl.value) || 0;
    if (received <= 0) return ['badge-rcv-none', 'Not Bought'];
    if (paidEl.value === '') return ['badge-rcv-pending', 'Enter Amount'];
    if (Math.abs(received - ordered) < 0.0001) return ['badge-rcv-match', 'Complete'];
    return received < ordered ? ['badge-rcv-short', 'Short'] : ['badge-rcv-over', 'Over'];
}

function updateReceivingRow(input) {
    var row = input.closest('tr');
    var status = receivingStatus(row);
    var badge = row.querySelector('.js-rcv-badge');
    RCV_CLASSES.forEach(function (c) { badge.classList.remove(c); });
    badge.classList.add('js-rcv-badge', status[0]);
    badge.textContent = status[1];

    var received = parseFloat(row.querySelector('.js-received').value) || 0;
    var paid     = parseFloat(row.querySelector('.js-paid').value);
    var hint     = row.querySelector('.js-unit-cost');
    hint.textContent = (received > 0 && !isNaN(paid)) ? '₱' + (paid / received).toFixed(2) + ' / ' + hint.dataset.unit : '';

    updateSummary();
}

function updateSummary() {
    var recorded = 0, bought = 0, total = 0;
    document.querySelectorAll('.js-received').forEach(function (recvEl) {
        var row = recvEl.closest('tr');
        if (!row.querySelector('.js-rcv-badge').classList.contains('badge-rcv-pending')) recorded++;
        if ((parseFloat(recvEl.value) || 0) > 0) bought++;
        total += parseFloat(row.querySelector('.js-paid').value) || 0;
    });
    document.getElementById('sumRecorded').textContent = recorded;
    document.getElementById('sumBought').textContent = bought;
    document.getElementById('sumPaid').textContent = total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.querySelectorAll('.js-received').forEach(function (el) { updateReceivingRow(el); });
document.querySelectorAll('input[name^="quantities"]').forEach(function (el) {
    el.addEventListener('input', function () { updateReceivingRow(el); });
});

function openLocalModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeLocalModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-backdrop').forEach(function (el) {
    el.addEventListener('click', function (e) { if (e.target === el) closeLocalModal(el.id); });
});

function onAddPoItemChange() {
    var sel = document.getElementById('addPoItemSelect');
    var opt = sel.selectedOptions[0];
    var unit = opt ? opt.dataset.unit : '';
    var threshold = opt ? opt.dataset.threshold : '';
    var qtyInput = document.getElementById('addPoItemQty');

    document.getElementById('addPoItemUnit').textContent = unit || '—';
    document.getElementById('addPoItemMin').textContent = threshold || '—';
    document.getElementById('addPoItemStockHint').innerHTML = (opt && opt.dataset.stock)
        ? 'Current stock: ' + opt.dataset.stock + ' ' + unit
        : '&nbsp;';

    qtyInput.min = threshold || '0.01';
    if (threshold && parseFloat(qtyInput.value) < parseFloat(threshold)) {
        qtyInput.value = threshold;
    }
}

@if($errors->has('inventory_item_id') || $errors->has('quantity_to_purchase'))
openLocalModal('addPoItemModal');
@endif
</script>
@endsection
