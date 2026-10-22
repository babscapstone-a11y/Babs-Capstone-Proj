@extends('layouts.admin')

@section('title', 'Add Staff Account')
@section('page-title', 'Add Staff Account')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="breadcrumb-sep">/</span>
    <span>Add Staff</span>
@endsection

@section('styles')
<style>
    .form-grid   { display:grid; grid-template-columns:1fr 1fr; gap:1.1rem; }
    .form-grid .span-2 { grid-column:1/-1; }
    /* Birthday: calendar pop-up button inside the same box as the other inputs */
    .input-wrap.bday-wrap .cd-field.bday-field { flex:1; height:auto; min-height:44px; border:0; border-radius:0 12px 12px 0;
        background:transparent; padding:.68rem .85rem .68rem 0; font-size:.9rem; box-shadow:none; }
    .input-wrap.bday-wrap .cd-field.bday-field i { color:var(--muted); }
    .bday-wrap:has(.cd-field.active) { border-color:var(--primary); box-shadow:0 0 0 4px rgba(220,38,38,0.08); }
    .bday-wrap:has(.cd-field.has-error) { border-color:#EF4444; box-shadow:0 0 0 4px rgba(239,68,68,0.08); }
    .section-label {
        font-size:.72rem; font-weight:700; letter-spacing:.07em;
        text-transform:uppercase; color:var(--muted);
        padding-bottom:.6rem; border-bottom:1px solid var(--border);
        margin-bottom:1rem;
    }
    .role-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:.65rem; }
    .role-option { display:none; }
    .role-label {
        display:flex; flex-direction:column; align-items:center;
        gap:.4rem; padding:.9rem .75rem; border-radius:12px;
        border:2px solid var(--border); cursor:pointer;
        transition:all .2s; text-align:center; background:var(--surface);
    }
    .role-label .role-icon { font-size:1.3rem; }
    .role-label .role-name { font-size:.8rem; font-weight:600; color:var(--muted); }
    .role-option:checked + .role-label {
        border-color:var(--primary);
        background:rgba(220,38,38,0.05);
    }
    .role-option:checked + .role-label .role-name { color:var(--primary); }
    .req-mark { color:var(--primary); margin-left:.1rem; font-weight:700; }
    .form-grid .col-right { grid-column:2; }   /* Confirm Password sits directly under Password */
    @media(max-width:640px) { .form-grid { grid-template-columns:1fr; } .form-grid .span-2, .form-grid .col-right { grid-column:auto; } }
</style>
@endsection

@section('content')
<div>

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
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> First Name <span class="req-mark" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrap @error('first_name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="first_name" name="first_name" type="text" class="form-input"
                                   pattern="[\p{L}\s'.\-]+" title="Letters only — spaces, hyphens, apostrophes and periods are allowed"
                                   oninput="this.value=this.value.replace(/[0-9]/g,'')"
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
                            <i class="fas fa-user" style="color:var(--primary);font-size:.78rem"></i> Last Name <span class="req-mark" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrap @error('last_name') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input id="last_name" name="last_name" type="text" class="form-input"
                                   pattern="[\p{L}\s'.\-]+" title="Letters only — spaces, hyphens, apostrophes and periods are allowed"
                                   oninput="this.value=this.value.replace(/[0-9]/g,'')"
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
                            <i class="fas fa-envelope" style="color:var(--primary);font-size:.78rem"></i> Email Address <span class="req-mark" aria-hidden="true">*</span>
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
                            <i class="fas fa-at" style="color:var(--primary);font-size:.78rem"></i> Username <span class="req-mark" aria-hidden="true">*</span>
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
                            <i class="fas fa-phone" style="color:var(--primary);font-size:.78rem"></i> Phone <span class="req-mark" aria-hidden="true">*</span>
                        </label>
                        <div class="input-wrap @error('phone') has-error @enderror">
                            <span class="input-icon"><i class="fas fa-mobile-screen"></i></span>
                            <input id="phone" name="phone" type="tel" class="form-input"
                                   value="{{ old('phone') }}" placeholder="09XXXXXXXXX" required
                                   inputmode="numeric" maxlength="11" pattern="[0-9]{11}"
                                   title="Enter an 11-digit mobile number, e.g. 09171234567"
                                   aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                        </div>
                        @error('phone')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="birthday">
                            <i class="fas fa-cake-candles" style="color:var(--primary);font-size:.78rem"></i> Birthday <span class="req-mark" aria-hidden="true">*</span>
                        </label>
                        {{-- Same calendar pop-up as the downtime schedule, with month/year drop-downs --}}
                        <input type="hidden" name="birthday" id="birthday" value="{{ old('birthday') }}"
                               data-min="1900-01-01" data-max="{{ now()->subDay()->toDateString() }}" data-start="{{ now()->subYears(25)->startOfYear()->toDateString() }}">
                        <div class="input-wrap bday-wrap">
                            <span class="input-icon"><i class="fas fa-calendar"></i></span>
                            <button type="button" class="cd-field bday-field {{ $errors->has('birthday') ? 'has-error' : '' }}" id="birthdayBtn"
                                    data-picker="date" data-for="birthday" data-year-select data-placeholder="Select birthday"
                                    aria-haspopup="dialog" aria-label="Birthday" onclick="DatePick.open(this)">
                                <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
                            </button>
                        </div>
                        @error('birthday')
                            <div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                        @enderror
                        <div class="field-error" id="birthdayError" style="display:none">
                            <i class="fas fa-circle-exclamation"></i> Please select the staff member's birthday.
                        </div>
                    </div>

                    {{-- No password to type: the system makes a default one from last name + birthday --}}
                    <div class="form-group span-2" style="background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.18);border-radius:12px;padding:.8rem 1rem;font-size:.82rem;color:var(--text-2, #374151)">
                        <i class="fas fa-key" style="color:#2563EB"></i>
                        <strong>Default password:</strong> the system creates it from the last name and birthday —
                        e.g. <em>Dela Cruz</em>, born Jan 15, 2000 → <code>delacruz01152000</code>.
                        The staff member must set a new password the first time they log in.
                    </div>

                </div>

                {{-- Role --}}
                <div class="section-label" style="margin-top:1.5rem">Assigned Role <span class="req-mark" aria-hidden="true">*</span></div>
                <div class="role-grid">
                    @foreach($roles as $r)
                    @php
                        $icons = ['admin'=>'fa-user-shield','cashier'=>'fa-cash-register','kitchen_staff'=>'fa-utensils','table_server'=>'fa-concierge-bell'];
                        $icon  = $icons[$r->role_name] ?? 'fa-user';
                    @endphp
                    <div>
                        <input type="radio" name="role_id" id="role_{{ $r->id }}" value="{{ $r->id }}"
                               class="role-option" {{ old('role_id') == $r->id ? 'checked' : '' }}>
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
                <div class="field-error" id="roleError" style="margin-top:.4rem;display:none">
                    <i class="fas fa-circle-exclamation"></i> Please select a role for this staff member.
                </div>

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
                <div style="display:flex;justify-content:space-between;gap:.75rem"><span style="color:var(--muted)">Birthday</span><strong id="confirmBirthday" style="text-align:right"></strong></div>
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
@include('partials.clock-dial')
@include('partials.date-picker')
<script>
document.getElementById('birthday').addEventListener('change', function () {
    document.getElementById('birthdayError').style.display = 'none';
});

// Intercept submit → show confirmation modal instead of posting straight away
var createForm = document.getElementById('createForm');
document.querySelectorAll('input[name="role_id"]').forEach(function (r) {
    r.addEventListener('change', function () { document.getElementById('roleError').style.display = 'none'; });
});

createForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!this.checkValidity()) { this.reportValidity(); return; }

    // Birthday is a calendar pop-up (hidden input), so the browser can't check it — do it here
    var bdayOk = !!document.getElementById('birthday').value;
    document.getElementById('birthdayError').style.display = bdayOk ? 'none' : 'flex';
    document.getElementById('birthdayBtn').classList.toggle('has-error', !bdayOk);
    if (!bdayOk) { document.getElementById('birthdayBtn').focus(); return; }

    // Role is required. Its radios are hidden behind styled cards, so the browser can't point at them —
    // check it here (after the visible fields) and show our own message.
    var roleChosen = !!document.querySelector('input[name="role_id"]:checked');
    document.getElementById('roleError').style.display = roleChosen ? 'none' : 'flex';
    if (!roleChosen) {
        document.querySelector('.role-grid').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    var roleInput  = document.querySelector('input[name="role_id"]:checked');
    var roleLabel  = roleInput ? document.querySelector('label[for="' + roleInput.id + '"] .role-name') : null;

    document.getElementById('confirmName').textContent   = (document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value).trim();
    document.getElementById('confirmEmail').textContent  = document.getElementById('email').value;
    document.getElementById('confirmUsername').textContent = document.getElementById('username').value;
    document.getElementById('confirmPhone').textContent  = document.getElementById('phone').value || '—';
    var bday = document.getElementById('birthday').value;
    document.getElementById('confirmBirthday').textContent = bday
        ? new Date(bday + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
        : '—';
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
