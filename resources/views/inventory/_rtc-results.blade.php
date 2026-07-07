    @if(request()->hasAny(['search','status']))
    <div class="results-count">{{ $items->count() }} result{{ $items->count() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Raw Stock</th>
                    <th>RTC Servings</th>
                    <th>Portion Rule</th>
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
                    <td><span style="font-size:.8rem;padding:.2rem .55rem;border-radius:6px;background:#F3F4F6;font-weight:600;">{{ $item->category ?? '—' }}</span></td>
                    <td>
                        <div style="font-weight:700">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</div>
                        @php
                            $pct = $item->reorder_level > 0 ? min(100, ($item->quantity / ($item->reorder_level * 2)) * 100) : 100;
                            $color = $item->stock_status === 'available' ? 'progress-green' : ($item->stock_status === 'low_stock' ? 'progress-amber' : 'progress-red');
                        @endphp
                        <div class="progress-bar"><div class="progress-fill {{ $color }}" style="width:{{ $pct }}%"></div></div>
                    </td>
                    <td>
                        @if($item->rtc_servings > 0)
                        <span style="font-weight:700;color:#1D4ED8">{{ number_format($item->rtc_servings, 0) }}</span>
                        <span style="font-size:.75rem;color:var(--muted)"> servings</span>
                        @else
                        <span style="color:var(--muted);font-size:.8rem">—</span>
                        @endif
                    </td>
                    <td>
                        @if($item->portion_size)
                        <span style="font-size:.8rem">{{ number_format($item->portion_size, 3) }} {{ $item->portion_unit ?? $item->unit }}/serving</span>
                        @else
                        <span style="color:var(--muted);font-size:.8rem">Not set</span>
                        @endif
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
                            <button class="btn btn-outline btn-sm" onclick="openStockInFor({{ $item->id }}, '{{ addslashes($item->item_name) }}', '{{ $item->unit }}')">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" style="color:#2563EB;border-color:#BFDBFE" onclick="openConvertFor({{ $item->id }}, '{{ addslashes($item->item_name) }}', '{{ $item->unit }}', {{ $item->quantity }}, {{ $item->portion_size ?? 0.25 }}, '{{ $item->portion_unit ?? $item->unit }}')">
                                <i class="fas fa-arrows-rotate"></i>
                            </button>
                            <a href="{{ route('inventory.edit', $item) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-sliders"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="empty-row"><i class="fas fa-box-open" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No RTC items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
