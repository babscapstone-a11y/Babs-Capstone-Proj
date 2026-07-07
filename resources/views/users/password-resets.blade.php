@extends('layouts.admin')

@section('title', 'Password Reset Requests')
@section('page-title', 'Password Reset Requests')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="breadcrumb-sep">/</span>
    <span>Reset Requests</span>
@endsection

@section('styles')
<style>
    .req-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.855rem; }
    .req-table thead th {
        padding:.85rem 1.1rem; text-align:left;
        font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
        color:var(--muted); background:#F8FAFC; border-bottom:1px solid var(--border);
    }
    .req-table tbody tr { transition:background .15s; }
    .req-table tbody tr:hover { background:rgba(220,38,38,0.02); }
    .req-table td { padding:.9rem 1.1rem; border-bottom:1px solid rgba(17,24,39,0.05); vertical-align:middle; }
    .req-table tbody tr:last-child td { border-bottom:none; }

    .staff-mini { display:flex; align-items:center; gap:.55rem; }
    .mini-avatar {
        width:34px; height:34px; border-radius:8px; flex-shrink:0;
        background:linear-gradient(135deg,var(--primary),#F97316);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:700; font-size:.72rem;
    }
    .mini-name  { font-weight:600; color:var(--dark); font-size:.855rem; }
    .mini-email { font-size:.73rem; color:var(--muted); }

    .tab-row { display:flex; gap:.5rem; margin-bottom:1.25rem; border-bottom:2px solid var(--border); }
    .tab-btn {
        padding:.58rem 1.1rem; border-radius:8px 8px 0 0;
        font-size:.84rem; font-weight:600; color:var(--muted);
        background:none; border:none; cursor:pointer; transition:all .18s;
        border-bottom:2px solid transparent; margin-bottom:-2px;
        font-family:inherit;
    }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-btn:hover  { color:var(--dark); }

    .reject-note-input {
        width:100%; border:1.5px solid rgba(17,24,39,0.1); border-radius:10px;
        padding:.55rem .85rem; font-size:.85rem; color:var(--dark);
        font-family:inherit; resize:vertical; outline:none; min-height:72px;
        transition:border-color .2s;
    }
    .reject-note-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(220,38,38,0.08); }

    .empty-state { text-align:center; padding:3.5rem 1rem; }
    .empty-state i { font-size:2.5rem; color:rgba(17,24,39,0.12); margin-bottom:.65rem; display:block; }
    .empty-state p { color:var(--muted); font-size:.88rem; margin:0; }
</style>
@endsection

@section('content')

{{-- Stats --}}
<div style="display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap">
    @foreach([['pending','Pending','D97706'],['approved','Approved','16A34A'],['rejected','Rejected','DC2626']] as [$s,$l,$c])
    @php $cnt = $requests->getCollection()->where('status',$s)->count() @endphp
    <div style="flex:1;min-width:110px;background:#fff;border-radius:12px;border:1px solid var(--border);padding:.9rem 1.1rem;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted)">{{ $l }}</div>
        <div style="font-size:1.5rem;font-weight:800;color:#{{ $c }};margin-top:.1rem">{{ $cnt }}</div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-key" style="color:var(--primary);margin-right:.4rem"></i>
            Password Reset Requests
            @if($pendingCount > 0)
                <span class="badge badge-pending" style="margin-left:.4rem">{{ $pendingCount }} pending</span>
            @endif
        </h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Staff List
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="empty-state">
            <i class="fas fa-key"></i>
            <p>No password reset requests found.</p>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="req-table" aria-label="Password reset requests">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff Member</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem;font-weight:600">#{{ $req->id }}</td>
                        <td>
                            <div class="staff-mini">
                                <div class="mini-avatar">{{ $req->user?->initials ?? '?' }}</div>
                                <div>
                                    <div class="mini-name">{{ $req->user?->name ?? 'Deleted User' }}</div>
                                    <div class="mini-email">{{ $req->user?->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.84rem;color:var(--muted)">{{ $req->requestedBy?->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $req->status }}">
                                @if($req->status === 'pending')   <i class="fas fa-clock"></i>
                                @elseif($req->status === 'approved') <i class="fas fa-check"></i>
                                @else <i class="fas fa-xmark"></i>
                                @endif
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        <td style="font-size:.82rem;color:var(--muted)">{{ $req->processedBy?->name ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--muted);white-space:nowrap">
                            {{ $req->created_at->format('M d, Y H:i') }}
                            @if($req->processed_at)
                                <div style="font-size:.72rem;margin-top:.15rem">
                                    Processed: {{ $req->processed_at->format('M d, Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($req->isPending())
                            <div style="display:flex;gap:.35rem">
                                {{-- Approve --}}
                                <form method="POST"
                                      action="{{ route('password-reset-requests.approve', $req) }}"
                                      onsubmit="return confirm('Approve this request and send the password reset email to {{ addslashes($req->user?->email ?? '') }}?')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm" title="Approve & Send Email">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <button type="button" class="btn btn-danger btn-sm" title="Reject"
                                    onclick="openRejectModal({{ $req->id }}, '{{ route('password-reset-requests.reject', $req) }}', '{{ addslashes($req->user?->name ?? '') }}')">
                                    <i class="fas fa-xmark"></i> Reject
                                </button>
                            </div>
                            @else
                                <span style="font-size:.78rem;color:var(--muted)">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($requests->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
            {{ $requests->links() }}
        </div>
        @endif
    @endif
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="rejectModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon danger"><i class="fas fa-xmark"></i></div>
        <h3 class="modal-title">Reject Request</h3>
        <p class="modal-desc">Reject the password reset request for <strong id="rejectUserName"></strong>.</p>
        <form id="rejectForm" method="POST">
            @csrf @method('PUT')
            <textarea name="note" class="reject-note-input" placeholder="Reason (optional)…" rows="3"></textarea>
            <div class="modal-actions" style="margin-top:1rem">
                <button type="button" class="btn-modal-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-modal-confirm" style="background:#DC2626">
                    <i class="fas fa-xmark"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openRejectModal(id, action, name) {
    document.getElementById('rejectUserName').textContent = name;
    document.getElementById('rejectForm').action = action;
    document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() { document.getElementById('rejectModal').classList.remove('open'); }
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>
@endsection
