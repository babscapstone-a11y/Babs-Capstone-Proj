@extends('layouts.admin')
@section('title', 'Create Discount')
@section('page-title', 'Create Discount')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="breadcrumb-sep">/</span>
    <a href="{{ route('discounts.index') }}">Discounts</a>
    <span class="breadcrumb-sep">/</span> Create
@endsection

@section('styles')
<style>
.disc-page{max-width:1100px;margin:0 auto}
.disc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;flex-wrap:wrap}
.disc-title{font-size:1.4rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.disc-title i{color:var(--primary)}
.form-grid{display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start}
@media(max-width:900px){.form-grid{grid-template-columns:1fr}}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.1rem}
.card-hd{padding:.9rem 1.4rem;border-bottom:1px solid var(--border);background:#FAFBFC;display:flex;align-items:center;gap:.5rem}
.card-hd h3{font-size:.9rem;font-weight:700;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.card-hd h3 i{color:var(--primary)}
.card-body{padding:1.4rem}
.field{margin-bottom:1.15rem}
.field label{display:block;font-size:.82rem;font-weight:600;color:var(--dark);margin-bottom:.38rem}
.field label .req{color:var(--primary);margin-left:.15rem}
.field input[type=text],.field input[type=number],.field input[type=date],.field select,.field textarea{width:100%;padding:.62rem .95rem;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(220,38,38,.08)}
.field .help{font-size:.74rem;color:var(--muted);margin-top:.3rem}
.err{font-size:.76rem;color:#DC2626;margin-top:.3rem;display:flex;align-items:center;gap:.25rem}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.val-wrap{display:flex;align-items:center;border:1.5px solid var(--border);border-radius:10px;overflow:hidden;transition:border-color .18s;background:#fff}
.val-wrap:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px rgba(220,38,38,.08)}
.val-prefix{padding:.62rem .85rem;background:#F8FAFC;border-right:1.5px solid var(--border);font-size:.85rem;font-weight:700;color:var(--muted);white-space:nowrap;flex-shrink:0}
.val-suffix{padding:.62rem .85rem;background:#F8FAFC;border-left:1.5px solid var(--border);font-size:.85rem;font-weight:700;color:var(--muted);white-space:nowrap;flex-shrink:0}
.val-input{flex:1;border:none!important;border-radius:0!important;box-shadow:none!important;outline:none;padding:.62rem .75rem;font-size:.85rem;font-family:inherit;color:var(--dark);background:transparent}
.val-input:focus{box-shadow:none!important}
.type-radio-group{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.type-radio{position:relative}
.type-radio input{position:absolute;opacity:0;width:0;height:0}
.type-radio-label{display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:all .18s;background:#fff}
.type-radio-label:hover{border-color:var(--primary);background:#FEF2F2}
.type-radio input:checked + .type-radio-label{border-color:var(--primary);background:#FEF2F2}
.type-radio-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
.tr-pct{background:#EFF6FF;color:#1D4ED8}.tr-fix{background:#F5F3FF;color:#7C3AED}
.type-radio-text .t-label{font-size:.85rem;font-weight:700;color:var(--dark)}
.type-radio-text .t-sub{font-size:.73rem;color:var(--muted);margin-top:.1rem}
.status-toggle{display:flex;gap:.75rem}
.status-btn{flex:1;padding:.65rem;border-radius:10px;border:2px solid var(--border);background:#fff;cursor:pointer;font-size:.84rem;font-weight:600;font-family:inherit;text-align:center;transition:all .18s}
.status-btn.active-s{border-color:#16A34A;background:#F0FDF4;color:#15803D}
.status-btn.inactive-s{border-color:#6B7280;background:#F3F4F6;color:#6B7280}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.58rem 1.15rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.cond-section{display:none}
.cond-section.visible{display:block}
.preview-card{background:linear-gradient(135deg,var(--dark),#1F2937);border-radius:14px;padding:1.25rem;color:#fff;margin-bottom:1rem}
.preview-card .p-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.5);margin-bottom:.75rem}
.preview-card .p-name{font-size:1rem;font-weight:800;margin-bottom:.35rem}
.preview-card .p-value{font-size:1.65rem;font-weight:900;color:var(--accent);line-height:1}
.preview-card .p-type{font-size:.73rem;color:rgba(255,255,255,.55);margin-top:.15rem}
.preview-card .p-elig{margin-top:.65rem;padding-top:.65rem;border-top:1px solid rgba(255,255,255,.1);font-size:.79rem;color:rgba(255,255,255,.7)}
.future-notice{background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:12px;padding:.9rem 1.1rem;font-size:.8rem;color:#1D4ED8;line-height:1.6}
.future-notice i{color:#60A5FA}
</style>
@endsection

@section('content')
<div class="disc-page">

    <div class="disc-header">
        <div>
            <div class="disc-title"><i class="fas fa-plus-circle"></i> Create New Discount</div>
            <div style="font-size:.83rem;color:var(--muted);margin-top:.25rem">Define a new discount rule for the POS and Online Ordering modules</div>
        </div>
        <a href="{{ route('discounts.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('discounts.store') }}" id="discountForm">
        @csrf
        <div class="form-grid">

            {{-- Left Column --}}
            <div>
                {{-- Basic Information --}}
                <div class="card">
                    <div class="card-hd"><h3><i class="fas fa-info-circle"></i> Basic Information</h3></div>
                    <div class="card-body">
                        <div class="field">
                            <label>Discount Name <span class="req">*</span></label>
                            <input type="text" name="discount_name" value="{{ old('discount_name') }}" placeholder="e.g. Senior Citizen Discount" required>
                            @error('discount_name')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label>Discount Type <span class="req">*</span></label>
                            <div class="type-radio-group">
                                <div class="type-radio">
                                    <input type="radio" name="discount_type" id="type_pct" value="percentage" {{ old('discount_type','percentage')==='percentage' ? 'checked' : '' }} onchange="updateTypeUI()">
                                    <label class="type-radio-label" for="type_pct">
                                        <div class="type-radio-icon tr-pct"><i class="fas fa-percent"></i></div>
                                        <div class="type-radio-text"><div class="t-label">Percentage</div><div class="t-sub">e.g. 20% off</div></div>
                                    </label>
                                </div>
                                <div class="type-radio">
                                    <input type="radio" name="discount_type" id="type_fix" value="fixed" {{ old('discount_type')==='fixed' ? 'checked' : '' }} onchange="updateTypeUI()">
                                    <label class="type-radio-label" for="type_fix">
                                        <div class="type-radio-icon tr-fix"><i class="fas fa-peso-sign"></i></div>
                                        <div class="type-radio-text"><div class="t-label">Fixed Amount</div><div class="t-sub">e.g. ₱100 off</div></div>
                                    </label>
                                </div>
                            </div>
                            @error('discount_type')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label id="valueLabel">Discount Percentage <span class="req">*</span></label>
                            <div class="val-wrap">
                                <div class="val-prefix" id="valPrefix" style="display:none">₱</div>
                                <input type="number" name="discount_value" id="discountValue" class="val-input" value="{{ old('discount_value') }}" step="0.01" min="0.01" placeholder="0" required oninput="updatePreview()">
                                <div class="val-suffix" id="valSuffix">%</div>
                            </div>
                            <div class="help" id="valueHelp">Enter a value between 1 and 100</div>
                            @error('discount_value')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label>Eligibility Condition <span class="req">*</span></label>
                            <select name="eligibility_type" id="eligibilityType" required onchange="updateConditions(); updatePreview()">
                                <option value="">Select eligibility…</option>
                                @foreach($eligibility as $key => $label)
                                <option value="{{ $key }}" {{ old('eligibility_type')===$key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('eligibility_type')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label>Description <span style="font-size:.72rem;color:var(--muted);font-weight:500">(Optional)</span></label>
                            <textarea name="description" rows="3" placeholder="Describe the eligibility requirements or conditions…" style="resize:vertical">{{ old('description') }}</textarea>
                            @error('description')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Conditions --}}
                <div class="card">
                    <div class="card-hd"><h3><i class="fas fa-sliders"></i> Conditions</h3></div>
                    <div class="card-body">
                        {{-- Min Purchase --}}
                        <div class="cond-section" id="condMinPurchase">
                            <div class="field">
                                <label>Minimum Purchase Amount</label>
                                <div class="val-wrap">
                                    <div class="val-prefix">₱</div>
                                    <input type="number" name="minimum_purchase" class="val-input" value="{{ old('minimum_purchase') }}" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="help">Leave empty if no minimum is required</div>
                                @error('minimum_purchase')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Max Discount --}}
                        <div class="field">
                            <label>Maximum Discount Cap <span style="font-size:.72rem;color:var(--muted);font-weight:500">(Optional)</span></label>
                            <div class="val-wrap">
                                <div class="val-prefix">₱</div>
                                <input type="number" name="maximum_discount" class="val-input" value="{{ old('maximum_discount') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="help">Maximum amount this discount can reduce (leave empty for no cap)</div>
                            @error('maximum_discount')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                        </div>

                        {{-- Date Range --}}
                        <div class="cond-section" id="condDateRange">
                            <div class="field-grid">
                                <div class="field">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" value="{{ old('start_date') }}">
                                    @error('start_date')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                                </div>
                                <div class="field">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')<div class="err"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <p style="font-size:.78rem;color:var(--muted);margin:0" id="condNotice">Select an eligibility type above to configure conditions.</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:.75rem;justify-content:flex-end">
                    <a href="{{ route('discounts.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Create Discount</button>
                </div>
            </div>

            {{-- Right Column --}}
            <div>
                {{-- Status --}}
                <div class="card">
                    <div class="card-hd"><h3><i class="fas fa-toggle-on"></i> Status</h3></div>
                    <div class="card-body" style="padding-bottom:1.25rem">
                        <div class="status-toggle">
                            <input type="radio" name="status" id="st_active"   value="active"   {{ old('status','active')==='active'   ? 'checked':'' }} style="display:none" onchange="updateStatusUI()">
                            <input type="radio" name="status" id="st_inactive" value="inactive" {{ old('status')==='inactive' ? 'checked':'' }} style="display:none" onchange="updateStatusUI()">
                            <label for="st_active"   class="status-btn" id="stBtnActive"><i class="fas fa-circle-play"></i> Active</label>
                            <label for="st_inactive" class="status-btn" id="stBtnInactive"><i class="fas fa-circle-pause"></i> Inactive</label>
                        </div>
                        <div style="margin-top:.85rem;font-size:.78rem;color:var(--muted);line-height:1.6" id="statusNote">
                            Active discounts are available in the POS and Online Ordering modules.
                        </div>
                        @error('status')<div class="err" style="margin-top:.5rem"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="card">
                    <div class="card-hd"><h3><i class="fas fa-eye"></i> Preview</h3></div>
                    <div class="card-body">
                        <div class="preview-card">
                            <div class="p-label">Discount Rule Preview</div>
                            <div class="p-name" id="prevName">—</div>
                            <div class="p-value" id="prevValue">—</div>
                            <div class="p-type" id="prevType">—</div>
                            <div class="p-elig" id="prevElig">—</div>
                        </div>
                        <div style="font-size:.75rem;color:var(--muted);line-height:1.6">
                            Preview updates as you fill the form.
                        </div>
                    </div>
                </div>

                {{-- Future Notice --}}
                <div class="future-notice">
                    <i class="fas fa-circle-info"></i>
                    This discount will become available in the <strong>POS Module</strong> and <strong>Online Ordering Checkout Module</strong> when set to <strong>Active</strong>.
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const eligLabels = @json(\App\Models\Discount::ELIGIBILITY);

function updateTypeUI() {
    const isPct = document.getElementById('type_pct').checked;
    document.getElementById('valueLabel').innerHTML = isPct
        ? 'Discount Percentage <span style="color:#DC2626">*</span>'
        : 'Fixed Discount Amount <span style="color:#DC2626">*</span>';
    document.getElementById('valPrefix').style.display = isPct ? 'none' : '';
    document.getElementById('valSuffix').style.display = isPct ? '' : 'none';
    document.getElementById('valueHelp').textContent = isPct
        ? 'Enter a value between 1 and 100'
        : 'Enter the fixed amount in Philippine Peso';
    const input = document.getElementById('discountValue');
    input.max  = isPct ? '100' : '';
    input.step = isPct ? '1' : '0.01';
    updatePreview();
}

function updateConditions() {
    const elig = document.getElementById('eligibilityType').value;
    const showMin  = ['minimum_purchase','date_range','promotional'].includes(elig);
    const showDate = ['date_range','promotional'].includes(elig);
    const notice   = document.getElementById('condNotice');
    document.getElementById('condMinPurchase').classList.toggle('visible', showMin);
    document.getElementById('condDateRange').classList.toggle('visible', showDate);
    notice.style.display = (showMin || showDate) ? 'none' : '';
}

function updatePreview() {
    const name  = document.querySelector('[name=discount_name]').value || '—';
    const val   = parseFloat(document.getElementById('discountValue').value) || 0;
    const isPct = document.getElementById('type_pct').checked;
    const elig  = document.getElementById('eligibilityType').value;
    const fmtVal = isPct ? val.toFixed(0) + '%' : '₱' + val.toFixed(2);
    document.getElementById('prevName').textContent  = name;
    document.getElementById('prevValue').textContent = fmtVal;
    document.getElementById('prevType').textContent  = isPct ? 'Percentage Discount' : 'Fixed Amount Discount';
    document.getElementById('prevElig').textContent  = elig ? 'Eligibility: ' + (eligLabels[elig] || elig) : 'No eligibility selected';
}

function updateStatusUI() {
    const isActive = document.getElementById('st_active').checked;
    document.getElementById('stBtnActive').className   = 'status-btn' + (isActive ? ' active-s' : '');
    document.getElementById('stBtnInactive').className = 'status-btn' + (!isActive ? ' inactive-s' : '');
    document.getElementById('statusNote').textContent  = isActive
        ? 'Active discounts are available in the POS and Online Ordering modules.'
        : 'Inactive discounts are stored but not available for selection.';
}

// Name input live preview
document.querySelector('[name=discount_name]').addEventListener('input', updatePreview);

// Init on load
window.addEventListener('DOMContentLoaded', function () {
    updateTypeUI();
    updateConditions();
    updateStatusUI();
    updatePreview();
});
</script>
@endsection
