@extends('layouts.admin')

@section('title', 'Request Password Reset – ' . $user->name)
@section('page-title', 'Request Password Reset')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
    <span class="breadcrumb-sep">/</span>
    <span>Reset Password</span>
@endsection

@section('content')
<div style="max-width:520px">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-key" style="color:var(--primary);margin-right:.4rem"></i> Reset Password Request</h2>
            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">

            {{-- Staff card --}}
            <div style="display:flex;align-items:center;gap:.85rem;padding:1rem;background:#F8FAFC;border-radius:12px;border:1px solid var(--border);margin-bottom:1.5rem">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--primary),#F97316);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;flex-shrink:0">
                    {{ $user->initials }}
                </div>
                <div>
                    <div style="font-weight:700;color:var(--dark)">{{ $user->name }}</div>
                    <div style="font-size:.82rem;color:var(--muted)">{{ $user->email }}</div>
                    @if($user->role)
                        <span class="badge badge-{{ $user->role->role_name }}" style="margin-top:.3rem">{{ $user->role->label }}</span>
                    @endif
                </div>
            </div>

            @if($hasPending)
            <div style="padding:.9rem 1.1rem;border-radius:12px;background:rgba(245,158,11,0.08);border:1.5px solid rgba(245,158,11,0.25);color:#92400E;font-size:.86rem;margin-bottom:1.25rem;display:flex;gap:.6rem;align-items:flex-start">
                <i class="fas fa-triangle-exclamation" style="margin-top:.12rem;flex-shrink:0"></i>
                <div>
                    <strong>A reset request is already pending</strong> for this staff member.
                    Go to <a href="{{ route('password-reset-requests.index') }}" style="color:var(--primary)">Reset Requests</a> to approve or reject it.
                </div>
            </div>
            @else
            <div style="padding:.9rem 1.1rem;border-radius:12px;background:#EFF6FF;border:1.5px solid #BFDBFE;color:#1E40AF;font-size:.84rem;margin-bottom:1.5rem;display:flex;gap:.6rem;align-items:flex-start">
                <i class="fas fa-circle-info" style="margin-top:.12rem;flex-shrink:0"></i>
                <div>
                    This will create a <strong>pending reset request</strong>. You will then need to approve it from the
                    <a href="{{ route('password-reset-requests.index') }}" style="color:var(--primary)">Reset Requests</a> page
                    to trigger the email.
                </div>
            </div>

            <form method="POST" action="{{ route('users.password-reset.store', $user) }}">
                @csrf
                <div style="display:flex;gap:.75rem">
                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary" style="min-width:110px;justify-content:center">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">
                        <i class="fas fa-paper-plane"></i> Create Reset Request
                    </button>
                </div>
            </form>
            @endif

            @if($hasPending)
            <div style="margin-top:1rem">
                <a href="{{ route('password-reset-requests.index') }}" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-key"></i> View Pending Requests
                </a>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
