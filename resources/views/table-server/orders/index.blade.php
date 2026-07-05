@extends('layouts.table-server')

@section('title', 'My Orders')

@section('styles')
<style>
    .ts-orders-card {
        background: var(--white); border-radius: 16px; border: 1px solid var(--border);
        box-shadow: 0 4px 18px rgba(0,0,0,0.06); overflow: hidden;
    }
    .ts-orders-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); }
    .ts-orders-header h1 { font-size: 1.15rem; font-weight: 800; color: var(--dark); margin: 0; }
    .ts-orders-header p { font-size: .85rem; color: var(--muted); margin: .3rem 0 0; }

    table.ts-orders-table { width: 100%; border-collapse: collapse; }
    table.ts-orders-table th {
        text-align: left; padding: .8rem 1.5rem; font-size: .72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em; color: var(--muted);
        border-bottom: 1px solid var(--border); background: var(--bg);
    }
    table.ts-orders-table td { padding: .9rem 1.5rem; border-bottom: 1px solid var(--border); font-size: .87rem; }
    table.ts-orders-table tr:last-child td { border-bottom: none; }

    .status-dot { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .7rem; border-radius: 50px; font-size: .76rem; font-weight: 700; }

    .empty-state { text-align: center; padding: 3.5rem 1.5rem; color: var(--muted); }
    .empty-state i { font-size: 2.5rem; margin-bottom: .8rem; opacity: .4; }
</style>
@endsection

@section('content')

<div class="ts-orders-card">
    <div class="ts-orders-header">
        <h1><i class="fas fa-receipt" style="color:var(--primary)"></i> My Orders</h1>
        <p>Orders you've submitted to the kitchen.</p>
    </div>

    @if($orders->isEmpty())
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <div style="font-weight:700;color:var(--dark)">No orders submitted yet</div>
            <p style="font-size:.85rem;margin-top:.3rem">Orders you submit will appear here.</p>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="ts-orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td style="font-weight:700;color:var(--dark)">{{ $order->order_number }}</td>
                        <td>{{ $order->dineInOrder?->table_number ?? '—' }}</td>
                        <td>{{ $order->item_count }} {{ Str::plural('item', $order->item_count) }}</td>
                        <td style="font-weight:700;color:var(--dark)">₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="status-dot" style="background:{{ $order->status_color }}1a; color:{{ $order->status_color }}">
                                {{ $order->kitchen_status_label }}
                            </span>
                        </td>
                        <td style="color:var(--muted);font-size:.82rem">
                            {{ $order->created_at->format('M d, Y') }} · {{ $order->created_at->format('h:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:center">
            {{ $orders->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
