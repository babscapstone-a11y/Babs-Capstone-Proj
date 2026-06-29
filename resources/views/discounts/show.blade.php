@extends('layouts.admin')
@section('title', $discount->discount_name)
@section('page-title', $discount->discount_name)
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('discounts.index') }}">Discounts</a>
    <span class="breadcrumb-sep">/</span> {{ $discount->discount_name }}
@endsection

@section('styles')
<style>
.disc-page{max-width:1000px;margin:0 auto}
.disc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.disc-title{font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.disc-title i{color:var(--primary)}
.show-grid{display:grid;grid-template-columns:1fr 300px;gap:1.25rem;align-items:start}
@media(max-width:800px){.show-grid{grid-template-columns:1fr}}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.1rem}
.card-hd{padding:.9rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#FAFBFC}
.card-hd h3{font-size:.9rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.card-hd h3 i{color:var(--primary)}
.info-list{padding:0}
.info-row{display:flex;align-items:flex-start;padding:.95rem 1.4rem;border-bottom:1px solid #F3F4F6;gap:1rem}
.info-row:last-child{border-bottom:none}
.info-lbl{font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);min-width:160px;flex-shrink:0;padding-top:.05rem}
.info-val{font-size:.9rem;color:var(--dark);flex:1}
.value-large{font-size:2rem;font-weight:900;line-height:1}
.value-pct{color:#16A34A}
.value-fix{color:#7C3AED}
.badge-disc-active{background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase}
.badge-disc-inactive{background:#F3F4F6;color:#6B7280;display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase}
.badge-disc-expired{background:#FEE2E2;color:#B91C1C;display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase}
.badge-type-pct{background:#EFF6FF;color:#1D4ED8;padding:.22rem .7rem;border-radius:8px;font-size:.78rem;font-weight:700;display:inline-block}
.badge-type-fix{background:#F5F3FF;color:#7C3AED;padding:.22rem .7rem;border-radius:8px;font-size:.78rem;font-weight:700;display:inline-block}
.elig-badge{padding:.22rem .7rem;border-radius:8px;font-size:.78rem;font-weight:700;display:inline-block}
.elig-senior{background:#EFF6FF;color:#1D4ED8}.elig-pwd{background:#F5F3FF;color:#7C3AED}.elig-promo{background:#FEF3C7;color:#92400E}
.elig-employee{background:#F0FDF4;color:#15803D}.elig-min{background:#ECFEFF;color:#0E7490}.elig-date{background:#FFF7ED;color:#C2410C}.elig-all{background:#F3F4F6;color:#6B7280}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.btn-amber{background:#D97706;color:#fff}.btn-amber:hover{background:#B45309}
.highlight-card{background:linear-gradient(135deg,var(--dark),#1F2937);border-radius:16px;padding:1.75rem;color:#fff;margin-bottom:1.1rem;position:relative;overflow:hidden}
.highlight-card::before{content:'';position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(220,38,38,.15);right:-40px;top:-40px}
.hl-type{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.45);margin-bottom:.5rem}
.hl-name{font-size:1.2rem;font-weight:800;margin-bottom:.75rem}
.hl-value{font-size:2.8rem;font-weight:900;color:var(--accent);line-height:1;position:relative;z-index:1}
.hl-elig{margin-top:.85rem;font-size:.82rem;color:rgba(255,255,255,.65);display:flex;align-items:center;gap:.4rem}
.meta-info{font-size:.75rem;color:var(--muted);margin-top:.6rem;display:flex;flex-direction:column;gap:.35rem}
.meta-info span{display:flex;align-items:center;gap:.4rem}
.notice-box{background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:12px;padding:1rem 1.2rem;font-size:.82rem;color:#1D4ED8;line-height:1.6;display:flex;gap:.65rem}
.notice-box i{color:#60A5FA;font-size:.95rem;flex-shrink:0;margin-top:.05rem}
.expired-banner{background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;font-size:.83rem;color:#B91C1C;font-weight:500}
.section-divider{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:.7rem 1.4rem;background:#F8FAFC;border-bottom:1px solid var(--border)}
</style>
@endsection

@section('content')
<div class="disc-page">

    <div class="disc-header">
        <div>
            <div class="disc-title"><i class="fas fa-tag"></i> {{ $discount->discount_name }}</div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.25rem">Created {{ $discount->created_at->format('F d, Y') }}</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('discounts.edit', $discount) }}" class="btn btn-outline"><i class="fas fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('discounts.toggle-status', $discount) }}"
                  onsubmit="return confirm('{{ $discount->is_active ? 'Deactivate' : 'Activate' }} discount \"{{ addslashes($discount->discount_name) }}\"?')">
                @csrf @method('PUT')
                <button type="submit" class="btn {{ $discount->is_active ? 'btn-amber' : 'btn-green' }}">
                    <i class="fas {{ $discount->is_active ? 'fa-circle-pause' : 'fa-circle-play' }}"></i>
                    {{ $discount->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('discounts.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    @if($discount->is_expired && $discount->is_active)
    <div class="expired-banner">
        <i class="fas fa-clock"></i>
        <div>This discount has <strong>expired</strong> (end date: {{ $discount->end_date->format('F d, Y') }}). It is currently still active but cannot be applied to new orders past its end date.</div>
    </div>
    @endif

    <div class="show-grid">

        {{-- Left: Details --}}
        <div>
            {{-- Highlight Card --}}
            <div class="highlight-card">
                <div class="hl-type">{{ $discount->type_label }}</div>
                <div class="hl-name">{{ $discount->discount_name }}</div>
                <div class="hl-value">{{ $discount->formatted_value }}</div>
                @php
                    $eligClass = match($discount->eligibility_type) {
                        'senior_citizen' => 'elig-senior','pwd' => 'elig-pwd','promotional' => 'elig-promo',
                        'employee' => 'elig-employee','minimum_purchase' => 'elig-min','date_range' => 'elig-date',
                        default => 'elig-all'
                    };
                @endphp
                <div class="hl-elig"><i class="fas fa-users"></i> {{ $discount->eligibility_label }}</div>
            </div>

            {{-- Discount Information --}}
            <div class="card">
                <div class="card-hd"><h3><i class="fas fa-info-circle"></i> Discount Information</h3></div>
                <div class="info-list">
                    <div class="info-row">
                        <div class="info-lbl">Discount Name</div>
                        <div class="info-val" style="font-weight:700">{{ $discount->discount_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Discount Type</div>
                        <div class="info-val"><span class="{{ $discount->discount_type==='percentage' ? 'badge-type-pct' : 'badge-type-fix' }}">{{ $discount->type_label }}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Discount Value</div>
                        <div class="info-val"><span class="value-large {{ $discount->discount_type==='percentage' ? 'value-pct' : 'value-fix' }}">{{ $discount->formatted_value }}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Eligibility</div>
                        <div class="info-val"><span class="elig-badge {{ $eligClass }}">{{ $discount->eligibility_label }}</span></div>
                    </div>
                    @if($discount->description)
                    <div class="info-row">
                        <div class="info-lbl">Description</div>
                        <div class="info-val" style="line-height:1.6;color:var(--muted)">{{ $discount->description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Conditions --}}
            @if($discount->minimum_purchase || $discount->maximum_discount || $discount->start_date || $discount->end_date)
            <div class="card">
                <div class="card-hd"><h3><i class="fas fa-sliders"></i> Conditions</h3></div>
                <div class="info-list">
                    @if($discount->minimum_purchase)
                    <div class="info-row">
                        <div class="info-lbl">Minimum Purchase</div>
                        <div class="info-val" style="font-weight:700">₱{{ number_format($discount->minimum_purchase, 2) }}</div>
                    </div>
                    @endif
                    @if($discount->maximum_discount)
                    <div class="info-row">
                        <div class="info-lbl">Maximum Discount</div>
                        <div class="info-val" style="font-weight:700">₱{{ number_format($discount->maximum_discount, 2) }}</div>
                    </div>
                    @endif
                    @if($discount->start_date || $discount->end_date)
                    <div class="info-row">
                        <div class="info-lbl">Validity Period</div>
                        <div class="info-val">{{ $discount->validity_period }}</div>
                    </div>
                    @if($discount->end_date)
                    <div class="info-row">
                        <div class="info-lbl">Days Remaining</div>
                        <div class="info-val">
                            @if($discount->is_expired)
                            <span style="color:#DC2626;font-weight:700"><i class="fas fa-clock"></i> Expired {{ $discount->end_date->diffForHumans() }}</span>
                            @else
                            <span style="color:#16A34A;font-weight:700"><i class="fas fa-calendar-check"></i> Expires {{ $discount->end_date->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            @endif

            {{-- Future Integration Notice --}}
            <div class="notice-box">
                <i class="fas fa-circle-info"></i>
                <div>This discount will become available in the <strong>POS Module</strong> and <strong>Online Ordering Checkout Module</strong> when set to <strong>Active</strong>. Actual discount application will be handled during the transaction process.</div>
            </div>
        </div>

        {{-- Right: Status & Meta --}}
        <div>
            <div class="card">
                <div class="card-hd"><h3><i class="fas fa-circle-half-stroke"></i> Status</h3></div>
                <div style="padding:1.4rem;text-align:center">
                    <span class="{{ $discount->status_badge_class }}" style="font-size:.85rem;padding:.5rem 1.25rem">
                        <i class="fas fa-circle" style="font-size:.5rem"></i> {{ $discount->status_label }}
                    </span>
                    <div style="margin-top:1rem;font-size:.8rem;color:var(--muted);line-height:1.6">
                        @if($discount->is_active && !$discount->is_expired)
                        This discount is <strong>active</strong> and available for use in POS and Online Ordering.
                        @elseif($discount->is_expired)
                        This discount has expired and cannot be applied to new orders.
                        @else
                        This discount is <strong>inactive</strong> and not available for selection.
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-hd"><h3><i class="fas fa-clock-rotate-left"></i> Record Details</h3></div>
                <div style="padding:1.25rem 1.4rem">
                    <div class="meta-info">
                        <span><i class="fas fa-calendar-plus" style="color:var(--primary)"></i> Created: {{ $discount->created_at->format('M d, Y h:i A') }}</span>
                        <span><i class="fas fa-calendar-check" style="color:var(--accent)"></i> Last Updated: {{ $discount->updated_at->format('M d, Y h:i A') }}</span>
                        <span><i class="fas fa-hashtag" style="color:var(--muted)"></i> ID: #{{ $discount->id }}</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:.65rem">
                <a href="{{ route('discounts.edit', $discount) }}" class="btn btn-primary" style="justify-content:center"><i class="fas fa-pen"></i> Edit This Discount</a>
                <form method="POST" action="{{ route('discounts.toggle-status', $discount) }}"
                      onsubmit="return confirm('{{ $discount->is_active ? 'Deactivate' : 'Activate' }} discount \"{{ addslashes($discount->discount_name) }}\"?')">
                    @csrf @method('PUT')
                    <button type="submit" style="width:100%;justify-content:center" class="btn {{ $discount->is_active ? 'btn-amber' : 'btn-green' }}">
                        <i class="fas {{ $discount->is_active ? 'fa-circle-pause' : 'fa-circle-play' }}"></i>
                        {{ $discount->is_active ? 'Deactivate Discount' : 'Activate Discount' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
