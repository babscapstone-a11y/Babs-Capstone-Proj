    @if(request()->hasAny(['search','status']))
    <div class="results-count">{{ $items->count() }} result{{ $items->count() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Raw Stock</th>
                    <th>Reorder Level</th>
                    <th>Stock Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $item)
                <tr>
                    <td style="color:var(--muted);font-size:.78rem">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:700;color:var(--dark)">{{ $item->item_name }}</div>
                        @if($item->supplier)
                        <div style="font-size:.72rem;color:var(--muted)">{{ $item->supplier }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</div>
                        @php
                            $pct = $item->reorder_level > 0 ? min(100, ($item->quantity / ($item->reorder_level * 2)) * 100) : 100;
                            $color = $item->stock_status === 'available' ? 'progress-green' : ($item->stock_status === 'low_stock' ? 'progress-amber' : 'progress-red');
                        @endphp
                        <div class="progress-bar"><div class="progress-fill {{ $color }}" style="width:{{ $pct }}%"></div></div>
                    </td>
                    <td>
                        <div style="font-size:.82rem">{{ number_format($item->reorder_level, 2) }} {{ $item->unit }}</div>
                        <div style="font-size:.73rem;color:var(--muted)">Min: {{ number_format($item->min_stock_level, 2) }}</div>
                    </td>
                    <td>
                        @php $s = $item->stock_status; @endphp
                        <span class="badge {{ $s === 'available' ? 'badge-available' : ($s === 'low_stock' ? 'badge-low' : 'badge-out') }}">
                            @if($s === 'low_stock')<i class="fas fa-triangle-exclamation"></i>@elseif($s === 'out_of_stock')<i class="fas fa-circle-xmark"></i>@else<i class="fas fa-circle-check"></i>@endif
                            {{ $item->stock_status_label }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                            <a href="{{ route('inventory.edit', $item) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-sliders"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-row"><i class="fas fa-box-open" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No RTC items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
