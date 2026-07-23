@extends('layouts.admin')
@section('title', $po->po_number)
@section('page-title', $po->po_number)
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    <span class="breadcrumb-sep">/</span> {{ $po->po_number }}
@endsection

@section('styles')
<style>
.po-page{max-width:1100px;margin:0 auto}
.po-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.po-title{font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.7rem}
.po-title i{color:var(--primary)}
.info-bar{background:#fff;border-radius:14px;border:1px solid var(--border);padding:1.1rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:2.5rem;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.info-item{display:flex;flex-direction:column;gap:.18rem}
.info-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.info-value{font-size:.9rem;font-weight:700;color:var(--dark)}
.badge-po-draft{background:#FEF3C7;color:#B45309;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-finalized{background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-out{background:#FEE2E2;color:#B91C1C;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-po-low{background:#FEF3C7;color:#B45309;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
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
.badge-rtc{background:#EFF6FF;color:#1D4ED8;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-bev{background:#F5F3FF;color:#6D28D9;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase;display:inline-block}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.notes-box{padding:1.25rem 1.5rem;font-size:.86rem;color:var(--dark);background:#FFFBEB;border-left:3px solid var(--accent);line-height:1.6}
.action-bar{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;padding:1.1rem 1.5rem;border-top:1px solid var(--border)}
.divider{flex:1}
.finalized-notice{background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:12px;padding:.9rem 1.2rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;font-size:.84rem;color:#15803D;font-weight:600}
</style>
@endsection

@section('content')
<div class="po-page">

    <div class="po-header">
        <div>
            <div class="po-title">
                @if($po->isFinalized())
                <i class="fas fa-file-circle-check"></i>
                @else
                <i class="fas fa-file-pen"></i>
                @endif
                {{ $po->po_number }}
            </div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.3rem">
                Created {{ $po->created_at->format('F d, Y \a\t h:i A') }}
            </div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            @if($po->isDraft())
            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-outline"><i class="fas fa-pen"></i> Edit Draft</a>
            <button type="button" class="btn btn-green" onclick="openModal({
                    type: 'warn',
                    iconClass: 'fas fa-check-double',
                    title: 'Finalize Purchase Order?',
                    desc: 'Finalize ' + {{ Js::from($po->po_number) }} + '? This action cannot be undone.',
                    action: '{{ route('purchase-orders.finalize', $po) }}',
                    method: 'POST',
                    confirmText: 'Finalize'
                })"><i class="fas fa-check-double"></i> Finalize</button>
            @else
            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank" class="btn btn-blue"><i class="fas fa-print"></i> Print / Save PDF</a>
            @endif
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if($po->isFinalized())
    <div class="finalized-notice">
        <i class="fas fa-circle-check" style="font-size:1.1rem"></i>
        <div>This purchase order was finalized on <strong>{{ $po->finalized_at?->format('F d, Y h:i A') }}</strong>. It is now read-only.</div>
    </div>
    @endif

    {{-- Info Bar --}}
    <div class="info-bar">
        <div class="info-item"><div class="info-label">PO Number</div><div class="info-value" style="color:var(--primary)">{{ $po->po_number }}</div></div>
        <div class="info-item"><div class="info-label">Status</div><div class="info-value"><span class="{{ $po->status_badge_class }}"><i class="fas fa-circle" style="font-size:.4rem"></i> {{ $po->status_label }}</span></div></div>
        <div class="info-item"><div class="info-label">Total Items</div><div class="info-value">{{ $po->items->count() }}</div></div>
        <div class="info-item"><div class="info-label">Prepared By</div><div class="info-value">{{ $po->preparedBy?->name ?? 'Admin' }}</div></div>
        <div class="info-item"><div class="info-label">Date Created</div><div class="info-value">{{ $po->created_at->format('M d, Y') }}</div></div>
        @if($po->isFinalized())
        <div class="info-item"><div class="info-label">Finalized On</div><div class="info-value">{{ $po->finalized_at?->format('M d, Y h:i A') }}</div></div>
        @endif
    </div>

    @if($po->notes)
    <div class="card">
        <div class="card-hd"><h3><i class="fas fa-note-sticky"></i> Notes</h3></div>
        <div class="notes-box">{{ $po->notes }}</div>
    </div>
    @endif

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
                        <th>#</th><th>Item Name</th><th>Category</th>
                        <th>Current Stock</th><th>Threshold</th>
                        <th>Recommended</th><th>Qty to Purchase</th>
                        <th>Unit</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rtcItems as $i => $item)
                    <tr>
                        <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                        <td style="font-weight:700">{{ $item->item_name }}</td>
                        <td style="color:var(--muted)">{{ $item->category ?? '—' }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->current_stock,2) }} {{ $item->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->threshold,2) }} {{ $item->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->quantity_recommended,2) }} {{ $item->unit }}</td>
                        <td style="font-weight:800;color:var(--primary);font-size:.95rem">{{ number_format($item->quantity_to_purchase,2) }} <span style="font-weight:400;font-size:.78rem;color:var(--muted)">{{ $item->unit }}</span></td>
                        <td style="color:var(--muted)">{{ $item->unit }}</td>
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
                        <th>#</th><th>Item Name</th><th>Category</th>
                        <th>Current Stock</th><th>Threshold</th>
                        <th>Recommended</th><th>Qty to Purchase</th>
                        <th>Unit</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bevItems as $i => $item)
                    <tr>
                        <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                        <td style="font-weight:700">{{ $item->item_name }}</td>
                        <td style="color:var(--muted)">{{ $item->category ?? '—' }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->current_stock,0) }} {{ $item->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->threshold,0) }} {{ $item->unit }}</td>
                        <td style="color:var(--muted)">{{ number_format($item->quantity_recommended,0) }} {{ $item->unit }}</td>
                        <td style="font-weight:800;color:var(--primary);font-size:.95rem">{{ number_format($item->quantity_to_purchase,0) }} <span style="font-weight:400;font-size:.78rem;color:var(--muted)">{{ $item->unit }}</span></td>
                        <td style="color:var(--muted)">{{ $item->unit }}</td>
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
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
            @if($po->isDraft())
            <a href="{{ route('inventory.stock-in.index') }}" class="btn btn-outline"><i class="fas fa-arrow-down-to-bracket"></i> Record Stock-In</a>
            @endif
            <div class="divider"></div>
            @if($po->isFinalized())
            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank" class="btn btn-blue"><i class="fas fa-print"></i> Print Purchase Order</a>
            @else
            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary)"><i class="fas fa-pen"></i> Edit Quantities</a>
            <button type="button" class="btn btn-green" onclick="openModal({
                    type: 'warn',
                    iconClass: 'fas fa-check-double',
                    title: 'Finalize Purchase Order?',
                    desc: 'Finalize ' + {{ Js::from($po->po_number) }} + '? This cannot be undone.',
                    action: '{{ route('purchase-orders.finalize', $po) }}',
                    method: 'POST',
                    confirmText: 'Finalize'
                })"><i class="fas fa-check-double"></i> Finalize Purchase Order</button>
            @endif
        </div>
    </div>

</div>
@endsection
