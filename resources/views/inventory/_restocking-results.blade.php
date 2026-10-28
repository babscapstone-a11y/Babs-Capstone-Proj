    @if(request()->hasAny(['search','type']))
    <div class="results-count">{{ $items->count() }} result{{ $items->count() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Type</th>
                    <th>Current Qty</th>
                    <th>Unit</th>
                    <th>Reorder Level</th>
                    <th>Suggested Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $item)
                <tr>
                    <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                    <td><div style="font-weight:700">{{ $item->item_name }}</div></td>
                    <td><span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ $item->item_type === 'rtc' ? 'Raw Meat' : 'Beverage' }}</span></td>
                    <td>
                        @php $decimals = $item->item_type === 'rtc' ? 2 : 0; @endphp
                        <div style="font-weight:700;font-size:.95rem">{{ number_format($item->quantity, $decimals) }}</div>
                        @php $pct = $item->reorder_level > 0 ? min(100, ($item->quantity / ($item->reorder_level * 2)) * 100) : 100; @endphp
                        <div class="progress-bar"><div class="progress-fill {{ $statusFilter === 'low_stock' ? 'progress-amber' : 'progress-red' }}" style="width:{{ $pct }}%"></div></div>
                        @if($item->pack_breakdown)<div style="font-size:.72rem;color:var(--muted);margin-top:.3rem">{{ $item->pack_breakdown }}</div>@endif
                    </td>
                    <td style="color:var(--muted)">@unit($item->unit)</td>
                    <td><div style="font-size:.82rem">{{ number_format($item->reorder_level, $decimals) }} @unit($item->unit)</div></td>
                    <td><span class="reorder-qty">{{ number_format($item->suggested_restock, $decimals) }} @unit($item->unit)</span></td>
                    <td>
                        <span class="badge {{ $statusFilter === 'low_stock' ? 'badge-low' : 'badge-out' }}">
                            <i class="fas {{ $statusFilter === 'low_stock' ? 'fa-triangle-exclamation' : 'fa-circle-xmark' }}"></i>
                            {{ $item->stock_status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="row-acts">
                            <a href="{{ route('inventory.edit', $item) }}" class="act-btn act-edit" title="Edit Settings" aria-label="Edit settings"><i class="fas fa-sliders"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="empty-row">
                    <i class="fas fa-circle-check" style="font-size:1.4rem;margin-bottom:.5rem;display:block;color:var(--green-600)"></i>
                    @if(request()->hasAny(['search','type']))
                        No matching items found.
                    @else
                        {{ $statusFilter === 'low_stock' ? 'No items are low on stock!' : 'No items are out of stock!' }}
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
