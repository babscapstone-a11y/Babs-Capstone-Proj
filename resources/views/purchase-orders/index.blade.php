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
.stat-card{background:var(--surface);border-radius:16px;padding:1.25rem 1.4rem;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem}
.stat-icon-wrap{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.si-blue{background:var(--blue-50);color:var(--blue-600)}.si-green{background:var(--green-50);color:var(--green-600)}.si-amber{background:var(--amber-50);color:var(--amber-600)}.si-red{background:var(--red-50);color:var(--red-600)}
.stat-content .stat-val{font-size:1.75rem;font-weight:900;color:var(--dark);line-height:1}
.stat-content .stat-lbl{font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem}
.po-card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
.po-card-hd{padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface-2)}
.po-card-hd h3{font-size:.88rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.po-card-hd h3 i{color:var(--primary)}
.mini-table{width:100%;border-collapse:collapse;font-size:.81rem}
.mini-table th{padding:.55rem .9rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:var(--surface-2);border-bottom:1px solid var(--border)}
.mini-table td{padding:.7rem .9rem;border-bottom:1px solid var(--line);color:var(--dark);vertical-align:middle}
.mini-table tr:last-child td{border-bottom:none}
.mini-table tr:hover td{background:var(--surface-2)}
.badge-po-draft{background:var(--amber-100);color:var(--amber-700);display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-finalized{background:var(--green-100);color:var(--green-700);display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-po-stocked{background:var(--blue-100);color:var(--blue-700);display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.filter-row{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.1rem}
.search-box{position:relative;flex:1;min-width:200px}
.search-box i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
.search-input{width:100%;padding:.55rem .9rem .55rem 2.25rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.search-input:focus{border-color:var(--primary)}
.flt-select,.flt-date{padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.btn{display:inline-flex;align-items:center;gap:.42rem;padding:.52rem 1.05rem;border-radius:10px;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff;box-shadow:0 3px 10px rgba(220,38,38,.2)}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:var(--surface);border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.btn-sm{padding:.35rem .7rem;font-size:.76rem}
.full-card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
.full-table{width:100%;border-collapse:collapse;font-size:.83rem}
.full-table th{padding:.65rem 1rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:var(--surface-2);border-bottom:1px solid var(--border)}
.full-table td{padding:.8rem 1rem;border-bottom:1px solid var(--line);color:var(--dark);vertical-align:middle}
.full-table tr:last-child td{border-bottom:none}
.full-table tr:hover td{background:var(--surface-2)}
.empty-msg{text-align:center;color:var(--muted);padding:2.5rem;font-size:.84rem}
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
            <div class="stat-content"><div class="stat-val">{{ $finalizedCount }}</div><div class="stat-lbl">Awaiting Stock-In</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap si-blue"><i class="fas fa-boxes-stacked"></i></div>
            <div class="stat-content"><div class="stat-val">{{ $stockedInCount }}</div><div class="stat-lbl">Stocked In</div></div>
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

    {{-- Full PO Table --}}
    <div class="full-card">
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);background:var(--surface-2);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
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
                    <option value="finalized" {{ request('status')==='finalized' ? 'selected':'' }}>Finalized (Awaiting Stock-In)</option>
                    <option value="stocked_in" {{ request('status')==='stocked_in' ? 'selected':'' }}>Stocked In</option>
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
                        <td><span style="font-weight:700;background:var(--surface-3);padding:.2rem .6rem;border-radius:6px">{{ $order->total_items }}</span></td>
                        <td><span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                        <td style="font-size:.82rem">{{ $order->preparedBy?->name ?? '—' }}</td>
                        <td style="color:var(--muted);font-size:.78rem">{{ $order->updated_at->diffForHumans() }}</td>
                        <td>
                            <div class="row-acts">
                                <a href="{{ route('purchase-orders.show', $order) }}" class="act-btn act-view" title="View" aria-label="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('purchase-orders.print', $order) }}" target="_blank" class="act-btn act-edit" title="Print" aria-label="Print"><i class="fas fa-print"></i></a>
                                @if($order->isDraft())
                                <button type="button" class="act-btn act-danger" title="Delete" aria-label="Delete" onclick="openModal({
                                        type: 'danger',
                                        iconClass: 'fas fa-trash',
                                        title: 'Delete Draft PO?',
                                        desc: 'Delete draft PO ' + {{ Js::from($order->po_number) }} + '?',
                                        action: '{{ route('purchase-orders.destroy', $order) }}',
                                        method: 'DELETE',
                                        confirmText: 'Delete'
                                    })"><i class="fas fa-trash"></i></button>
                                @elseif($order->awaitingStockIn())
                                <a href="{{ route('purchase-orders.show', $order) }}" class="act-btn act-success" title="Record Stock-In" aria-label="Record stock-in"><i class="fas fa-dolly"></i></a>
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

    {{-- Recent Purchase Orders (below the full list; Needs Restocking lives on the Dashboard) --}}
    <div style="margin-top:1.75rem">
        <div class="po-card">
            <div class="po-card-hd">
                <h3><i class="fas fa-clock-rotate-left"></i> Recent Purchase Orders</h3>
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
    </div>
</div>
@endsection
