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
                            @if($order->party_size)
                                <br><span style="font-size:.7rem"><i class="fas fa-users"></i> {{ $order->party_size }} pax</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:700">₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        @php
                            // Paid by QR (PayMongo confirmed) = approved automatically; pending = unpaid,
                            // or the customer uploaded a screenshot for the cashier to verify
                            $dpBadge = match(true) {
                                $order->wasAutoApproved()              => ['badge-approved', 'Paid via QR'],
                                $order->approval_status === 'approved' => ['badge-approved', 'Verified'],
                                $order->approval_status === 'rejected' => ['badge-rejected', 'Rejected'],
                                $order->approval_status === 'cancelled'=> ['badge-cancelled', 'N/A'],
                                $order->hasSubmittedPaymentProof()     => ['badge-pending', 'Proof Submitted'],
                                default                                => ['badge-cancelled', 'Unpaid'],
                            };
                        @endphp
                        <span class="badge {{ $dpBadge[0] }}">{{ $dpBadge[1] }}</span>
                    </td>
                    <td><span class="badge {{ $order->approval_status_badge_class }}">{{ $order->approval_status_label }}</span></td>
                    <td>
                        <div class="row-acts" style="justify-content:flex-end">
                            <a href="{{ route('cashier.online-orders.show', $order) }}" class="act-btn act-view" title="Review" aria-label="Review">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($order->approval_status === 'pending')
                                @if($order->hasSubmittedPaymentProof())
                                <button type="button" class="act-btn act-success" title="Approve" aria-label="Approve" onclick="confirmApprove({{ $order->id }}, '{{ $order->order_number }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @else
                                <button type="button" class="act-btn act-success" disabled style="opacity:.35;cursor:not-allowed"
                                        title="Unpaid — can be approved once the customer uploads their payment screenshot and reference number" aria-label="Approve (unavailable until payment proof is uploaded)">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                <button type="button" class="act-btn act-danger" title="Reject" aria-label="Reject" onclick="openRejectModal({{ $order->id }})">
                                    <i class="fas fa-xmark"></i>
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
