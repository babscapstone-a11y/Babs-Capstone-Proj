@extends('layouts.customer-app')
@section('title', "My Profile – Bab's Resto")

@section('styles')
<style>
/* ══ PAGE FADE-IN ══════════════════════════════════════════ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp .42s cubic-bezier(.22,1,.36,1) both; }
.fade-up-2 { animation: fadeUp .42s .08s cubic-bezier(.22,1,.36,1) both; }
.fade-up-3 { animation: fadeUp .42s .16s cubic-bezier(.22,1,.36,1) both; }

/* ══ LAYOUT ═══════════════════════════════════════════════ */
.profile-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    align-items: start;
}
.profile-grid > div { min-width: 0; }
@media (max-width: 900px) {
    .profile-grid { grid-template-columns: 1fr; }
}

/* ══ CARDS ════════════════════════════════════════════════ */
.card {
    background: var(--white);
    border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    overflow: hidden;
    margin-bottom: 1.25rem;
    transition: box-shadow .2s;
}

/* ══ PROFILE CARD ═════════════════════════════════════════ */
.profile-card { text-align: center; }
.profile-card-bg {
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dk), #7f1d1d);
}
.profile-card-body { padding: 0 1.5rem 1.5rem; }
.avatar-ring {
    width: 88px; height: 88px; border-radius: 50%;
    border: 4px solid var(--white);
    box-shadow: 0 4px 16px rgba(220,38,38,.25);
    margin: -44px auto 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; color: var(--white);
    overflow: hidden; position: relative; cursor: pointer;
    transition: box-shadow .2s;
}
.avatar-ring:hover { box-shadow: 0 6px 24px rgba(220,38,38,.4); }
.avatar-ring img { width: 100%; height: 100%; object-fit: cover; }
.avatar-ring .avatar-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s;
    font-size: .7rem; color: #fff; flex-direction: column; gap: .2rem;
}
.avatar-ring:hover .avatar-overlay { opacity: 1; }
.avatar-overlay i { font-size: .9rem; }
.profile-name { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin: .85rem 0 .2rem; }
.profile-email { font-size: .78rem; color: var(--muted); margin-bottom: .85rem; }
.profile-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    background: #DCFCE7; color: #15803D;
    font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    padding: .22rem .75rem; border-radius: 50px;
}
.profile-meta { margin-top: 1rem; text-align: left; }
.profile-meta-row {
    display: flex; align-items: center; gap: .6rem;
    padding: .5rem 0; border-top: 1px solid #F3F4F6;
    font-size: .78rem; color: var(--muted);
}
.profile-meta-row i { width: 16px; text-align: center; color: var(--primary); font-size: .8rem; }
.profile-meta-row span { color: var(--text); font-weight: 500; }

/* ══ QUICK NAV ════════════════════════════════════════════ */
.quick-nav .qn-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1.25rem; font-size: .85rem; font-weight: 500;
    color: var(--text); cursor: pointer; transition: all .17s;
    border-left: 3px solid transparent;
}
.quick-nav .qn-item:hover { background: #FEF2F2; color: var(--primary); border-left-color: var(--primary); }
.quick-nav .qn-item i { width: 18px; text-align: center; color: var(--muted); font-size: .9rem; transition: color .17s; }
.quick-nav .qn-item:hover i { color: var(--primary); }

/* ══ SECTION HEADERS ══════════════════════════════════════ */
.card-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: #FAFBFC;
}
.card-header h2 {
    font-size: .95rem; font-weight: 700; color: var(--dark);
    display: flex; align-items: center; gap: .55rem;
}
.card-header h2 i { color: var(--primary); font-size: .9rem; }
.card-header .hd-sub { font-size: .76rem; color: var(--muted); }

/* ══ FORM STYLES ══════════════════════════════════════════ */
.form-body { padding: 1.4rem 1.5rem; }
.form-section-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: var(--muted);
    margin-bottom: .85rem; padding-bottom: .5rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: .45rem;
}
.form-section-title i { color: var(--primary); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
.field { margin-bottom: 1rem; min-width: 0; }
.field label {
    display: block; font-size: .8rem; font-weight: 600; color: var(--dark);
    margin-bottom: .4rem;
}
.field label .req { color: var(--primary); margin-left: .1rem; }
.field input, .field select, .field textarea {
    width: 100%; padding: .65rem .95rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-size: .85rem; font-family: inherit; color: var(--text);
    background: var(--white); outline: none;
    transition: border-color .18s, box-shadow .18s;
}
.field input:focus, .field select:focus, .field textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(220,38,38,.08);
}
.field input[readonly], .field input:disabled {
    background: var(--bg); color: var(--muted); cursor: not-allowed;
    border-color: var(--border);
}
.field .help { font-size: .72rem; color: var(--muted); margin-top: .3rem; }
.field-error { font-size: .76rem; color: var(--primary); margin-top: .3rem; display: flex; align-items: center; gap: .25rem; }

/* Profile picture upload field */
.pic-upload-wrap { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
.pic-preview {
    width: 68px; height: 68px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; font-weight: 700; color: #fff;
    overflow: hidden; flex-shrink: 0;
    border: 3px solid var(--border);
}
.pic-preview img { width: 100%; height: 100%; object-fit: cover; }
.pic-upload-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1rem; border-radius: 8px;
    border: 1.5px dashed var(--primary); background: #FEF2F2;
    color: var(--primary); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .18s;
}
.pic-upload-btn:hover { background: #FEE2E2; }
.pic-input { display: none; }

/* ══ BUTTONS ══════════════════════════════════════════════ */
.btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .62rem 1.4rem; border-radius: 10px;
    font-size: .85rem; font-weight: 600; font-family: inherit;
    cursor: pointer; border: none; transition: all .18s;
    text-decoration: none;
}
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-dk); box-shadow: 0 4px 12px rgba(220,38,38,.3); }
.btn-primary:active { transform: scale(.98); }
.btn-outline { background: var(--white); border: 1.5px solid var(--border); color: var(--text); }
.btn-outline:hover { border-color: var(--primary); color: var(--primary); }
.btn-sm { padding: .38rem .85rem; font-size: .78rem; }

.form-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: #FAFBFC;
    display: flex; justify-content: flex-end; gap: .75rem;
}

/* ══ PASSWORD STRENGTH ════════════════════════════════════ */
.pwd-wrap { position: relative; }
.pwd-wrap input { padding-right: 2.5rem; }
.pwd-toggle {
    position: absolute; right: .8rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--muted); font-size: .85rem; padding: .2rem;
}
.pwd-strength { margin-top: .4rem; }
.pwd-bar { height: 3px; border-radius: 2px; background: var(--border); overflow: hidden; }
.pwd-bar-fill { height: 100%; border-radius: 2px; transition: width .3s, background .3s; width: 0; }
.pwd-label { font-size: .7rem; margin-top: .2rem; color: var(--muted); }

/* ══ ORDER TABLE ══════════════════════════════════════════ */
.orders-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
.orders-table th {
    padding: .7rem 1rem; text-align: left;
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    color: var(--muted); background: #F8FAFC;
    border-bottom: 1px solid var(--border); white-space: nowrap;
}
.orders-table td {
    padding: .85rem 1rem; border-bottom: 1px solid #F5F5F5;
    color: var(--text); vertical-align: middle;
}
.orders-table tr:last-child td { border-bottom: none; }
.orders-table tr:hover td { background: #FAFAFA; }

/* Order type icon */
.order-type-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .73rem; font-weight: 600; padding: .22rem .65rem;
    border-radius: 20px; white-space: nowrap;
}
.ot-dine   { background: #EFF6FF; color: #1D4ED8; }
.ot-take   { background: #ECFEFF; color: #0E7490; }
.ot-online { background: #FFF7ED; color: #C2410C; }

/* Status badge */
.status-dot {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .73rem; font-weight: 600; padding: .22rem .65rem;
    border-radius: 20px; white-space: nowrap;
}
.status-dot::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%;
    background: currentColor; display: block;
}

/* Payment status badge */
.badge-paid     { background: #DCFCE7; color: #15803D; }
.badge-pending  { background: #FEF3C7; color: #92400E; }
.badge-failed   { background: #FEE2E2; color: #B91C1C; }
.badge-refunded { background: #EDE9FE; color: #6D28D9; }

/* ══ EMPTY STATE ══════════════════════════════════════════ */
.empty-state {
    text-align: center; padding: 3.5rem 2rem;
    display: flex; flex-direction: column; align-items: center; gap: 1rem;
}
.empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #FEF2F2;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; color: var(--primary); opacity: .55;
}
.empty-title { font-size: 1rem; font-weight: 700; color: var(--dark); }
.empty-sub   { font-size: .83rem; color: var(--muted); }

/* ══ FILTER BAR ═══════════════════════════════════════════ */
.filter-bar {
    display: flex; align-items: center; gap: .6rem; flex-wrap: wrap;
    padding: .85rem 1.25rem; border-bottom: 1px solid var(--border);
}
.filter-select {
    padding: .4rem .75rem; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: .8rem; font-family: inherit; color: var(--text);
    background: var(--white); outline: none;
}
.filter-select:focus { border-color: var(--primary); }

/* ══ BREADCRUMB ═══════════════════════════════════════════ */
.page-breadcrumb {
    display: flex; align-items: center; gap: .4rem;
    font-size: .78rem; color: var(--muted);
    margin-bottom: 1.25rem;
}
.page-breadcrumb a { color: var(--primary); font-weight: 500; }
.page-breadcrumb a:hover { text-decoration: underline; }
.page-breadcrumb span { opacity: .5; }
</style>
@endsection

@section('content')
<div class="page-wrap">

    {{-- Breadcrumb --}}
    <div class="page-breadcrumb fade-up">
        <a href="{{ route('catalog.index') }}"><i class="fas fa-home"></i> Menu</a>
        <span>/</span> My Profile
    </div>

    <div class="profile-grid">

        {{-- ═══ LEFT SIDEBAR ═══════════════════════════════════════ --}}
        <div>

            {{-- Profile Card --}}
            <div class="card profile-card fade-up">
                <div class="profile-card-bg"></div>
                <div class="profile-card-body">
                    <div class="avatar-ring" onclick="document.getElementById('profilePicInput').click()" title="Change photo">
                        @if($customer->profile_picture_url)
                            <img src="{{ $customer->profile_picture_url }}" alt="{{ $customer->full_name }}" id="avatarPreview">
                        @else
                            <span id="avatarInitials">{{ $customer->initials }}</span>
                        @endif
                        <div class="avatar-overlay"><i class="fas fa-camera"></i><span style="font-size:.65rem">Change</span></div>
                    </div>

                    <div class="profile-name">{{ $customer->full_name }}</div>
                    <div class="profile-email">{{ $customer->email }}</div>
                    <span class="profile-badge"><i class="fas fa-circle" style="font-size:.4rem"></i> Active Account</span>

                    <div class="profile-meta">
                        <div class="profile-meta-row">
                            <i class="fas fa-id-badge"></i>
                            Customer ID: <span>#{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="profile-meta-row">
                            <i class="fas fa-calendar"></i>
                            Joined: <span>{{ $customer->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($customer->contact_no)
                        <div class="profile-meta-row">
                            <i class="fas fa-phone"></i>
                            <span>{{ $customer->contact_no }}</span>
                        </div>
                        @endif
                        @if($customer->address)
                        <div class="profile-meta-row" style="align-items:flex-start">
                            <i class="fas fa-location-dot" style="margin-top:.15rem"></i>
                            <span>{{ $customer->address->full_address }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Nav --}}
            <div class="card quick-nav fade-up-2">
                <div class="card-header">
                    <h2><i class="fas fa-bars"></i> Quick Navigation</h2>
                </div>
                <a href="#profile"   class="qn-item"><i class="fas fa-user"></i> Profile Information</a>
                <a href="#password"  class="qn-item"><i class="fas fa-lock"></i> Change Password</a>
                <a href="#orders"    class="qn-item"><i class="fas fa-receipt"></i> Order History</a>
                <a href="{{ route('catalog.index') }}" class="qn-item"><i class="fas fa-utensils"></i> Browse Menu</a>
            </div>

        </div>

        {{-- ═══ RIGHT CONTENT ═══════════════════════════════════════ --}}
        <div>

            {{-- ─── PROFILE INFORMATION ─────────────────────────────── --}}
            <div class="card fade-up" id="profile">
                <div class="card-header">
                    <h2><i class="fas fa-user-pen"></i> Profile Information</h2>
                    <span class="hd-sub">Update your personal details</span>
                </div>

                <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf @method('PUT')

                    <div class="form-body">
                        {{-- Profile picture upload (hidden, triggered by avatar click) --}}
                        <input type="file" name="profile_picture" id="profilePicInput" class="pic-input" accept="image/*" onchange="previewPicture(this)">

                        {{-- Personal Info --}}
                        <div class="form-section-title"><i class="fas fa-user"></i> Personal Information</div>
                        <div class="form-row">
                            <div class="field">
                                <label>First Name <span class="req">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
                                @error('first_name')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}">
                                @error('last_name')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label>Email Address</label>
                                <input type="email" value="{{ $customer->email }}" readonly>
                                <div class="help"><i class="fas fa-lock" style="font-size:.65rem"></i> Email cannot be changed</div>
                            </div>
                            <div class="field">
                                <label>Contact Number</label>
                                <input type="text" name="contact_no" value="{{ old('contact_no', $customer->contact_no) }}" placeholder="+63 9XX XXX XXXX">
                                @error('contact_no')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Read-only fields --}}
                        <div class="form-row">
                            <div class="field">
                                <label>Customer ID</label>
                                <input type="text" value="#{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}" readonly>
                            </div>
                            <div class="field">
                                <label>Date Registered</label>
                                <input type="text" value="{{ $customer->created_at->format('F d, Y') }}" readonly>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="form-section-title" style="margin-top:.5rem"><i class="fas fa-location-dot"></i> Address Information</div>
                        <div class="field">
                            <label>Street / House No.</label>
                            <input type="text" name="street" value="{{ old('street', $customer->address?->street) }}" placeholder="e.g. 123 Mabini St.">
                            @error('street')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label>Barangay</label>
                                <input type="text" name="barangay" value="{{ old('barangay', $customer->address?->barangay) }}" placeholder="Barangay">
                                @error('barangay')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label>Municipality / City</label>
                                <input type="text" name="municipality" value="{{ old('municipality', $customer->address?->municipality) }}" placeholder="Municipality or City">
                                @error('municipality')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="field">
                            <label>Province</label>
                            <input type="text" name="province" value="{{ old('province', $customer->address?->province) }}" placeholder="Province">
                            @error('province')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        {{-- Profile picture upload hint --}}
                        <div style="display:flex;align-items:center;gap:.75rem;background:#FEF9F0;border:1.5px solid #FDE68A;border-radius:10px;padding:.75rem 1rem;margin-top:.25rem;font-size:.78rem;color:#92400E">
                            <i class="fas fa-circle-info"></i>
                            <span>Click on your profile picture above to upload a new photo. Accepted: JPG, PNG, GIF (max 2 MB).</span>
                        </div>
                        <div id="picChangeNotice" style="display:none;margin-top:.5rem;background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:10px;padding:.6rem 1rem;font-size:.78rem;color:#1D4ED8;align-items:center;gap:.5rem">
                            <i class="fas fa-image"></i> <span id="picFileName"></span> — will be saved when you click Save Changes.
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="reset" class="btn btn-outline" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary" onclick="return confirmSave()">
                            <i class="fas fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─── CHANGE PASSWORD ──────────────────────────────────── --}}
            <div class="card fade-up-2" id="password">
                <div class="card-header">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                    <span class="hd-sub">Keep your account secure</span>
                </div>

                <form method="POST" action="{{ route('account.password.update') }}" id="pwdForm" novalidate>
                    @csrf @method('PUT')

                    <div class="form-body">
                        <div class="field">
                            <label>Current Password <span class="req">*</span></label>
                            <div class="pwd-wrap">
                                <input type="password" name="current_password" id="currPwd" placeholder="Enter your current password" required>
                                <button type="button" class="pwd-toggle" onclick="togglePwd('currPwd', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            @error('current_password')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label>New Password <span class="req">*</span></label>
                            <div class="pwd-wrap">
                                <input type="password" name="new_password" id="newPwd" placeholder="Minimum 8 characters" required oninput="checkStrength(this.value)">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('newPwd', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="pwd-strength">
                                <div class="pwd-bar"><div class="pwd-bar-fill" id="pwdBar"></div></div>
                                <div class="pwd-label" id="pwdLabel"></div>
                            </div>
                            @error('new_password')<div class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label>Confirm New Password <span class="req">*</span></label>
                            <div class="pwd-wrap">
                                <input type="password" name="new_password_confirmation" id="confPwd" placeholder="Re-enter new password" required>
                                <button type="button" class="pwd-toggle" onclick="togglePwd('confPwd', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            <div id="matchMsg" style="font-size:.74rem;margin-top:.3rem"></div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="reset" class="btn btn-outline" onclick="resetPwdForm()">Clear</button>
                        <button type="submit" class="btn btn-primary" onclick="return confirmPwdChange()">
                            <i class="fas fa-shield-halved"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─── ORDER HISTORY ────────────────────────────────────── --}}
            <div class="card fade-up-3" id="orders">
                <div class="card-header">
                    <h2><i class="fas fa-receipt"></i> Order History</h2>
                    <span class="hd-sub">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }}</span>
                </div>

                @if($orders->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-bag-shopping"></i></div>
                        <div class="empty-title">No orders yet</div>
                        <div class="empty-sub">You haven't placed any orders yet. Start exploring our menu!</div>
                        <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="margin-top:.5rem">
                            <i class="fas fa-utensils"></i> Start Ordering
                        </a>
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                @php
                                    $typeClass = match($order->order_type) {
                                        'dine_in' => 'ot-dine',
                                        'takeout' => 'ot-take',
                                        'online'  => 'ot-online',
                                        default   => 'ot-dine',
                                    };
                                @endphp
                                <tr style="cursor:pointer" onclick="window.location='{{ route('account.orders.show', $order) }}'">
                                    <td>
                                        <span style="font-weight:700;color:var(--dark)">{{ $order->order_number ?? '#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td style="color:var(--muted);font-size:.79rem">
                                        {{ $order->created_at->format('M d, Y') }}<br>
                                        <span style="font-size:.72rem">{{ $order->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="order-type-chip {{ $typeClass }}">
                                            <i class="fas {{ $order->order_type_icon }}"></i>
                                            {{ $order->order_type_label }}
                                        </span>
                                    </td>
                                    <td style="font-weight:600">{{ $order->item_count }}</td>
                                    <td style="font-weight:700;color:var(--dark)">₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="status-dot {{ $order->payment_status_class }}">
                                            {{ $order->payment_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-dot" style="background:{{ $order->status_color }}1a; color:{{ $order->status_color }}">
                                            {{ $order->status_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline btn-sm" onclick="event.stopPropagation()">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:center">
                        {{ $orders->links() }}
                    </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
/* Profile picture preview */
function previewPicture(input) {
    const ring    = document.querySelector('.avatar-ring');
    const notice  = document.getElementById('picChangeNotice');
    const fname   = document.getElementById('picFileName');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            ring.innerHTML = `<img src="${e.target.result}" alt="Preview">
                <div class="avatar-overlay"><i class="fas fa-camera"></i><span style="font-size:.65rem">Change</span></div>`;
        };
        reader.readAsDataURL(input.files[0]);
        if (notice) {
            notice.style.display = 'flex';
            fname.textContent = input.files[0].name;
        }
    }
}

/* Password toggle */
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = `fas fa-eye${isText ? '' : '-slash'}`;
}

/* Password strength checker */
function checkStrength(val) {
    const bar   = document.getElementById('pwdBar');
    const label = document.getElementById('pwdLabel');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { pct: '20%', bg: '#DC2626', text: 'Very Weak' },
        { pct: '40%', bg: '#F59E0B', text: 'Weak' },
        { pct: '65%', bg: '#F59E0B', text: 'Fair' },
        { pct: '85%', bg: '#16A34A', text: 'Strong' },
        { pct: '100%', bg: '#15803D', text: 'Very Strong' },
    ];
    const l = val.length === 0 ? null : levels[score];
    bar.style.width  = l ? l.pct : '0';
    bar.style.background = l ? l.bg : '';
    label.textContent = l ? l.text : '';
    label.style.color = l ? l.bg : '';
    checkMatch();
}

/* Password match */
document.getElementById('confPwd').addEventListener('input', checkMatch);
function checkMatch() {
    const np  = document.getElementById('newPwd').value;
    const cp  = document.getElementById('confPwd').value;
    const msg = document.getElementById('matchMsg');
    if (!cp) { msg.textContent = ''; return; }
    if (np === cp) {
        msg.innerHTML = '<span style="color:#16A34A"><i class="fas fa-check-circle"></i> Passwords match</span>';
    } else {
        msg.innerHTML = '<span style="color:#DC2626"><i class="fas fa-xmark-circle"></i> Passwords do not match</span>';
    }
}

/* Confirm save profile */
function confirmSave() {
    return confirm('Save changes to your profile?');
}

/* Confirm password change */
function confirmPwdChange() {
    const np = document.getElementById('newPwd').value;
    const cp = document.getElementById('confPwd').value;
    if (np !== cp) {
        alert('New passwords do not match. Please check and try again.');
        return false;
    }
    return confirm('Update your password?');
}

function resetForm() {
    document.getElementById('picChangeNotice').style.display = 'none';
}
function resetPwdForm() {
    ['currPwd','newPwd','confPwd'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('pwdBar').style.width = '0';
    document.getElementById('pwdLabel').textContent = '';
    document.getElementById('matchMsg').textContent = '';
}

/* Smooth scroll for quick nav links */
document.querySelectorAll('.qn-item[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(a.getAttribute('href'));
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

/* Auto-scroll to error section */
@if($errors->has('current_password') || $errors->has('new_password'))
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('password')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
@endif
</script>
@endsection
