@if(request()->hasAny(['search','role','status']))
<div class="results-count">{{ $users->total() }} result{{ $users->total() === 1 ? '' : 's' }} found</div>
@endif

{{-- Table --}}
<div style="overflow-x:auto">
    <table class="data-table" aria-label="Staff accounts table">
        <thead>
            <tr>
                <th>#</th>
                <th>Staff Member</th>
                <th>Role</th>
                <th>Status</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td style="color:var(--muted);font-size:.78rem;font-weight:600">#{{ $user->id }}</td>
                <td>
                    <div class="staff-cell">
                        <div class="staff-avatar">{{ $user->initials }}</div>
                        <div>
                            <div class="staff-name">{{ $user->name }}</div>
                            <div class="staff-email">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->role)
                        <span class="badge badge-{{ $user->role->role_name }}">{{ $user->role->label }}</span>
                    @else
                        <span class="badge badge-inactive">Unknown</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $user->status }}">
                        <span class="badge-dot" style="background:{{ $user->status === 'active' ? '#16A34A' : '#6B7280' }}"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td style="color:var(--muted);font-size:.82rem">{{ $user->staff->contact_no ?? '—' }}</td>
                <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $user->updated_at->format('M d, Y') }}</td>
                <td>
                    <div class="actions">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline btn-icon btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $user)
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-icon btn-sm" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button type="button" class="btn btn-outline btn-icon btn-sm" title="Reset Password"
                            onclick="openResetModal('{{ route('users.password-reset.store', $user) }}', '{{ addslashes($user->name) }}')">
                            <i class="fas fa-key"></i>
                        </button>
                        <button type="button"
                            class="btn btn-icon btn-sm {{ $user->status === 'active' ? 'btn-danger' : 'btn-success' }}"
                            title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}"
                            onclick="openToggleModal(
                                '{{ route('users.toggle-status', $user) }}',
                                '{{ addslashes($user->name) }}',
                                '{{ $user->status }}'
                            )">
                            <i class="fas fa-{{ $user->status === 'active' ? 'ban' : 'circle-check' }}"></i>
                        </button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        @if(request()->hasAny(['search','role','status']))
                            <p>No matching records found.</p>
                        @else
                            <p>No staff accounts found.</p>
                            @can('create', \App\Models\User::class)
                                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm" style="margin-top:.75rem">
                                    <i class="fas fa-plus"></i> Add First Staff
                                </a>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div class="pagination-bar">
    <div class="pagination-info">
        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} staff
    </div>
    <div class="pagination-links">
        {{ $users->onEachSide(1)->links('pagination::simple-default') }}
    </div>
</div>
@endif
