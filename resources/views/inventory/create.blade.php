@extends('layouts.admin')
@section('title', 'Add Inventory Item')

@section('styles')
<style>
.inv-page{padding:2rem;max-width:700px;margin:0 auto}
.page-header{display:flex;align-items:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1.75rem}
.section-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border)}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.65rem .95rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:#fff;transition:border-color .18s;box-sizing:border-box}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary)}
.field .help{font-size:.73rem;color:var(--muted);margin-top:.3rem}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.btn-row{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.2rem;border-radius:10px;font-size:.84rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.error-msg{background:#FEF2F2;border:1.5px solid #FECACA;color:#B91C1C;border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1rem}

.type-toggle{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1.5rem}
.type-option{display:flex;align-items:center;gap:.6rem;padding:.85rem 1rem;border:1.5px solid var(--border);border-radius:12px;cursor:pointer;transition:all .18s}
.type-option i{font-size:1.1rem;color:var(--muted)}
.type-option .label{font-weight:700;font-size:.87rem;color:var(--dark)}
.type-option input[type=radio]{accent-color:var(--primary)}
.type-option.active{border-color:var(--primary);background:rgba(220,38,38,0.04)}
.type-option.active i{color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <a href="{{ $type === 'rtc' ? route('inventory.rtc') : route('inventory.beverages') }}" class="btn btn-outline" style="padding:.45rem .85rem;font-size:.8rem"><i class="fas fa-arrow-left"></i></a>
        <div>
            <div class="page-title"><i class="fas fa-plus"></i> Add Inventory Item</div>
            <div class="page-sub">Add a new raw meat (RTC) or beverage item to inventory</div>
        </div>
    </div>

    @if($errors->any())
    <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('inventory.store') }}" id="itemForm">
            @csrf

            <div class="section-label">Item Type</div>
            <div class="type-toggle">
                <label class="type-option {{ old('item_type', $type) === 'rtc' ? 'active' : '' }}" data-type="rtc">
                    <input type="radio" name="item_type" value="rtc" {{ old('item_type', $type) === 'rtc' ? 'checked' : '' }}>
                    <i class="fas fa-drumstick-bite"></i>
                    <span class="label">RTC Raw Meat</span>
                </label>
                <label class="type-option {{ old('item_type', $type) === 'beverage' ? 'active' : '' }}" data-type="beverage">
                    <input type="radio" name="item_type" value="beverage" {{ old('item_type', $type) === 'beverage' ? 'checked' : '' }}>
                    <i class="fas fa-bottle-water"></i>
                    <span class="label">Beverage</span>
                </label>
            </div>

            <div class="section-label">Item Details</div>
            <div class="field-grid">
                <div class="field">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" required value="{{ old('item_name') }}" placeholder="e.g. Pork Belly">
                </div>
                <div class="field">
                    <label>Category</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Pork, Chicken…">
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label>Unit *</label>
                    <input type="text" name="unit" required value="{{ old('unit') }}" placeholder="e.g. kg, pcs, liters">
                </div>
                <div class="field">
                    <label>Supplier</label>
                    <input type="text" name="supplier" value="{{ old('supplier') }}" placeholder="Supplier name…">
                </div>
            </div>

            <div class="section-label" style="margin-top:1.5rem">Opening Stock</div>
            <div class="field-grid">
                <div class="field">
                    <label>Opening Quantity *</label>
                    <input type="number" name="quantity" step="0.01" min="0" required value="{{ old('quantity', 0) }}">
                </div>
                <div class="field">
                    <label>Cost Price</label>
                    <input type="number" name="cost_price" step="0.01" min="0" value="{{ old('cost_price') }}" placeholder="0.00">
                </div>
            </div>

            <div class="section-label" style="margin-top:1.5rem">Stock Thresholds</div>
            <div class="field-grid">
                <div class="field">
                    <label>Reorder Level *</label>
                    <input type="number" name="reorder_level" step="0.01" min="0" required value="{{ old('reorder_level') }}">
                    <div class="help">Alert triggers when qty drops to/below this</div>
                </div>
                <div class="field">
                    <label>Min Stock Level *</label>
                    <input type="number" name="min_stock_level" step="0.01" min="0" required value="{{ old('min_stock_level') }}">
                    <div class="help">Minimum safe quantity to maintain</div>
                </div>
            </div>

            <div id="rtcSection">
                <div class="section-label" style="margin-top:1.5rem">RTC Conversion Rules (Optional)</div>
                <div class="field-grid">
                    <div class="field">
                        <label>Portion Size</label>
                        <input type="number" name="portion_size" step="0.001" min="0.001" value="{{ old('portion_size') }}">
                        <div class="help">Raw quantity used per RTC serving — can be set later</div>
                    </div>
                    <div class="field">
                        <label>Portion Unit</label>
                        <input type="text" name="portion_unit" value="{{ old('portion_unit') }}" placeholder="e.g. kg">
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <a href="{{ $type === 'rtc' ? route('inventory.rtc') : route('inventory.beverages') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
    var options = document.querySelectorAll('.type-option');
    var rtcSection = document.getElementById('rtcSection');

    function sync() {
        var checked = document.querySelector('input[name="item_type"]:checked');
        options.forEach(function(opt){
            opt.classList.toggle('active', opt.dataset.type === checked.value);
        });
        rtcSection.style.display = checked.value === 'rtc' ? '' : 'none';
    }

    options.forEach(function(opt){
        opt.addEventListener('click', function(){
            opt.querySelector('input[type=radio]').checked = true;
            sync();
        });
    });

    sync();
})();
</script>
@endsection
