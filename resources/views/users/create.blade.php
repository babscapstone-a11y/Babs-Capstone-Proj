@extends('layouts.admin')

@section('title', 'Add Staff Account')
@section('page-title', 'Add Staff Account')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">Staff Accounts</a>
    <span class="breadcrumb-sep">/</span>
    <span>Add Staff</span>
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
    .role-option:checked + .role-label {
        border-color:var(--primary);
        background:rgba(220,38,38,0.05);
    }
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
    @media(max-width:640px) { .form-grid { grid-template-columns:1fr; } .form-grid .span-2 { grid-column:auto; } }
</style>
@endsection

@section('content')
<div style="max-width:760px">

    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-user-plus" style="color:var(--primary);margin-right:.4rem"></i> New Staff Account</h2>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('users.store') }}" id="createForm" novalidate>
                @csrf

                {{-- Account information --}}
                <div class="section-label">Account Information</div>
                <div class="form-grid">

                    <div class="form-group span-2">
                        <label class="form-label" for="name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> Full Name
                        </label>
                        <div class="input-wrap @error('name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="name" name="name" type="text" class="form-input"
                                   value="{{ old('name') }}" required autofocus
                                   placeholder="e.g. Juan dela Cruz"
                                   aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                        </div>
                        @error('name')
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
                                   value="{{ old('email') }}" required
                                   placeholder="staff@babsresto.com"
                                   aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                        </div>
                        @error('email')
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
                                   value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock" style="color:var(--primary);font-size:.78rem"></i> Password
                        </label>
                        <div class="input-wrap @error('password') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input id="password" name="password" type="password" class="form-input"
                                   required placeholder="Min. 8 characters" autocomplete="new-password"
                                   aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                            <button type="button" class="toggle-pwd" id="togglePwd" aria-label="Show password">
                                <i class="fas fa-eye" id="togglePwdIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">
                            <i class="fas fa-shield-halved" style="color:var(--primary);font-size:.78rem"></i> Confirm Password
                        </label>
                        <div class="input-wrap" id="confirmWrap">
                            <span class="input-icon"><i class="fas fa-shield-halved"></i></span>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   class="form-input" required placeholder="Re-enter password" autocomplete="new-password">
                            <button type="button" class="toggle-pwd" id="toggleConfirm" aria-label="Show confirm password">
                                <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                            </button>
                        </div>
                        <div class="field-error" id="matchError" style="display:none">
                            <i class="fas fa-circle-exclamation"></i> Passwords do not match
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
                    @endphp
                    <div>
                        <input type="radio" name="role_id" id="role_{{ $r->id }}" value="{{ $r->id }}"
                               class="role-option" {{ old('role_id') == $r->id ? 'checked' : '' }} required>
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
                    <input type="radio" name="status" id="status_active" value="active"
                           class="status-option" {{ old('status','active') === 'active' ? 'checked' : '' }}>
                    <label for="status_active" class="status-label">
                        <i class="fas fa-circle-check"></i> Active
                    </label>

                    <input type="radio" name="status" id="status_inactive" value="inactive"
                           class="status-option" {{ old('status') === 'inactive' ? 'checked' : '' }}>
                    <label for="status_inactive" class="status-label">
                        <i class="fas fa-ban"></i> Inactive
                    </label>
                </div>
                @error('status')
                    <div class="field-error" style="margin-top:.4rem"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror

                {{-- Actions --}}
                <div style="display:flex;gap:.75rem;margin-top:2rem;padding-top:1.25rem;border-top:1px solid var(--border)">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary" style="min-width:120px;justify-content:center">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="flex:1;justify-content:center">
                        <span class="btn-spinner" id="btnSpinner" style="width:16px;height:16px;border-radius:50%;border:2px solid rgba(255,255,255,0.35);border-top-color:#fff;animation:spin .7s linear infinite;display:none"></span>
                        <span id="btnText"><i class="fas fa-user-plus"></i> Create Staff Account</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Password show/hide
var pwd    = document.getElementById('password');
var pwdBtn = document.getElementById('togglePwd');
var pwdIcon= document.getElementById('togglePwdIcon');
pwdBtn.addEventListener('click', function() {
    var show = pwd.type === 'text';
    pwd.type = show ? 'password' : 'text';
    pwdIcon.className = show ? 'fas fa-eye' : 'fas fa-eye-slash';
    pwdBtn.setAttribute('aria-label', show ? 'Show password' : 'Hide password');
});

var conf     = document.getElementById('password_confirmation');
var confBtn  = document.getElementById('toggleConfirm');
var confIcon = document.getElementById('toggleConfirmIcon');
var confWrap = document.getElementById('confirmWrap');
var matchErr = document.getElementById('matchError');
confBtn.addEventListener('click', function() {
    var show = conf.type === 'text';
    conf.type = show ? 'password' : 'text';
    confIcon.className = show ? 'fas fa-eye' : 'fas fa-eye-slash';
});
conf.addEventListener('input', function() {
    if (!this.value) { matchErr.style.display='none'; confWrap.classList.remove('has-error'); return; }
    var match = this.value === pwd.value;
    matchErr.style.display = match ? 'none' : 'flex';
    confWrap.classList.toggle('has-error', !match);
});

// Loading state
document.getElementById('createForm').addEventListener('submit', function() {
    if (!this.checkValidity()) return;
    if (pwd.value !== conf.value) return;
    document.getElementById('btnSpinner').style.display = 'block';
    document.getElementById('btnText').textContent = ' Creating…';
    document.getElementById('submitBtn').disabled = true;
});
</script>
@endsection
