@extends('layouts.admin')
@section('title', 'Restocking / Repurchase List')

@section('styles')
<style>
@media print {
    .no-print{display:none!important}
    body,.inv-page{background:#fff!important}
    .card{box-shadow:none!important;border:1px solid #ddd!important}
    .page-header{margin-bottom:1rem!important}
    .print-header{display:block!important}
}
.inv-page{padding:2rem;max-width:1100px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.print-header{display:none;text-align:center;margin-bottom:1.5rem}
.print-header h2{font-size:1.2rem;font-weight:800;margin:0}
.print-header p{font-size:.8rem;color:#666;margin:.25rem 0 0}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-low{background:#FEF3C7;color:#B45309}.badge-out{background:#FEE2E2;color:#B91C1C}
.badge-rtc{background:#EFF6FF;color:#1D4ED8}.badge-bev{background:#F5F3FF;color:#6D28D9}
.section-hd{font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--dark);padding:.7rem 1.25rem;background:#F8FAFC;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.5rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1.5rem}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.6rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr.out td{background:#FFF5F5}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.summary-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:#fff;border-radius:14px;padding:1rem 1.2rem;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07)}
.stat-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;margin-bottom:.6rem}
.stat-value{font-size:1.5rem;font-weight:800;color:var(--dark);line-height:1}
.stat-label{font-size:.72rem;font-weight:600;color:var(--muted);margin-top:.2rem;text-transform:uppercase;letter-spacing:.04em}
.stat-icon.amber{background:#FFFBEB;color:#D97706}.stat-icon.red{background:#FEF2F2;color:#DC2626}.stat-icon.blue{background:#EFF6FF;color:#2563EB}
.reorder-qty{font-weight:700;color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">

    <div class="page-header no-print">
        <div>
            <div class="page-title"><i class="fas fa-cart-shopping"></i> Restocking / Repurchase List</div>
            <div class="page-sub">Items at or below reorder threshold requiring repurchase</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
            <button class="btn btn-green" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            @if($outOfStockItems->isNotEmpty() || $lowStockItems->isNotEmpty())
            <form method="POST" action="{{ route('purchase-orders.generate') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Generate a draft Purchase Order from all {{ $outOfStockItems->count() + $lowStockItems->count() }} restocking item(s)?')">
                    <i class="fas fa-file-invoice"></i> Generate Purchase Order
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="print-header">
        <h2>Babs Resto — Restocking List</h2>
        <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="summary-grid no-print">
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-value">{{ $lowStockItems->count() + $outOfStockItems->count() }}</div><div class="stat-label">Total Items Needed</div></div>
        <div class="stat-card"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div><div class="stat-value">{{ $outOfStockItems->count() }}</div><div class="stat-label">Out of Stock</div></div>
        <div class="stat-card"><div class="stat-icon amber"><i class="fas fa-battery-half"></i></div><div class="stat-value">{{ $lowStockItems->count() }}</div><div class="stat-label">Low Stock</div></div>
    </div>

    @if($outOfStockItems->isNotEmpty())
    <div class="card">
        <div class="section-hd" style="background:#FFF5F5;border-bottom-color:#FECACA;color:#B91C1C"><i class="fas fa-circle-xmark"></i> Out of Stock — Urgent Repurchase Needed</div>
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Current Qty</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Suggested Order</th>
                        <th>Supplier</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outOfStockItems as $i => $item)
                    <tr class="out">
                        <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                        <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                        <td><span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($item->item_type) }}</span></td>
                        <td style="color:var(--muted)">{{ $item->category ?? '—' }}</td>
                        <td><span class="badge badge-out">0 / {{ number_format($item->quantity,0) }}</span></td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->reorder_level,0) }}</td>
                        <td><span class="reorder-qty">{{ number_format($item->suggested_restock, 0) }} {{ $item->unit }}</span></td>
                        <td>{{ $item->supplier ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($lowStockItems->isNotEmpty())
    <div class="card">
        <div class="section-hd" style="background:#FFFBEB;border-bottom-color:#FDE68A;color:#B45309"><i class="fas fa-triangle-exclamation"></i> Low Stock — Replenish Soon</div>
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Current Qty</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Suggested Order</th>
                        <th>Supplier</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $i => $item)
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                        <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                        <td><span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($item->item_type) }}</span></td>
                        <td style="color:var(--muted)">{{ $item->category ?? '—' }}</td>
                        <td>
                            <span class="badge badge-low">{{ number_format($item->quantity,0) }}</span>
                        </td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format($item->reorder_level,0) }}</td>
                        <td><span class="reorder-qty">{{ number_format($item->suggested_restock, 0) }} {{ $item->unit }}</span></td>
                        <td>{{ $item->supplier ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($outOfStockItems->isEmpty() && $lowStockItems->isEmpty())
    <div class="card" style="padding:3rem;text-align:center">
        <i class="fas fa-circle-check" style="font-size:2.5rem;color:#16A34A;margin-bottom:1rem;display:block"></i>
        <div style="font-size:1.1rem;font-weight:700;color:var(--dark)">All items are well-stocked!</div>
        <div style="font-size:.85rem;color:var(--muted);margin-top:.35rem">No items are currently below the reorder threshold.</div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline" style="margin-top:1.25rem"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
    </div>
    @endif

    @if($outOfStockItems->isNotEmpty() || $lowStockItems->isNotEmpty())
    <div class="no-print" style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap">
        <form method="POST" action="{{ route('purchase-orders.generate') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate a draft Purchase Order from all restocking items?')">
                <i class="fas fa-file-invoice"></i> Generate Purchase Order
            </button>
        </form>
        <a href="{{ route('inventory.stock-in.index') }}" class="btn btn-outline"><i class="fas fa-arrow-down-to-bracket"></i> Record Stock-In</a>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-list"></i> View Purchase Orders</a>
    </div>
    @endif
</div>
@endsection
