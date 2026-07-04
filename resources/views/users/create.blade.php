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

                    <div class="form-group">
                        <label class="form-label" for="first_name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> First Name
                        </label>
                        <div class="input-wrap @error('first_name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="first_name" name="first_name" type="text" class="form-input"
                                   value="{{ old('first_name') }}" required autofocus
                                   placeholder="Juan"
                                   aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}">
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
                                   value="{{ old('last_name') }}" required
                                   placeholder="dela Cruz"
                                   aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}">
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
                                   value="{{ old('email') }}" required
                                   placeholder="staff@babsresto.com"
                                   aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
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
                                   value="{{ old('username') }}" required
                                   placeholder="juan.delacruz"
                                   aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}">
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
                                   value="{{ old('phone') }}" placeholder="09XXXXXXXXX"
                                   inputmode="numeric" maxlength="11" pattern="[0-9]{11}"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
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

    {{-- Confirm-create modal --}}
    <div class="modal-overlay" id="createConfirmModal" role="dialog" aria-modal="true" aria-labelledby="createConfirmTitle">
        <div class="modal-box">
            <div class="modal-icon warn"><i class="fas fa-user-plus"></i></div>
            <h3 class="modal-title" id="createConfirmTitle">Confirm New Staff Account</h3>
            <p class="modal-desc">Please review the details below before creating this account.</p>
            <div style="background:var(--bg,#F8FAFC);border:1px solid var(--border);border-radius:12px;padding:.9rem 1rem;margin-bottom:1.5rem;font-size:.83rem;display:flex;flex-direction:column;gap:.55rem">
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Name</span><strong id="confirmName" style="text-align:right"></strong></div>
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Email</span><strong id="confirmEmail" style="text-align:right"></strong></div>
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Username</span><strong id="confirmUsername" style="text-align:right"></strong></div>
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Phone</span><strong id="confirmPhone" style="text-align:right"></strong></div>
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Role</span><strong id="confirmRole" style="text-align:right"></strong></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeCreateConfirmModal()">Cancel</button>
                <button type="button" class="btn-modal-confirm" id="confirmCreateBtn" style="flex:1">
                    <i class="fas fa-check"></i> Confirm &amp; Create
                </button>
            </div>
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

// Intercept submit → show confirmation modal instead of posting straight away
var createForm = document.getElementById('createForm');
createForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!this.checkValidity()) { this.reportValidity(); return; }
    if (pwd.value !== conf.value) {
        matchErr.style.display = 'flex';
        confWrap.classList.add('has-error');
        conf.focus();
        return;
    }

    var roleInput  = document.querySelector('input[name="role_id"]:checked');
    var roleLabel  = roleInput ? document.querySelector('label[for="' + roleInput.id + '"] .role-name') : null;

    document.getElementById('confirmName').textContent   = (document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value).trim();
    document.getElementById('confirmEmail').textContent  = document.getElementById('email').value;
    document.getElementById('confirmUsername').textContent = document.getElementById('username').value;
    document.getElementById('confirmPhone').textContent  = document.getElementById('phone').value || '—';
    document.getElementById('confirmRole').textContent   = roleLabel ? roleLabel.textContent : '—';

    document.getElementById('createConfirmModal').classList.add('open');
});

function closeCreateConfirmModal() {
    document.getElementById('createConfirmModal').classList.remove('open');
}
document.getElementById('createConfirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateConfirmModal();
});

document.getElementById('confirmCreateBtn').addEventListener('click', function() {
    this.disabled = true;
    document.getElementById('btnSpinner').style.display = 'block';
    document.getElementById('btnText').textContent = ' Creating…';
    document.getElementById('submitBtn').disabled = true;
    createForm.submit();
});
</script>
@endsection
