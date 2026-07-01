@extends('layouts.admin')

@section('title', $user->name . ' – Staff Profile')
@section('page-title', 'Staff Profile')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">Staff Accounts</a>
    <span class="breadcrumb-sep">/</span>
    <span>{{ $user->name }}</span>
@endsection

@section('styles')
<style>
    .profile-grid { display:grid; grid-template-columns:280px 1fr; gap:1.25rem; }
    .profile-card {
        background:#fff; border-radius:16px; border:1px solid var(--border);
        box-shadow:0 2px 16px rgba(17,24,39,0.06); overflow:hidden;
    }
    .profile-hero {
        background:linear-gradient(135deg, var(--dark) 0%, #1F2937 100%);
        padding:2rem 1.5rem; text-align:center; position:relative;
    }
    .profile-hero::before {
        content:''; position:absolute; inset:0;
        background:radial-gradient(ellipse 80% 60% at 50% 0%, rgba(220,38,38,0.25) 0%, transparent 70%);
    }
    .profile-avatar-lg {
        width:80px; height:80px; border-radius:20px;
        background:linear-gradient(135deg, var(--primary), #F97316);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:800; font-size:1.6rem;
        box-shadow:0 12px 32px rgba(220,38,38,0.35);
        margin:0 auto .75rem; position:relative; z-index:1;
    }
    .profile-name  { color:#fff; font-size:1.1rem; font-weight:700; position:relative; z-index:1; }
    .profile-email { color:rgba(255,255,255,0.5); font-size:.8rem; margin-top:.2rem; position:relative; z-index:1; }
    .profile-body  { padding:1.25rem; }
    .detail-row {
        display:flex; align-items:center; gap:.65rem;
        padding:.65rem 0; border-bottom:1px solid rgba(17,24,39,0.05);
    }
    .detail-row:last-child { border-bottom:none; }
    .detail-icon  { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:rgba(220,38,38,0.08); color:var(--primary); font-size:.8rem; flex-shrink:0; }
    .detail-label { font-size:.72rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }
    .detail-value { font-size:.86rem; font-weight:600; color:var(--dark); }

    .info-section-title { font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--muted); margin-bottom:.85rem; padding-bottom:.5rem; border-bottom:1px solid var(--border); }
    .action-grid { display:flex; flex-direction:column; gap:.55rem; }
    .action-btn-full { width:100%; display:flex; align-items:center; gap:.55rem; padding:.62rem .9rem; border-radius:10px; font-size:.84rem; font-weight:600; font-family:inherit; cursor:pointer; transition:all .18s; border:none; }
    .action-btn-full.edit     { background:rgba(37,99,235,0.08); color:#1D4ED8; }
    .action-btn-full.edit:hover{ background:#2563EB; color:#fff; }
    .action-btn-full.reset    { background:rgba(245,158,11,0.08); color:#D97706; }
    .action-btn-full.reset:hover { background:#D97706; color:#fff; }
    .action-btn-full.activate { background:rgba(22,163,74,0.08); color:#16A34A; }
    .action-btn-full.activate:hover { background:#16A34A; color:#fff; }
    .action-btn-full.deactivate { background:rgba(220,38,38,0.08); color:var(--primary); }
    .action-btn-full.deactivate:hover { background:var(--primary); color:#fff; }

    .history-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.835rem; }
    .history-table th { padding:.7rem .9rem; text-align:left; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); background:#F8FAFC; border-bottom:1px solid var(--border); }
    .history-table td { padding:.75rem .9rem; border-bottom:1px solid rgba(17,24,39,0.04); vertical-align:middle; }
    .history-table tbody tr:last-child td { border-bottom:none; }

    @media(max-width:900px) { .profile-grid { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')
<div class="profile-grid">

    {{-- Left — Profile sidebar --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Identity card --}}
        <div class="profile-card">
            <div class="profile-hero">
                <div class="profile-avatar-lg">{{ $user->initials }}</div>
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-email">{{ $user->email }}</div>
                <div style="margin-top:.7rem;position:relative;z-index:1">
                    @if($user->role)
                        <span class="badge badge-{{ $user->role->role_name }}">{{ $user->role->label }}</span>
                    @endif
                    <span class="badge badge-{{ $user->status }}" style="margin-left:.3rem">
                        <span class="badge-dot" style="background:{{ $user->status === 'active' ? '#16A34A' : '#6B7280' }}"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>
            <div class="profile-body">
                <div class="detail-row">
                    <div class="detail-icon"><i class="fas fa-id-badge"></i></div>
                    <div><div class="detail-label">Staff ID</div><div class="detail-value">#{{ $user->id }}</div></div>
                </div>
                @if($user->staff)
                <div class="detail-row">
                    <div class="detail-icon"><i class="fas fa-phone-flip"></i></div>
                    <div><div class="detail-label">Contact No.</div><div class="detail-value">{{ $user->staff->contact_no ?: '—' }}</div></div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-icon"><i class="fas fa-calendar-plus"></i></div>
                    <div><div class="detail-label">Created</div><div class="detail-value">{{ $user->created_at->format('M d, Y') }}</div></div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="fas fa-calendar-check"></i></div>
                    <div><div class="detail-label">Last Updated</div><div class="detail-value">{{ $user->updated_at->format('M d, Y') }}</div></div>
                </div>
            </div>
        </div>

        {{-- Actions card --}}
        @can('update', $user)
        <div class="profile-card" style="padding:1.25rem">
            <div class="info-section-title">Quick Actions</div>
            <div class="action-grid">
                <a href="{{ route('users.edit', $user) }}" class="action-btn-full edit">
                    <i class="fas fa-pen" style="width:16px;text-align:center"></i> Edit Staff Account
                </a>

                @if(!$pendingReset)
                    <button type="button" class="action-btn-full reset"
                        onclick="openResetModal('{{ route('users.password-reset.store', $user) }}', '{{ addslashes($user->name) }}')">
                        <i class="fas fa-key" style="width:16px;text-align:center"></i> Request Password Reset
                    </button>
                @else
                    <div style="padding:.62rem .9rem;border-radius:10px;background:rgba(245,158,11,0.08);border:1.5px solid rgba(245,158,11,0.2);font-size:.83rem;color:#D97706;display:flex;align-items:center;gap:.5rem">
                        <i class="fas fa-clock" style="width:16px;text-align:center"></i>
                        Reset request pending approval
                    </div>
                @endif

                @if($user->status === 'active')
                    <button type="button" class="action-btn-full deactivate"
                        onclick="openToggleModal('{{ route('users.toggle-status', $user) }}', '{{ addslashes($user->name) }}', 'active')">
                        <i class="fas fa-ban" style="width:16px;text-align:center"></i> Deactivate Account
                    </button>
                @else
                    <button type="button" class="action-btn-full activate"
                        onclick="openToggleModal('{{ route('users.toggle-status', $user) }}', '{{ addslashes($user->name) }}', 'inactive')">
                        <i class="fas fa-circle-check" style="width:16px;text-align:center"></i> Activate Account
                    </button>
                @endif
            </div>
        </div>
        @endcan
    </div>

    {{-- Right — Details & history --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Inactive warning --}}
        @if($user->status === 'inactive')
        <div style="padding:.9rem 1.1rem;border-radius:12px;background:rgba(220,38,38,0.06);border:1.5px solid rgba(220,38,38,0.2);display:flex;align-items:center;gap:.65rem;font-size:.86rem;color:#991B1B;font-weight:500">
            <i class="fas fa-ban"></i>
            This account is <strong>inactive</strong>. This staff member cannot log in or perform any operations.
        </div>
        @endif

        {{-- Reset history card --}}
        <div class="profile-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock-rotate-left" style="color:var(--primary);margin-right:.4rem"></i> Password Reset History</h3>
                <a href="{{ route('password-reset-requests.index') }}" class="btn btn-secondary btn-sm">View All</a>
            </div>
            @php $resets = $user->passwordResetRequests->sortByDesc('created_at')->take(5); @endphp
            @if($resets->isEmpty())
                <div style="padding:2rem;text-align:center;color:var(--muted);font-size:.85rem">
                    <i class="fas fa-key" style="font-size:1.8rem;opacity:.2;display:block;margin-bottom:.5rem"></i>
                    No password reset history.
                </div>
            @else
                <div style="overflow-x:auto">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Requested By</th>
                                <th>Status</th>
                                <th>Processed By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resets as $req)
                            <tr>
                                <td style="font-weight:600">{{ $req->requestedBy?->name ?? '—' }}</td>
                                <td><span class="badge badge-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                                <td style="color:var(--muted)">{{ $req->processedBy?->name ?? '—' }}</td>
                                <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">
                                    {{ $req->created_at->format('M d, Y H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Staff record --}}
        @if($user->staff)
        <div class="profile-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-address-card" style="color:var(--primary);margin-right:.4rem"></i> Staff Record</h3>
            </div>
            <div style="padding:1.1rem 1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                <div>
                    <div class="detail-label">First Name</div>
                    <div style="font-size:.9rem;font-weight:600;color:var(--dark);margin-top:.2rem">{{ $user->staff->first_name }}</div>
                </div>
                <div>
                    <div class="detail-label">Last Name</div>
                    <div style="font-size:.9rem;font-weight:600;color:var(--dark);margin-top:.2rem">{{ $user->staff->last_name ?: '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Email</div>
                    <div style="font-size:.86rem;color:var(--dark);margin-top:.2rem">{{ $user->staff->email }}</div>
                </div>
                <div>
                    <div class="detail-label">Contact No.</div>
                    <div style="font-size:.86rem;color:var(--dark);margin-top:.2rem">{{ $user->staff->contact_no ?: '—' }}</div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Reset-Password modal --}}
<div class="modal-overlay" id="resetModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon warn"><i class="fas fa-key"></i></div>
        <h3 class="modal-title">Request Password Reset</h3>
        <p class="modal-desc" id="resetModalDesc">Create a reset request. You will need to approve it before the email is sent.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeResetModal()">Cancel</button>
            <form id="resetForm" method="POST" style="flex:1;display:flex">
                @csrf
                <button type="submit" class="btn-modal-confirm" style="flex:1">
                    <i class="fas fa-paper-plane"></i> Create Request
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Toggle status modal --}}
<div class="modal-overlay" id="toggleModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon" id="toggleModalIcon"><i class="fas fa-ban"></i></div>
        <h3 class="modal-title" id="toggleModalTitle"></h3>
        <p class="modal-desc"  id="toggleModalDesc"></p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeToggleModal()">Cancel</button>
            <form id="toggleForm" method="POST" style="flex:1;display:flex">
                @csrf @method('PUT')
                <button type="submit" class="btn-modal-confirm" id="toggleConfirmBtn" style="flex:1">Confirm</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openResetModal(action, name) {
    document.getElementById('resetForm').action = action;
    document.getElementById('resetModal').classList.add('open');
}
function closeResetModal() { document.getElementById('resetModal').classList.remove('open'); }
document.getElementById('resetModal').addEventListener('click', function(e){ if(e.target===this) closeResetModal(); });

function openToggleModal(action, name, currentStatus) {
    var isActive = currentStatus === 'active';
    var icon     = document.getElementById('toggleModalIcon');
    var title    = document.getElementById('toggleModalTitle');
    var desc     = document.getElementById('toggleModalDesc');
    var btn      = document.getElementById('toggleConfirmBtn');
    icon.className       = 'modal-icon ' + (isActive ? 'danger' : 'warn');
    icon.innerHTML       = '<i class="fas fa-' + (isActive ? 'ban' : 'circle-check') + '"></i>';
    title.textContent    = isActive ? 'Deactivate Account?' : 'Activate Account?';
    desc.innerHTML       = isActive
        ? '<strong>' + name + '</strong> will no longer be able to log in or perform any operations.'
        : '<strong>' + name + '</strong> will regain access to the system.';
    btn.textContent      = isActive ? 'Deactivate' : 'Activate';
    btn.style.background = isActive ? '#DC2626' : '#16A34A';
    document.getElementById('toggleForm').action = action;
    document.getElementById('toggleModal').classList.add('open');
}
function closeToggleModal() { document.getElementById('toggleModal').classList.remove('open'); }
document.getElementById('toggleModal').addEventListener('click', function(e){ if(e.target===this) closeToggleModal(); });
</script>
@endsection
