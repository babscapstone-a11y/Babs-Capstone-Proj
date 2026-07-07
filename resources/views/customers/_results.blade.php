    <div class="table-header">
        <h2>
            <i class="fas fa-users" style="color:var(--primary);margin-right:.4rem"></i>
            Customer Accounts
            @if($customers->total())
                <span style="font-size:.75rem;font-weight:500;color:var(--muted)">({{ $customers->total() }})</span>
            @endif
        </h2>
    </div>

    @if(request()->hasAny(['search','status']))
    <div class="results-count">{{ $customers->total() }} result{{ $customers->total() === 1 ? '' : 's' }} found</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>Customer</th>
                    <th>Contact Number</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => request('dir', 'desc') === 'desc' ? 'asc' : 'desc']) }}" class="sort-link">
                            Registered
                            @if(request('sort', 'created_at') === 'created_at')
                                <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}" style="color:var(--primary)"></i>
                            @endif
                        </a>
                    </th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td style="font-size:.75rem;color:var(--muted);font-weight:600">
                        #{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div class="avatar-cell">
                            <div class="customer-avatar">{{ $customer->initials }}</div>
                            <div>
                                <div class="cust-name">{{ $customer->full_name }}</div>
                                <div class="cust-email">{{ $customer->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.82rem;color:var(--muted)">
                        {{ $customer->contact_no ?: '—' }}
                    </td>
                    <td style="white-space:nowrap;font-size:.78rem;color:var(--muted)">
                        {{ $customer->created_at->format('M d, Y') }}<br>
                        <span style="font-size:.7rem">{{ $customer->created_at->format('h:i A') }}</span>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted)">
                        <span style="font-style:italic">—</span>
                    </td>
                    <td>
                        @if($customer->status === 'active')
                            <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Active</span>
                        @else
                            <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.4rem"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-group">
                            @can('view', $customer)
                            <a href="{{ route('customers.show', $customer) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @endcan
                            @can('toggleStatus', $customer)
                            <button type="button"
                                class="btn-action {{ $customer->status === 'active' ? 'btn-deact' : 'btn-activ' }}"
                                onclick="openToggleModal({{ $customer->id }}, '{{ addslashes($customer->full_name) }}', {{ $customer->status === 'active' ? 'true' : 'false' }})">
                                <i class="fas fa-{{ $customer->status === 'active' ? 'ban' : 'check' }}"></i>
                                {{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-users" style="color:var(--muted);opacity:.4"></i>
                            <h3>No Customers Found</h3>
                            <p style="margin:0">
                                {{ request()->hasAny(['search','status']) ? 'No matching records found.' : 'No customers have registered yet. Customers appear here automatically after they register.' }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--muted)">
            Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers
        </span>
        {{ $customers->links() }}
    </div>
    @endif
