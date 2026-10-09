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
.po-page{max-width:1200px;margin:0 auto}
.po-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.po-title{font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.7rem}
.po-title i{color:var(--primary)}
.info-bar{background:#fff;border-radius:14px;border:1px solid var(--border);padding:1.1rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:2.5rem;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.info-item{display:flex;flex-direction:column;gap:.18rem}
.info-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.info-value{font-size:.9rem;font-weight:700;color:var(--dark)}
.badge-po-draft{background:#FEF3C7;color:#B45309;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-finalized{background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-stocked{background:#DBEAFE;color:#1D4ED8;display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.badge-po-out{background:#FEE2E2;color:#B91C1C;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-po-low{background:#FEF3C7;color:#B45309;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-po-ok{background:#F0FDF4;color:#15803D;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase;display:inline-block}
.badge-rcv-pending,.badge-rcv-match,.badge-rcv-short,.badge-rcv-over,.badge-rcv-none{padding:.2rem .55rem;border-radius:50px;font-size:.66rem;font-weight:700;text-transform:uppercase;display:inline-block;white-space:nowrap}
.badge-rcv-pending{background:#F3F4F6;color:#6B7280}
.badge-rcv-match{background:#DCFCE7;color:#15803D}
.badge-rcv-short{background:#FEF3C7;color:#B45309}
.badge-rcv-over{background:#DBEAFE;color:#1D4ED8}
.badge-rcv-none{background:#FEE2E2;color:#B91C1C}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.25rem}
.card-hd{padding:.9rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;background:#FAFBFC}
.card-hd h3{font-size:.9rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.card-hd h3 i{color:var(--primary)}
.tbl-wrap{overflow-x:auto}
.po-table{width:100%;border-collapse:collapse;font-size:.83rem}
.po-table th{padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.po-table th.rcv-col{background:#EFF6FF;color:#1D4ED8}
.po-table td.rcv-col{background:#F8FBFF}
.po-table td{padding:.85rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.po-table tr:last-child td{border-bottom:none}
.po-table tfoot td{border-top:2px solid var(--border);font-weight:800;background:#FAFBFC}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.notes-box{padding:1.25rem 1.5rem;font-size:.86rem;color:var(--dark);background:#FFFBEB;border-left:3px solid var(--accent);line-height:1.6}
.action-bar{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;padding:1.1rem 1.5rem;border-top:1px solid var(--border)}
.divider{flex:1}
.notice{border-radius:12px;padding:.9rem 1.2rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;font-size:.84rem;font-weight:600;flex-wrap:wrap}
.notice-green{background:#F0FDF4;border:1.5px solid #86EFAC;color:#15803D}
.notice-amber{background:#FFFBEB;border:1.5px solid #FCD34D;color:#B45309}
.notice-blue{background:#EFF6FF;border:1.5px solid #93C5FD;color:#1D4ED8}
.notice .grow{flex:1;min-width:220px}
</style>
@endsection

@section('content')
@php
    $stockInConfirm = [
        'type'        => 'warn',
        'iconClass'   => 'fas fa-arrow-down-to-bracket',
        'title'       => 'Record Stock-In?',
        'desc'        => "All bought items on {$po->po_number} (" . $po->items->where('quantity_received', '>', 0)->count() . " item(s), ₱" . number_format($po->total_amount_paid, 2) . ") will be added to inventory. This can only be done once.",
        'action'      => route('purchase-orders.stock-in', $po),
        'method'      => 'POST',
        'confirmText' => 'Record Stock-In',
    ];
@endphp
<div class="po-page">

    <div class="po-header">
        <div>
            <div class="po-title">
                @if($po->isStockedIn())
                <i class="fas fa-boxes-stacked"></i>
                @elseif($po->isFinalized())
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
            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank" class="btn btn-blue"><i class="fas fa-print"></i> Print / Save PDF</a>
            @if($po->isDraft())
            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-outline"><i class="fas fa-pen"></i> Edit Draft</a>
            @elseif($po->awaitingStockIn())
            <button type="button" class="btn btn-green" onclick="openModal({{ Js::from($stockInConfirm) }})"><i class="fas fa-arrow-down-to-bracket"></i> Record Stock-In</button>
            @endif
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if($po->isStockedIn())
    <div class="notice notice-blue">
        <i class="fas fa-boxes-stacked" style="font-size:1.1rem"></i>
        <div class="grow">Items were stocked in on <strong>{{ $po->stocked_in_at?->format('F d, Y h:i A') }}</strong> by {{ $po->stockedInBy?->name ?? 'Admin' }}. Inventory has been updated.</div>
        <a href="{{ route('inventory.stock-in.index') }}" class="btn btn-outline" style="font-size:.78rem;padding:.4rem .8rem"><i class="fas fa-clock-rotate-left"></i> Stock-In History</a>
    </div>
    @elseif($po->awaitingStockIn())
    <div class="notice notice-amber">
        <i class="fas fa-circle-info" style="font-size:1.1rem"></i>
        <div class="grow">Finalized on <strong>{{ $po->finalized_at?->format('F d, Y h:i A') }}</strong>. The bought items have <strong>not been added to inventory yet</strong>.</div>
        <button type="button" class="btn btn-green" onclick="openModal({{ Js::from($stockInConfirm) }})"><i class="fas fa-arrow-down-to-bracket"></i> Record Stock-In</button>
    </div>
    @else
    <div class="notice notice-amber">
        <i class="fas fa-file-pen" style="font-size:1.1rem"></i>
        <div class="grow">This is a draft. After buying the items, open <strong>Edit Draft</strong> to enter the quantity bought and amount paid, then finalize.</div>
    </div>
    @endif

    {{-- Info Bar --}}
    <div class="info-bar">
        <div class="info-item"><div class="info-label">PO Number</div><div class="info-value" style="color:var(--primary)">{{ $po->po_number }}</div></div>
        <div class="info-item"><div class="info-label">Status</div><div class="info-value"><span class="{{ $po->status_badge_class }}"><i class="fas fa-circle" style="font-size:.4rem"></i> {{ $po->status_label }}</span></div></div>
        <div class="info-item"><div class="info-label">Total Items</div><div class="info-value">{{ $po->items->count() }}</div></div>
        <div class="info-item"><div class="info-label">Total Amount Paid</div><div class="info-value">₱{{ number_format($po->total_amount_paid, 2) }}</div></div>
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

    @include('purchase-orders.partials.show-items', [
        'items' => $po->items->where('item_type', 'rtc'),
        'title' => 'RTC Raw Meat Items', 'icon' => 'fa-drumstick-bite', 'decimals' => 2,
    ])
    @include('purchase-orders.partials.show-items', [
        'items' => $po->items->where('item_type', 'beverage'),
        'title' => 'Beverage Items', 'icon' => 'fa-bottle-water', 'decimals' => 0,
    ])

    {{-- Action bar --}}
    <div class="card">
        <div class="action-bar">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to List</a>
            <div class="divider"></div>
            <a href="{{ route('purchase-orders.print', $po) }}" target="_blank" class="btn btn-blue"><i class="fas fa-print"></i> Print Purchase Order</a>
            @if($po->isDraft())
            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn btn-outline" style="border-color:var(--primary);color:var(--primary)"><i class="fas fa-pen"></i> Edit / Record Purchase</a>
            @elseif($po->awaitingStockIn())
            <button type="button" class="btn btn-green" onclick="openModal({{ Js::from($stockInConfirm) }})"><i class="fas fa-arrow-down-to-bracket"></i> Record Stock-In</button>
            @endif
        </div>
    </div>

</div>
@endsection
