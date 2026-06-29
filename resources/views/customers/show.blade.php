@extends('layouts.admin')

@section('title', $customer->full_name)
@section('page-title', 'Customer Accounts')

@section('breadcrumb')
    <a href="{{ route('customers.index') }}" style="color:var(--primary);text-decoration:none">Customer Accounts</a>
    <i class="fas fa-chevron-right" style="font-size:.65rem;margin:0 .35rem;color:var(--muted)"></i>
    <span>{{ Str::limit($customer->full_name, 35) }}</span>
@endsection

@section('styles')
<style>
    .show-layout { display: grid; grid-template-columns: 290px 1fr; gap: 1.25rem; align-items: start; }

    /* Profile card */
    .profile-card {
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 18px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(17,24,39,0.07);
        position: sticky; top: 90px;
    }
    .profile-hero {
        background: linear-gradient(145deg, #0f0a2e 0%, #1e1050 50%, #111827 100%);
        padding: 1.75rem 1.25rem 1.5rem;
        text-align: center; position: relative; overflow: hidden;
    }
    .profile-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse 70% 60% at 80% 20%, rgba(124,58,237,0.25) 0%, transparent 60%);
        pointer-events: none;
    }
    .hero-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #7C3AED, #2563EB);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; font-weight: 800; color: #fff;
        margin: 0 auto .85rem;
        border: 3px solid rgba(255,255,255,0.12);
        position: relative; z-index: 1;
    }
    .hero-name {
        font-size: 1.05rem; font-weight: 800; color: #fff; margin: 0 0 .4rem;
        position: relative; z-index: 1;
    }
    .hero-email {
        font-size: .75rem; color: rgba(255,255,255,0.5); position: relative; z-index: 1;
        word-break: break-all;
    }
    .hero-badges { display: flex; gap: .4rem; justify-content: center; flex-wrap: wrap; margin-top: .65rem; position: relative; z-index: 1; }

    .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .65rem; border-radius: 50px; font-size: .7rem; font-weight: 700; }
    .badge-active   { background: rgba(22,163,74,0.15);  color: #86EFAC; border: 1px solid rgba(22,163,74,0.3); }
    .badge-inactive { background: rgba(220,38,38,0.15);  color: #FCA5A5; border: 1px solid rgba(220,38,38,0.3); }
    .badge-role     { background: rgba(124,58,237,0.15); color: #C4B5FD; border: 1px solid rgba(124,58,237,0.3); }

    /* Detail rows */
    .detail-body { padding: 1.1rem 1.2rem; }
    .detail-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: .55rem 0; border-bottom: 1px solid var(--border);
        font-size: .82rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--muted); font-weight: 600; display: flex; align-items: center; gap: .45rem; }
    .detail-value { color: var(--dark); font-weight: 600; text-align: right; max-width: 58%; word-break: break-word; }

    /* Quick actions */
    .quick-actions { padding: 1rem 1.2rem; border-top: 1px solid var(--border); }
    .qa-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .6rem; }
    .action-btn {
        display: flex; align-items: center; gap: .55rem;
        padding: .6rem .9rem; border-radius: 10px; font-size: .83rem; font-weight: 600;
        border: 1.5px solid; cursor: pointer; font-family: inherit;
        text-decoration: none; transition: all .18s; width: 100%;
        margin-bottom: .4rem; box-sizing: border-box;
    }
    .action-btn:last-child { margin-bottom: 0; }
    .btn-deact { color: #B91C1C; border-color: rgba(220,38,38,0.3); background: rgba(220,38,38,0.06); }
    .btn-deact:hover { background: rgba(220,38,38,0.12); }
    .btn-activ { color: #15803D; border-color: rgba(22,163,74,0.3); background: rgba(22,163,74,0.06); }
    .btn-activ:hover { background: rgba(22,163,74,0.12); }

    /* Right content */
    .content-stack { display: flex; flex-direction: column; gap: 1.1rem; }
    .info-card {
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 16px; overflow: hidden;
        box-shadow: 0 2px 10px rgba(17,24,39,0.04);
    }
    .info-card-header {
        padding: .85rem 1.2rem; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .55rem;
        font-size: .82rem; font-weight: 700; color: var(--dark);
    }
    .info-card-header i { color: var(--primary); }
    .info-card-body { padding: 1.2rem 1.25rem; }

    /* Order placeholder grid */
    .order-placeholder-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: .85rem; }
    .order-placeholder-card {
        background: var(--bg); border: 1.5px solid var(--border); border-radius: 12px;
        padding: 1rem 1.1rem; text-align: center;
    }
    .order-placeholder-val { font-size: 1.5rem; font-weight: 800; color: var(--muted); }
    .order-placeholder-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-top: .2rem; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    .anim-1 { animation: fadeUp .4s ease both; }
    .anim-2 { animation: fadeUp .4s .06s ease both; }

    @media (max-width:860px) {
        .show-layout { grid-template-columns: 1fr; }
        .profile-card { position: static; }
        .order-placeholder-grid { grid-template-columns: repeat(2,1fr); }
    }
    @media (max-width:480px) {
        .order-placeholder-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

<div class="show-layout">

    {{-- Left: Profile Card --}}
    <div class="anim-1">
        <div class="profile-card">
            <div class="profile-hero">
                <div class="hero-avatar">{{ $customer->initials }}</div>
                <div class="hero-name">{{ $customer->full_name }}</div>
                <div class="hero-email">{{ $customer->email }}</div>
                <div class="hero-badges">
                    <span class="badge badge-role"><i class="fas fa-user" style="font-size:.5rem"></i> Customer</span>
                    @if($customer->status === 'active')
                        <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.4rem"></i> Active</span>
                    @else
                        <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.4rem"></i> Inactive</span>
                    @endif
                </div>
            </div>

            <div class="detail-body">
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-hashtag" style="color:var(--primary)"></i> Customer ID</span>
                    <span class="detail-value">#{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-phone" style="color:#7C3AED"></i> Contact</span>
                    <span class="detail-value">{{ $customer->contact_no ?: '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-plus" style="color:#2563EB"></i> Registered</span>
                    <span class="detail-value">{{ $customer->created_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-clock" style="color:#16A34A"></i> Last Login</span>
                    <span class="detail-value">—</span>
                </div>
            </div>

            @can('toggleStatus', $customer)
            <div class="quick-actions">
                <div class="qa-title">Account Actions</div>
                <button type="button"
                    class="action-btn {{ $customer->status === 'active' ? 'btn-deact' : 'btn-activ' }}"
                    onclick="openToggleModal({{ $customer->id }}, '{{ addslashes($customer->full_name) }}', {{ $customer->status === 'active' ? 'true' : 'false' }})">
                    <i class="fas fa-{{ $customer->status === 'active' ? 'ban' : 'check' }}"></i>
                    {{ $customer->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </div>
            @endcan
        </div>
    </div>

    {{-- Right: Detail Cards --}}
    <div class="content-stack anim-2">

        {{-- Customer Information --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-user"></i> Customer Information
            </div>
            <div class="info-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem">
                    <div style="padding:.85rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem">First Name</div>
                        <div style="font-size:.9rem;font-weight:700;color:var(--dark)">{{ $customer->first_name ?: '—' }}</div>
                    </div>
                    <div style="padding:.85rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem">Last Name</div>
                        <div style="font-size:.9rem;font-weight:700;color:var(--dark)">{{ $customer->last_name ?: '—' }}</div>
                    </div>
                    <div style="padding:.85rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border);grid-column:span 2">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem">Email Address</div>
                        <div style="font-size:.9rem;font-weight:700;color:var(--dark)">{{ $customer->email }}</div>
                    </div>
                    <div style="padding:.85rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem">Contact Number</div>
                        <div style="font-size:.9rem;font-weight:700;color:var(--dark)">{{ $customer->contact_no ?: '—' }}</div>
                    </div>
                    <div style="padding:.85rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.3rem">Account Status</div>
                        <div style="font-size:.9rem;font-weight:700;color:{{ $customer->status === 'active' ? '#16A34A' : '#DC2626' }}">
                            {{ ucfirst($customer->status) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-map-marker-alt"></i> Address
            </div>
            <div class="info-card-body">
                @if($customer->address)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        @foreach(['street' => 'Street / Building', 'barangay' => 'Barangay', 'municipality' => 'Municipality / City', 'province' => 'Province'] as $field => $label)
                        <div style="padding:.75rem .9rem;background:var(--bg);border-radius:10px;border:1.5px solid var(--border)">
                            <div style="font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.25rem">{{ $label }}</div>
                            <div style="font-size:.85rem;font-weight:600;color:var(--dark)">{{ $customer->address->$field ?: '—' }}</div>
                        </div>
                        @endforeach
                    </div>
                    <div style="margin-top:.85rem;padding:.75rem 1rem;background:rgba(124,58,237,0.05);border:1.5px solid rgba(124,58,237,0.15);border-radius:10px;font-size:.82rem;color:#5B21B6">
                        <i class="fas fa-map-marker-alt" style="margin-right:.35rem"></i>
                        {{ $customer->address->full_address ?: '—' }}
                    </div>
                @else
                    <div style="text-align:center;padding:1.5rem;color:var(--muted)">
                        <i class="fas fa-map-marker-alt" style="font-size:1.5rem;margin-bottom:.6rem;display:block;opacity:.35"></i>
                        <div style="font-size:.85rem;font-weight:600;color:var(--dark);margin-bottom:.25rem">No Address Saved</div>
                        <div style="font-size:.78rem">The customer has not added an address yet.</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Order Summary (placeholder) --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-receipt"></i> Order Summary
                <span style="margin-left:auto;font-size:.7rem;font-weight:500;color:var(--muted)">Order Module not yet implemented</span>
            </div>
            <div class="info-card-body">
                <div style="background:rgba(245,158,11,0.06);border:1.5px solid rgba(245,158,11,0.2);border-radius:12px;padding:.75rem 1rem;display:flex;align-items:center;gap:.55rem;font-size:.8rem;color:#92400E;margin-bottom:1rem">
                    <i class="fas fa-hourglass-half" style="flex-shrink:0"></i>
                    <span>Order history will be available once the Order Management Module is implemented.</span>
                </div>
                <div class="order-placeholder-grid">
                    <div class="order-placeholder-card">
                        <div class="order-placeholder-val">—</div>
                        <div class="order-placeholder-lbl">Total Orders</div>
                    </div>
                    <div class="order-placeholder-card">
                        <div class="order-placeholder-val">—</div>
                        <div class="order-placeholder-lbl">Completed</div>
                    </div>
                    <div class="order-placeholder-card">
                        <div class="order-placeholder-val">—</div>
                        <div class="order-placeholder-lbl">Cancelled</div>
                    </div>
                    <div class="order-placeholder-card">
                        <div class="order-placeholder-val">₱—</div>
                        <div class="order-placeholder-lbl">Amount Spent</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registration & Account Info --}}
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-clock"></i> Registration & Account Info
            </div>
            <div class="info-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem">
                    <div style="padding:.9rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.35rem">Registration Date</div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--dark)">{{ $customer->created_at->format('F d, Y') }}</div>
                        <div style="font-size:.74rem;color:var(--muted)">{{ $customer->created_at->format('h:i A') }}</div>
                    </div>
                    <div style="padding:.9rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border)">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.35rem">Last Updated</div>
                        <div style="font-size:.88rem;font-weight:700;color:var(--dark)">{{ $customer->updated_at->format('F d, Y') }}</div>
                        <div style="font-size:.74rem;color:var(--muted)">{{ $customer->updated_at->format('h:i A') }}</div>
                    </div>
                    @if($customer->user)
                    <div style="padding:.9rem 1rem;background:var(--bg);border-radius:12px;border:1.5px solid var(--border);grid-column:span 2">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.35rem">Linked User Account</div>
                        <div style="font-size:.85rem;font-weight:700;color:var(--dark)">{{ $customer->user->name }}</div>
                        <div style="font-size:.74rem;color:var(--muted)">ID: #{{ $customer->user->id }} · Role: Customer</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigation footer --}}
        <div style="display:flex;justify-content:flex-end">
            <a href="{{ route('customers.index') }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-weight:600;color:var(--dark);text-decoration:none;background:#fff;transition:border-color .18s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                <i class="fas fa-arrow-left"></i> Back to Customer List
            </a>
        </div>

    </div>{{-- /content-stack --}}
</div>

{{-- Toggle Modal --}}
<div id="toggleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:18px;padding:2rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="text-align:center;margin-bottom:1.25rem">
            <div id="toggleIcon" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto .9rem"></div>
            <h3 id="toggleTitle" style="font-size:1.05rem;font-weight:700;color:var(--dark);margin:0 0 .5rem"></h3>
            <p id="toggleBody" style="font-size:.85rem;color:var(--muted);margin:0;line-height:1.55"></p>
        </div>
        <form id="toggleForm" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;gap:.75rem;justify-content:center">
                <button type="button" onclick="closeToggleModal()" style="flex:1;padding:.65rem;border:1.5px solid var(--border);border-radius:10px;background:transparent;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="toggleSubmit" style="flex:1;padding:.65rem;border:none;border-radius:10px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer">Confirm</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openToggleModal(id, name, isActive) {
    var icon   = document.getElementById('toggleIcon');
    var title  = document.getElementById('toggleTitle');
    var body   = document.getElementById('toggleBody');
    var form   = document.getElementById('toggleForm');
    var submit = document.getElementById('toggleSubmit');
    if (isActive) {
        icon.style.background = 'rgba(220,38,38,0.10)'; icon.style.color = '#DC2626';
        icon.innerHTML = '<i class="fas fa-ban"></i>';
        title.textContent = 'Deactivate "' + name + '"?';
        body.innerHTML    = 'This customer will be <strong>logged out immediately</strong> and cannot log in or place orders. Their account and history remain in the database.';
        submit.style.background = 'linear-gradient(90deg,#DC2626,#F97316)';
        submit.textContent = 'Deactivate Account';
    } else {
        icon.style.background = 'rgba(22,163,74,0.10)'; icon.style.color = '#16A34A';
        icon.innerHTML = '<i class="fas fa-check"></i>';
        title.textContent = 'Activate "' + name + '"?';
        body.innerHTML    = 'This customer will be able to <strong>log in and place orders</strong> again.';
        submit.style.background = 'linear-gradient(90deg,#16A34A,#059669)';
        submit.textContent = 'Activate Account';
    }
    form.action = '/customers/' + id + '/toggle-status';
    document.getElementById('toggleModal').style.display = 'flex';
}
function closeToggleModal() {
    document.getElementById('toggleModal').style.display = 'none';
}
document.getElementById('toggleModal').addEventListener('click', function(e) {
    if (e.target === this) closeToggleModal();
});
</script>
@endsection
