    <div class="table-header">
        <h2>
            <i class="fas fa-ban" style="color:var(--primary);margin-right:.4rem"></i>
            Cancellation Requests
            @if($cancellationRequests->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $cancellationRequests->total() }})</span>
            @endif
        </h2>
    </div>

    @if(request()->hasAny(['search','status']))
    <div class="results-count">{{ $cancellationRequests->total() }} result{{ $cancellationRequests->total() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Order Type</th>
                    <th>Order Status</th>
                    <th>Request Date</th>
                    <th>Reason</th>
                    <th>Review Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cancellationRequests as $cr)
                @php $order = $cr->order; @endphp
                <tr>
                    <td style="font-weight:600;font-size:.78rem">{{ $cr->request_number ?? '#'.$cr->id }}</td>
                    <td style="font-size:.82rem">{{ $order?->order_number ?? '—' }}</td>
                    <td>
                        <div class="cust-name">{{ $cr->customer?->full_name ?? 'Unknown' }}</div>
                    </td>
                    <td style="font-size:.8rem;color:var(--muted)">{{ $order?->order_type_label ?? '—' }}</td>
                    <td>
                        @if($order)
                        <span class="badge" style="background:{{ $order->status_color }}1a;color:{{ $order->status_color }}">
                            {{ $order->status_name }}
                        </span>
                        @else
                        <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        {{ $cr->created_at->format('M d, Y') }}<br>
                        <span style="font-size:.7rem">{{ $cr->created_at->format('h:i A') }}</span>
                    </td>
                    <td class="reason-cell" title="{{ $cr->cancellation_reason }}">{{ \Illuminate\Support\Str::limit($cr->cancellation_reason, 60) }}</td>
                    <td>
                        <span class="badge {{ $cr->review_status_badge_class }}">
                            @if($cr->isPending()) <i class="fas fa-clock"></i>
                            @elseif($cr->isApproved()) <i class="fas fa-check"></i>
                            @else <i class="fas fa-xmark"></i>
                            @endif
                            {{ $cr->review_status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('cancellations.show', $cr) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> Review
                            </a>
                            @if($cr->isPending())
                                <button type="button" class="btn-action btn-appr"
                                    onclick="openModal({
                                        type: 'warn',
                                        iconClass: 'fas fa-circle-check',
                                        title: 'Approve Cancellation?',
                                        desc: 'Are you sure you want to approve this cancellation request?',
                                        action: '{{ route('cancellations.approve', $cr) }}',
                                        method: 'PUT',
                                        confirmText: 'Approve',
                                    })">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn-action btn-rej"
                                    onclick="openRejectModal({{ $cr->id }}, '{{ route('cancellations.reject', $cr) }}', '{{ addslashes($cr->request_number ?? '#'.$cr->id) }}')">
                                    <i class="fas fa-xmark"></i> Reject
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-ban"></i>
                            <h3>No Cancellation Requests Found</h3>
                            <p style="margin:0">
                                {{ request()->hasAny(['search','status']) ? 'No matching records found.' : 'Customer cancellation requests will appear here for review.' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cancellationRequests->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $cancellationRequests->firstItem() }}–{{ $cancellationRequests->lastItem() }} of {{ $cancellationRequests->total() }} requests
        </span>
        {{ $cancellationRequests->links() }}
    </div>
    @endif
