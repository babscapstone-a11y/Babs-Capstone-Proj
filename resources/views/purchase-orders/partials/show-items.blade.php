{{-- Read-only item table on the PO detail page: ordered quantities next to what was actually bought --}}
@if($items->isNotEmpty())
<div class="card">
    <div class="card-hd">
        <h3><i class="fas {{ $icon }}"></i> {{ $title }}</h3>
        <span style="font-size:.78rem;color:var(--muted)">{{ $items->count() }} item(s)</span>
    </div>
    <div class="tbl-wrap">
        <table class="po-table">
            <thead>
                <tr>
                    <th>#</th><th>Item Name</th><th>Category</th>
                    <th>Stock When Ordered</th><th>Status</th><th>Qty Ordered</th>
                    <th class="rcv-col">Qty Bought</th>
                    <th class="rcv-col">Amount Paid</th>
                    <th class="rcv-col">Unit Cost</th>
                    <th class="rcv-col">Check</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items->values() as $i => $item)
                <tr>
                    <td style="color:var(--muted);font-size:.76rem">{{ $i+1 }}</td>
                    <td style="font-weight:700">{{ $item->item_name }}</td>
                    <td style="color:var(--muted)">{{ $item->category ?? '—' }}</td>
                    <td style="color:var(--muted)">{{ number_format($item->current_stock, $decimals) }} {{ $item->unit }}</td>
                    <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                    <td style="font-weight:800;color:var(--primary)">{{ number_format($item->quantity_to_purchase, $decimals) }} <span style="font-weight:400;font-size:.78rem;color:var(--muted)">{{ $item->unit }}</span></td>
                    <td class="rcv-col" style="font-weight:700">
                        {{ $item->quantity_received !== null ? number_format($item->quantity_received, $decimals) . ' ' . $item->unit : '—' }}
                    </td>
                    <td class="rcv-col">{{ $item->amount_paid !== null ? '₱' . number_format($item->amount_paid, 2) : '—' }}</td>
                    <td class="rcv-col" style="color:var(--muted)">{{ $item->unit_cost !== null ? '₱' . number_format($item->unit_cost, 2) . '/' . $item->unit : '—' }}</td>
                    <td class="rcv-col"><span class="{{ $item->receiving_badge_class }}">{{ $item->receiving_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)">Subtotal Paid</td>
                    <td class="rcv-col">₱{{ number_format($items->sum('amount_paid'), 2) }}</td>
                    <td colspan="2" class="rcv-col"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
