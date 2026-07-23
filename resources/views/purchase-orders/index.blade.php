@extends('layouts.admin')
@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span> Purchase Orders
@endsection

@section('styles')
<style>
.po-page{padding:0;max-width:1400px;margin:0 auto}
.po-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.po-title{font-size:1.45rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.po-title i{color:var(--primary)}
.po-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.1rem;margin-bottom:1.75rem}
.stat-card{background:#fff;border-radius:16px;padding:1.25rem 1.4rem;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem}
.stat-icon-wrap{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.si-blue{background:#EFF6FF;color:#2563EB}.si-green{background:#F0FDF4;color:#16A34A}.si-amber{background:#FFFBEB;color:#D97706}.si-red{background:#FEF2F2;color:#DC2626}
.stat-content .stat-val{font-size:1.75rem;font-weight:900;color:var(--dark);line-height:1}
.stat-content .stat-lbl{font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem}
.grid-2{display:grid;grid-template-columns:1fr 1.4fr;gap:1.25rem;margin-bottom:1.75rem}
@media(max-width:900px){.grid-2{grid-template-columns:1fr}}
.po-card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
.po-card-hd{padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#FAFBFC}
.po-card-hd h3{font-size:.88rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.po-card-hd h3 i{color:var(--primary)}
.mini-table{width:100%;border-collapse:collapse;font-size:.81rem}
.mini-table th{padding:.55rem .9rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.mini-table td{padding:.7rem .9rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.mini-table tr:last-child td{border-bottom:none}
.mini-table tr:hover td{background:#FAFAFA}
.badge-po-draft{background:#FEF3C7;color:#B45309;display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-finalized{background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-out{background:#FEE2E2;color:#B91C1C;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-low{background:#FEF3C7;color:#B45309;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-rtc{background:#EFF6FF;color:#1D4ED8;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.badge-po-bev{background:#F5F3FF;color:#6D28D9;padding:.18rem .5rem;border-radius:6px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.filter-row{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.1rem}
.search-box{position:relative;flex:1;min-width:200px}
.search-box i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
.search-input{width:100%;padding:.55rem .9rem .55rem 2.25rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.search-input:focus{border-color:var(--primary)}
.flt-select,.flt-date{padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.btn{display:inline-flex;align-items:center;gap:.42rem;padding:.52rem 1.05rem;border-radius:10px;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff;box-shadow:0 3px 10px rgba(220,38,38,.2)}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.btn-sm{padding:.35rem .7rem;font-size:.76rem}
.full-card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
.full-table{width:100%;border-collapse:collapse;font-size:.83rem}
.full-table th{padding:.65rem 1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.full-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.full-table tr:last-child td{border-bottom:none}
.full-table tr:hover td{background:#FAFAFA}
.empty-msg{text-align:center;color:var(--muted);padding:2.5rem;font-size:.84rem}
.quick-actions{display:flex;gap:.75rem;flex-wrap:wrap;padding:1.1rem 1.25rem;border-top:1px solid var(--border);background:#FAFBFC}
.tbl-wrap{overflow-x:auto}
</style>
@endsection

@section('content')
<div class="po-page">

    <div class="po-header">
        <div>
            <div class="po-title"><i class="fas fa-file-invoice"></i> Purchase Orders</div>
            <div class="po-sub">Manage and finalize inventory repurchase orders</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.restocking') }}" class="btn btn-outline"><i class="fas fa-boxes-stacked"></i> View Restocking List</a>
            @if($lowStockCount + $outOfStockCount > 0)
            <button type="button" class="btn btn-primary" onclick="openModal({
                    type: 'warn',
                    iconClass: 'fas fa-wand-magic-sparkles',
                    title: 'Generate Purchase Order?',
                    desc: 'This will create a new draft Purchase Order from all current low/out-of-stock items.',
                    action: '{{ route('purchase-orders.generate') }}',
                    method: 'POST',
                    confirmText: 'Generate'
                })">
                <i class="fas fa-wand-magic-sparkles"></i> Generate Purchase Order
            </button>
            @else
            <button type="button" class="btn btn-primary" disabled title="No low/out-of-stock items to restock" style="opacity:.5;cursor:not-allowed">
                <i class="fas fa-wand-magic-sparkles"></i> Generate Purchase Order
            </button>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon-wrap si-amber"><i class="fas fa-file-pen"></i></div>
            <div class="stat-content"><div class="stat-val">{{ $draftCount }}</div><div class="stat-lbl">Draft Orders</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap si-green"><i class="fas fa-file-circle-check"></i></div>
            <div class="stat-content"><div class="stat-val">{{ $finalizedCount }}</div><div class="stat-lbl">Finalized Orders</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap si-amber"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="stat-content"><div class="stat-val">{{ $lowStockCount }}</div><div class="stat-lbl">Low Stock Items</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap si-red"><i class="fas fa-circle-xmark"></i></div>
            <div class="stat-content"><div class="stat-val">{{ $outOfStockCount }}</div><div class="stat-lbl">Out of Stock</div></div>
        </div>
    </div>

    {{-- Two-column: Recent POs + Needs Restocking --}}
    <div class="grid-2">
        {{-- Recent Purchase Orders --}}
        <div class="po-card">
            <div class="po-card-hd">
                <h3><i class="fas fa-clock-rotate-left"></i> Recent Purchase Orders</h3>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <table class="mini-table">
                <thead><tr><th>PO Number</th><th>Status</th><th>Items</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($recentOrders as $o)
                    <tr>
                        <td><a href="{{ route('purchase-orders.show', $o) }}" style="color:var(--primary);font-weight:700">{{ $o->po_number }}</a></td>
                        <td><span class="{{ $o->status_badge_class }}">{{ $o->status_label }}</span></td>
                        <td>{{ $o->total_items }}</td>
                        <td style="color:var(--muted);font-size:.78rem">{{ $o->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-msg">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Needs Restocking --}}
        <div class="po-card">
            <div class="po-card-hd">
                <h3><i class="fas fa-cart-shopping"></i> Needs Restocking</h3>
                <a href="{{ route('inventory.restocking') }}" class="btn btn-outline btn-sm">Full List</a>
            </div>
            @if($needsRestocking->isNotEmpty())
            <table class="mini-table">
                <thead><tr><th>Item</th><th>Type</th><th>Current Qty</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($needsRestocking as $item)
                    <tr>
                        <td><div style="font-weight:600">{{ $item->item_name }}</div><div style="font-size:.72rem;color:var(--muted)">{{ $item->category }}</div></td>
                        <td><span class="{{ $item->item_type === 'rtc' ? 'badge-po-rtc' : 'badge-po-bev' }}">{{ $item->item_type === 'rtc' ? 'RTC' : 'BEV' }}</span></td>
                        <td style="font-weight:700;color:{{ $item->stock_status === 'out_of_stock' ? '#DC2626' : '#D97706' }}">{{ number_format($item->quantity,0) }} {{ $item->unit }}</td>
                        <td><span class="{{ $item->stock_status === 'out_of_stock' ? 'badge-po-out' : 'badge-po-low' }}">{{ $item->stock_status_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-msg">
                <i class="fas fa-circle-check" style="font-size:1.4rem;color:#16A34A;display:block;margin-bottom:.5rem"></i>
                All inventory items are sufficiently stocked.
            </div>
            @endif
            @if($lowStockCount + $outOfStockCount > 0)
            <div class="quick-actions">
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal({
                        type: 'warn',
                        iconClass: 'fas fa-wand-magic-sparkles',
                        title: 'Generate Purchase Order?',
                        desc: 'This will create a draft Purchase Order from all low/out-of-stock items.',
                        action: '{{ route('purchase-orders.generate') }}',
                        method: 'POST',
                        confirmText: 'Generate'
                    })">
                    <i class="fas fa-wand-magic-sparkles"></i> Generate PO Draft
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Full PO Table --}}
    <div class="full-card">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:#FAFBFC;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
            <h3 style="font-size:.92rem;font-weight:700;margin:0;color:var(--dark)"><i class="fas fa-list" style="color:var(--primary);margin-right:.4rem"></i>All Purchase Orders</h3>
        </div>

        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border)">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="filter-row">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search PO number…" class="search-input">
                </div>
                <select name="status" class="flt-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="draft"     {{ request('status')==='draft'     ? 'selected':'' }}>Draft</option>
                    <option value="finalized" {{ request('status')==='finalized' ? 'selected':'' }}>Finalized</option>
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="flt-date" title="From">
                <input type="date" name="to"   value="{{ request('to') }}"   class="flt-date" title="To">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                @if(request()->hasAny(['q','status','from','to']))
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                @endif
            </form>
        </div>

        <div class="tbl-wrap">
            <table class="full-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Date Created</th>
                        <th>Total Items</th>
                        <th>Status</th>
                        <th>Prepared By</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('purchase-orders.show', $order) }}" style="font-weight:700;color:var(--primary)">{{ $order->po_number }}</a>
                        </td>
                        <td style="color:var(--muted);font-size:.8rem">{{ $order->created_at->format('M d, Y') }}<br><span style="font-size:.72rem">{{ $order->created_at->format('h:i A') }}</span></td>
                        <td><span style="font-weight:700;background:#F3F4F6;padding:.2rem .6rem;border-radius:6px">{{ $order->total_items }}</span></td>
                        <td><span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                        <td style="font-size:.82rem">{{ $order->preparedBy?->name ?? '—' }}</td>
                        <td style="color:var(--muted);font-size:.78rem">{{ $order->updated_at->diffForHumans() }}</td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                <a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-outline btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                @if($order->isDraft())
                                <a href="{{ route('purchase-orders.edit', $order) }}" class="btn btn-outline btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                                <button type="button" class="btn btn-green btn-sm" title="Finalize" onclick="openModal({
                                        type: 'warn',
                                        iconClass: 'fas fa-check-double',
                                        title: 'Finalize Purchase Order?',
                                        desc: 'Finalize ' + {{ Js::from($order->po_number) }} + '? This cannot be undone.',
                                        action: '{{ route('purchase-orders.finalize', $order) }}',
                                        method: 'POST',
                                        confirmText: 'Finalize'
                                    })"><i class="fas fa-check-double"></i></button>
                                <button type="button" class="btn btn-sm" style="background:#FEF2F2;color:#DC2626" title="Delete" onclick="openModal({
                                        type: 'danger',
                                        iconClass: 'fas fa-trash',
                                        title: 'Delete Draft PO?',
                                        desc: 'Delete draft PO ' + {{ Js::from($order->po_number) }} + '?',
                                        action: '{{ route('purchase-orders.destroy', $order) }}',
                                        method: 'DELETE',
                                        confirmText: 'Delete'
                                    })"><i class="fas fa-trash"></i></button>
                                @else
                                <a href="{{ route('purchase-orders.print', $order) }}" target="_blank" class="btn btn-blue btn-sm" title="Print"><i class="fas fa-print"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="empty-msg">
                        <i class="fas fa-file-invoice" style="font-size:1.6rem;display:block;margin-bottom:.5rem;opacity:.35"></i>
                        No purchase orders found.<br>
                        <button type="button" class="btn btn-primary btn-sm" style="margin-top:.75rem" onclick="openModal({
                                type: 'warn',
                                iconClass: 'fas fa-wand-magic-sparkles',
                                title: 'Generate Purchase Order?',
                                desc: 'This will create a draft Purchase Order from all current restocking items.',
                                action: '{{ route('purchase-orders.generate') }}',
                                method: 'POST',
                                confirmText: 'Generate'
                            })">
                            <i class="fas fa-wand-magic-sparkles"></i> Generate First Purchase Order
                        </button>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
