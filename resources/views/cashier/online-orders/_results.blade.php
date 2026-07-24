    <div class="table-header">
        <h2>
            <i class="fas fa-mobile-screen-button" style="color:var(--primary);margin-right:.4rem"></i>
            Online Orders
            @if($orders->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $orders->total() }})</span>
            @endif
        </h2>
    </div>

    @if(request()->hasAny(['q']))
    <div class="results-count">{{ $orders->total() }} result{{ $orders->total() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Contact Number</th>
                    <th>Order Placed</th>
                    <th>Pick-up Schedule</th>
                    <th style="text-align:right">Total Amount</th>
                    <th>Down-Payment</th>
                    <th>Order Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td style="font-weight:700">#{{ $order->order_number }}</td>
                    <td>
                        <div style="font-weight:600">{{ $order->customer_name }}</div>
                        <div style="font-size:.74rem;color:var(--muted)">{{ $order->customer?->email }}</div>
                    </td>
                    <td style="font-size:.82rem;color:var(--muted)">{{ $order->customer?->contact_no ?: '—' }}</td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        {{ $order->created_at->format('M d, Y') }}<br>
                        <span style="font-size:.7rem">{{ $order->created_at->format('h:i A') }}</span>
                    </td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        @if($order->pickup_at)
                            {{ $order->pickup_at->format('M d, Y') }}<br>
                            <span style="font-size:.7rem">{{ $order->pickup_at->format('h:i A') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:700">₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        @php
                            $dpBadge = match($order->approval_status) {
                                'approved' => ['badge-approved', 'Verified'],
                                'rejected' => ['badge-rejected', 'Rejected'],
                                'cancelled'=> ['badge-cancelled', 'N/A'],
                                default    => ['badge-pending', 'Awaiting Verification'],
                            };
                        @endphp
                        <span class="badge {{ $dpBadge[0] }}">{{ $dpBadge[1] }}</span>
                    </td>
                    <td><span class="badge {{ $order->approval_status_badge_class }}">{{ $order->approval_status_label }}</span></td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('cashier.online-orders.show', $order) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> Review
                            </a>
                            @if($order->approval_status === 'pending')
                                <button type="button" class="btn-action btn-approve" onclick="confirmApprove({{ $order->id }}, '{{ $order->order_number }}')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal({{ $order->id }})">
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
                            <i class="fas fa-inbox"></i>
                            <h3>No Online Orders Found</h3>
                            <p style="margin:0">
                                {{ request()->hasAny(['q']) ? 'No matching records found.' : 'There are no online orders in this status yet.' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </span>
        {{ $orders->links() }}
    </div>
    @endif
