@extends('layouts.admin')

@section('title', 'Edit Staff – ' . $user->name)
@section('page-title', 'Edit Staff Account')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
    <span class="breadcrumb-sep">/</span>
    <span>Edit</span>
@endsection

@section('styles')
<style>
    .form-grid   { display:grid; grid-template-columns:1fr 1fr; gap:1.1rem; }
    .form-grid .span-2 { grid-column:1/-1; }
    .section-label {
        font-size:.72rem; font-weight:700; letter-spacing:.07em;
        text-transform:uppercase; color:var(--muted);
        padding-bottom:.6rem; border-bottom:1px solid var(--border);
        margin-bottom:1rem;
    }
    .role-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:.65rem; }
    .role-option { display:none; }
    .role-label {
        display:flex; flex-direction:column; align-items:center;
        gap:.4rem; padding:.9rem .75rem; border-radius:12px;
        border:2px solid var(--border); cursor:pointer;
        transition:all .2s; text-align:center; background:#fff;
    }
    .role-label .role-icon { font-size:1.3rem; }
    .role-label .role-name { font-size:.8rem; font-weight:600; color:var(--muted); }
    .role-option:checked + .role-label { border-color:var(--primary); background:rgba(220,38,38,0.05); }
    .role-option:checked + .role-label .role-name { color:var(--primary); }
    .status-row { display:flex; gap:.65rem; }
    .status-option { display:none; }
    .status-label {
        flex:1; display:flex; align-items:center; justify-content:center; gap:.5rem;
        padding:.65rem; border-radius:10px; border:2px solid var(--border);
        cursor:pointer; font-size:.84rem; font-weight:600; color:var(--muted);
        transition:all .2s; background:#fff;
    }
    .status-option[value="active"]:checked + .status-label   { border-color:#16A34A; background:rgba(22,163,74,0.06); color:#16A34A; }
    .status-option[value="inactive"]:checked + .status-label { border-color:#6B7280; background:rgba(107,114,128,0.06); color:#6B7280; }

    .pwd-notice {
        display:flex; align-items:center; gap:.6rem;
        padding:.75rem 1rem; border-radius:10px;
        background:rgba(37,99,235,0.06); border:1.5px solid rgba(37,99,235,0.18);
        color:#1D4ED8; font-size:.83rem; margin-bottom:1rem;
    }
    .self-notice {
        display:flex; align-items:center; gap:.6rem;
        padding:.75rem 1rem; border-radius:10px;
        background:rgba(245,158,11,0.08); border:1.5px solid rgba(245,158,11,0.28);
        color:#92400E; font-size:.83rem; margin-bottom:1rem;
    }
    @media(max-width:640px) { .form-grid { grid-template-columns:1fr; } .form-grid .span-2 { grid-column:auto; } }
</style>
@endsection

@section('content')
<div style="max-width:760px">
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--primary),#F97316);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem">
                    {{ $user->initials }}
                </div>
                <div>
                    <h2 class="card-title" style="margin:0">{{ $user->name }}</h2>
                    <div style="font-size:.78rem;color:var(--muted)">{{ $user->email }}</div>
                </div>
            </div>
            <div style="display:flex;gap:.5rem">
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">

            @if(auth()->id() === $user->id)
            <div class="self-notice">
                <i class="fas fa-triangle-exclamation"></i>
                You are editing your own account. Role and status changes are restricted for security.
            </div>
            @endif

            <div class="pwd-notice">
                <i class="fas fa-circle-info"></i>
                Passwords cannot be changed here. Use the <strong>Reset Password</strong> action on the staff profile.
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}" id="editForm">
                @csrf @method('PUT')

                {{-- Account Information --}}
                <div class="section-label">Account Information</div>
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="first_name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> First Name
                        </label>
                        <div class="input-wrap @error('first_name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="first_name" name="first_name" type="text" class="form-input"
                                   value="{{ old('first_name', $user->staff->first_name ?? '') }}" required
                                   placeholder="Juan">
                        </div>
                        @error('first_name')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> Last Name
                        </label>
                        <div class="input-wrap @error('last_name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="last_name" name="last_name" type="text" class="form-input"
                                   value="{{ old('last_name', $user->staff->last_name ?? '') }}" required
                                   placeholder="dela Cruz">
                        </div>
                        @error('last_name')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope" style="color:var(--primary);font-size:.78rem"></i> Email Address
                        </label>
                        <div class="input-wrap @error('email') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-envelope"></i></span>
                            <input id="email" name="email" type="email" class="form-input"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>
                        @error('email')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">
                            <i class="fas fa-at" style="color:var(--primary);font-size:.78rem"></i> Username
                        </label>
                        <div class="input-wrap @error('username') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-at"></i></span>
                            <input id="username" name="username" type="text" class="form-input"
                                   value="{{ old('username', $user->username) }}" required
                                   placeholder="juan.delacruz">
                        </div>
                        @error('username')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone" style="color:var(--primary);font-size:.78rem"></i> Phone
                            <span class="form-label-opt">Optional</span>
                        </label>
                        <div class="input-wrap">
                            <span class="input-icon"><i class="fas fa-mobile-screen"></i></span>
                            <input id="phone" name="phone" type="tel" class="form-input"
                                   value="{{ old('phone', $user->staff->contact_no ?? '') }}" placeholder="09XXXXXXXXX"
                                   inputmode="numeric" maxlength="11" pattern="[0-9]{11}"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                        </div>
                    </div>

                </div>

                {{-- Role --}}
                <div class="section-label" style="margin-top:1.5rem">Assigned Role</div>
                <div class="role-grid">
                    @foreach($roles as $r)
                    @php
                        $icons = ['admin'=>'fa-user-shield','cashier'=>'fa-cash-register','kitchen_staff'=>'fa-utensils','table_server'=>'fa-concierge-bell'];
                        $icon  = $icons[$r->role_name] ?? 'fa-user';
                        $checked = old('role_id', $user->role_id) == $r->id;
                    @endphp
                    <div>
                        <input type="radio" name="role_id" id="role_{{ $r->id }}" value="{{ $r->id }}"
                               class="role-option" {{ $checked ? 'checked' : '' }}>
                        <label for="role_{{ $r->id }}" class="role-label">
                            <span class="role-icon"><i class="fas {{ $icon }}"></i></span>
                            <span class="role-name">{{ $r->label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('role_id')
                    <div class="field-error" style="margin-top:.4rem"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror

                {{-- Status --}}
                <div class="section-label" style="margin-top:1.5rem">Account Status</div>
                <div class="status-row">
                    @php $currentStatus = old('status', $user->status); @endphp
                    <input type="radio" name="status" id="status_active" value="active"
                           class="status-option" {{ $currentStatus === 'active' ? 'checked' : '' }}>
                    <label for="status_active" class="status-label">
                        <i class="fas fa-circle-check"></i> Active
                    </label>

                    <input type="radio" name="status" id="status_inactive" value="inactive"
                           class="status-option" {{ $currentStatus === 'inactive' ? 'checked' : '' }}>
                    <label for="status_inactive" class="status-label">
                        <i class="fas fa-ban"></i> Inactive
                    </label>
                </div>
                @error('status')
                    <div class="field-error" style="margin-top:.4rem"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror

                {{-- Actions --}}
                <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid var(--border)">
                    <a href="{{ route('users.show', $user) }}" class="btn btn-secondary" style="min-width:120px;justify-content:center">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1;justify-content:center">
                        <i class="fas fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
