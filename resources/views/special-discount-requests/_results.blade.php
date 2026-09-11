    <div class="table-header">
        <h2>
            <i class="fas fa-hand-holding-dollar" style="color:var(--primary);margin-right:.4rem"></i>
            Special Discount Requests
            @if($specialDiscountRequests->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $specialDiscountRequests->total() }})</span>
            @endif
        </h2>
    </div>

    @if(request()->hasAny(['search','status']))
    <div class="results-count">{{ $specialDiscountRequests->total() }} result{{ $specialDiscountRequests->total() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Order #</th>
                    <th>Discount</th>
                    <th>Requested By</th>
                    <th>Amount</th>
                    <th>Request Date</th>
                    <th>Reason</th>
                    <th>Review Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($specialDiscountRequests as $sdr)
                <tr>
                    <td style="font-weight:600;font-size:.78rem">{{ $sdr->request_number ?? '#'.$sdr->id }}</td>
                    <td style="font-size:.82rem">{{ $sdr->order?->order_number ?? '—' }}</td>
                    <td style="font-size:.8rem;color:var(--muted)">{{ $sdr->discount?->discount_name ?? '—' }}</td>
                    <td>
                        <div class="cust-name">{{ $sdr->cashier?->name ?: ($sdr->cashier?->username ?? 'Unknown') }}</div>
                    </td>
                    <td class="amount-cell">₱{{ number_format($sdr->requested_amount, 2) }}</td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        {{ $sdr->created_at->format('M d, Y') }}<br>
                        <span style="font-size:.7rem">{{ $sdr->created_at->format('h:i A') }}</span>
                    </td>
                    <td class="reason-cell" title="{{ $sdr->reason }}">{{ $sdr->reason ? \Illuminate\Support\Str::limit($sdr->reason, 60) : '—' }}</td>
                    <td>
                        <span class="badge {{ $sdr->review_status_badge_class }}">
                            @if($sdr->isPending()) <i class="fas fa-clock"></i>
                            @elseif($sdr->isApproved()) <i class="fas fa-check"></i>
                            @else <i class="fas fa-xmark"></i>
                            @endif
                            {{ $sdr->review_status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('special-discount-requests.show', $sdr) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> Review
                            </a>
                            @if($sdr->isPending())
                                <button type="button" class="btn-action btn-appr"
                                    onclick="openModal({
                                        type: 'warn',
                                        iconClass: 'fas fa-circle-check',
                                        title: 'Approve Special Discount?',
                                        desc: 'Are you sure you want to approve this ₱{{ number_format($sdr->requested_amount, 2) }} special discount request?',
                                        action: '{{ route('special-discount-requests.approve', $sdr) }}',
                                        method: 'PUT',
                                        confirmText: 'Approve',
                                    })">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn-action btn-rej"
                                    onclick="openRejectModal({{ $sdr->id }}, '{{ route('special-discount-requests.reject', $sdr) }}', '{{ addslashes($sdr->request_number ?? '#'.$sdr->id) }}')">
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
                            <i class="fas fa-hand-holding-dollar"></i>
                            <h3>No Special Discount Requests Found</h3>
                            <p style="margin:0">
                                {{ request()->hasAny(['search','status']) ? 'No matching records found.' : 'Cashier special-discount amount requests will appear here for review.' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($specialDiscountRequests->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $specialDiscountRequests->firstItem() }}–{{ $specialDiscountRequests->lastItem() }} of {{ $specialDiscountRequests->total() }} requests
        </span>
        {{ $specialDiscountRequests->links() }}
    </div>
    @endif
