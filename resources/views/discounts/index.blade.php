@extends('layouts.admin')
@section('title', 'Discount Management')
@section('page-title', 'Discount Management')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span> Discounts
@endsection

@section('styles')
<style>
.disc-page{max-width:1400px;margin:0 auto}
.disc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.disc-title{font-size:1.45rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.disc-title i{color:var(--primary)}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:1.1rem;margin-bottom:1.75rem}
.stat-card{background:#fff;border-radius:16px;padding:1.2rem 1.4rem;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem}
.si{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.si-green{background:#F0FDF4;color:#16A34A}.si-gray{background:#F3F4F6;color:#6B7280}.si-amber{background:#FFFBEB;color:#D97706}.si-red{background:#FEF2F2;color:#DC2626}
.sv{font-size:1.7rem;font-weight:900;color:var(--dark);line-height:1}
.sl{font-size:.7rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
.tbl-wrap{overflow-x:auto}
.disc-table{width:100%;border-collapse:collapse;font-size:.83rem}
.disc-table th{padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:#F8FAFC;border-bottom:1px solid var(--border);white-space:nowrap;cursor:pointer;user-select:none}
.disc-table th:hover{color:var(--primary)}
.disc-table td{padding:.8rem 1rem;border-bottom:1px solid #F3F4F6;color:var(--dark);vertical-align:middle}
.disc-table tr:last-child td{border-bottom:none}
.disc-table tr:hover td{background:#FAFAFA}
.filter-row{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;padding:1rem 1.25rem;border-bottom:1px solid var(--border)}
.search-box{position:relative;flex:1;min-width:200px}
.search-box i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
.search-input{width:100%;padding:.55rem .9rem .55rem 2.25rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:#fff}
.search-input:focus{border-color:var(--primary)}
.flt-select{padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;min-width:140px}
.btn{display:inline-flex;align-items:center;gap:.42rem;padding:.52rem 1.05rem;border-radius:10px;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-green{background:#16A34A;color:#fff}.btn-green:hover{background:#15803D}
.btn-amber{background:#D97706;color:#fff}.btn-amber:hover{background:#B45309}
.btn-sm{padding:.35rem .7rem;font-size:.75rem}
.badge-disc-active{background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;gap:.28rem;padding:.2rem .6rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-disc-inactive{background:#F3F4F6;color:#6B7280;display:inline-flex;align-items:center;gap:.28rem;padding:.2rem .6rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-disc-expired{background:#FEE2E2;color:#B91C1C;display:inline-flex;align-items:center;gap:.28rem;padding:.2rem .6rem;border-radius:50px;font-size:.68rem;font-weight:700;text-transform:uppercase}
.badge-type-pct{background:#EFF6FF;color:#1D4ED8;padding:.18rem .55rem;border-radius:6px;font-size:.66rem;font-weight:700;display:inline-block}
.badge-type-fix{background:#F5F3FF;color:#7C3AED;padding:.18rem .55rem;border-radius:6px;font-size:.66rem;font-weight:700;display:inline-block}
.elig-badge{padding:.18rem .55rem;border-radius:6px;font-size:.66rem;font-weight:700;display:inline-block}
.elig-senior{background:#EFF6FF;color:#1D4ED8}.elig-pwd{background:#F5F3FF;color:#7C3AED}.elig-promo{background:#FEF3C7;color:#92400E}
.elig-employee{background:#F0FDF4;color:#15803D}.elig-min{background:#ECFEFF;color:#0E7490}.elig-date{background:#FFF7ED;color:#C2410C}.elig-all{background:#F3F4F6;color:#6B7280}
.value-pct{font-size:1rem;font-weight:800;color:#16A34A}
.value-fix{font-size:1rem;font-weight:800;color:#7C3AED}
.sort-icon{font-size:.65rem;margin-left:.25rem;opacity:.5}
.sort-icon.active{opacity:1;color:var(--primary)}
.empty-msg{text-align:center;color:var(--muted);padding:3rem;font-size:.84rem}
.card-hd{padding:.9rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:#FAFBFC}
.card-hd h3{font-size:.9rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.card-hd h3 i{color:var(--primary)}
</style>
@endsection

@section('content')
<div class="disc-page">

    <div class="disc-header">
        <div>
            <div class="disc-title"><i class="fas fa-tag"></i> Discount Management</div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.25rem">Create and manage discount rules for POS and Online Ordering</div>
        </div>
        <a href="{{ route('discounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Discount
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card"><div class="si si-green"><i class="fas fa-circle-check"></i></div><div><div class="sv">{{ $totalActive }}</div><div class="sl">Active Discounts</div></div></div>
        <div class="stat-card"><div class="si si-gray"><i class="fas fa-circle-pause"></i></div><div><div class="sv">{{ $totalInactive }}</div><div class="sl">Inactive Discounts</div></div></div>
        <div class="stat-card"><div class="si si-amber"><i class="fas fa-percent"></i></div><div><div class="sv">{{ $totalPromo }}</div><div class="sl">Promotional</div></div></div>
        <div class="stat-card"><div class="si si-red"><i class="fas fa-clock"></i></div><div><div class="sv">{{ $totalExpired }}</div><div class="sl">Expired (Active)</div></div></div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="card-hd">
            <h3><i class="fas fa-list"></i> All Discount Rules</h3>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('discounts.index') }}" class="filter-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search discount name…" class="search-input">
            </div>
            <select name="type" class="flt-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="percentage" {{ request('type')==='percentage' ? 'selected' : '' }}>Percentage</option>
                <option value="fixed"      {{ request('type')==='fixed'      ? 'selected' : '' }}>Fixed Amount</option>
            </select>
            <select name="eligibility" class="flt-select" onchange="this.form.submit()">
                <option value="">All Eligibility</option>
                @foreach(\App\Models\Discount::ELIGIBILITY as $key => $label)
                <option value="{{ $key }}" {{ request('eligibility')===$key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="flt-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active"   {{ request('status')==='active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            @if(request()->hasAny(['q','type','eligibility','status']))
            <a href="{{ route('discounts.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
            @endif
        </form>

        <div class="tbl-wrap">
            <table class="disc-table">
                <thead>
                    <tr>
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'discount_name','dir'=>request('sort')==='discount_name'&&request('dir')==='asc'?'desc':'asc']) }}" style="color:inherit;text-decoration:none">Discount Name <i class="fas fa-sort sort-icon {{ request('sort')==='discount_name' ? 'active' : '' }}"></i></a></th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Eligibility</th>
                        <th>Validity Period</th>
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'status','dir'=>request('sort')==='status'&&request('dir')==='asc'?'desc':'asc']) }}" style="color:inherit;text-decoration:none">Status <i class="fas fa-sort sort-icon {{ request('sort')==='status' ? 'active' : '' }}"></i></a></th>
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'updated_at','dir'=>request('sort')==='updated_at'&&request('dir')==='asc'?'desc':'asc']) }}" style="color:inherit;text-decoration:none">Last Updated <i class="fas fa-sort sort-icon {{ request('sort')==='updated_at' ? 'active' : '' }}"></i></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $d)
                    @php
                        $eligClass = match($d->eligibility_type) {
                            'senior_citizen'   => 'elig-senior',
                            'pwd'              => 'elig-pwd',
                            'promotional'      => 'elig-promo',
                            'employee'         => 'elig-employee',
                            'minimum_purchase' => 'elig-min',
                            'date_range'       => 'elig-date',
                            default            => 'elig-all',
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('discounts.show', $d) }}" style="font-weight:700;color:var(--dark)">{{ $d->discount_name }}</a>
                            @if($d->description)<div style="font-size:.74rem;color:var(--muted);margin-top:.15rem">{{ Str::limit($d->description,55) }}</div>@endif
                        </td>
                        <td><span class="{{ $d->discount_type==='percentage' ? 'badge-type-pct' : 'badge-type-fix' }}">{{ $d->discount_type==='percentage' ? 'Percentage' : 'Fixed' }}</span></td>
                        <td><span class="{{ $d->discount_type==='percentage' ? 'value-pct' : 'value-fix' }}">{{ $d->formatted_value }}</span></td>
                        <td><span class="elig-badge {{ $eligClass }}">{{ $d->eligibility_label }}</span></td>
                        <td style="font-size:.79rem;color:var(--muted)">{{ $d->validity_period }}</td>
                        <td><span class="{{ $d->status_badge_class }}"><i class="fas fa-circle" style="font-size:.4rem"></i> {{ $d->status_label }}</span></td>
                        <td style="font-size:.78rem;color:var(--muted)">{{ $d->updated_at->diffForHumans() }}</td>
                        <td>
                            <div style="display:flex;gap:.35rem;flex-wrap:wrap">
                                <a href="{{ route('discounts.show', $d) }}" class="btn btn-outline btn-sm" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('discounts.edit', $d) }}" class="btn btn-outline btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('discounts.toggle-status', $d) }}"
                                      onsubmit="return confirm('{{ $d->is_active ? 'Deactivate' : 'Activate' }} discount \"{{ addslashes($d->discount_name) }}\"?')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-sm {{ $d->is_active ? 'btn-amber' : 'btn-green' }}" title="{{ $d->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $d->is_active ? 'fa-circle-pause' : 'fa-circle-play' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="empty-msg">
                        <i class="fas fa-tag" style="font-size:1.6rem;display:block;margin-bottom:.5rem;opacity:.35"></i>
                        No discounts found.
                        <a href="{{ route('discounts.create') }}" style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.75rem;color:var(--primary);font-weight:600;font-size:.82rem"><i class="fas fa-plus"></i> Create First Discount</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($discounts->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">{{ $discounts->links() }}</div>
        @endif
    </div>
</div>
@endsection
