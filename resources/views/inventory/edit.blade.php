@extends('layouts.admin')
@section('title', 'Edit Inventory Item')

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
.field input[readonly]{background:#F8FAFC;color:var(--muted);cursor:default}
.field .help{font-size:.73rem;color:var(--muted);margin-top:.3rem}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-available{background:#DCFCE7;color:#15803D}.badge-low{background:#FEF3C7;color:#B45309}.badge-out{background:#FEE2E2;color:#B91C1C}
.badge-rtc{background:#EFF6FF;color:#1D4ED8}.badge-bev{background:#F5F3FF;color:#6D28D9}
.item-info{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1.75rem;padding:1rem 1.2rem;background:#F8FAFC;border-radius:12px;border:1px solid var(--border)}
.item-info .name{font-size:1.05rem;font-weight:800;color:var(--dark)}
.item-info .qty{font-size:.85rem;color:var(--muted)}
.btn-row{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.2rem;border-radius:10px;font-size:.84rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:#fff;border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.error-msg{background:#FEF2F2;border:1.5px solid #FECACA;color:#B91C1C;border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1rem}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <a href="{{ $item->item_type === 'rtc' ? route('inventory.rtc') : route('inventory.beverages') }}" class="btn btn-outline" style="padding:.45rem .85rem;font-size:.8rem"><i class="fas fa-arrow-left"></i></a>
        <div>
            <div class="page-title"><i class="fas fa-sliders"></i> Edit Inventory Item</div>
            <div class="page-sub">Update thresholds, portion rules, and supplier details</div>
        </div>
    </div>

    @if($errors->any())
    <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="item-info">
        <span class="badge {{ $item->item_type === 'rtc' ? 'badge-rtc' : 'badge-bev' }}">{{ strtoupper($item->item_type) }}</span>
        <span class="name">{{ $item->item_name }}</span>
        <span class="qty">Current: <strong>{{ number_format($item->quantity,2) }} {{ $item->unit }}</strong></span>
        @php $s=$item->stock_status; @endphp
        <span class="badge {{ $s==='available'?'badge-available':($s==='low_stock'?'badge-low':'badge-out') }}">{{ $item->stock_status_label }}</span>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('inventory.update', $item) }}">
            @csrf
            @method('PUT')

            <div class="section-label">Item Details</div>
            <div class="field-grid">
                <div class="field">
                    <label>Item Name</label>
                    <input type="text" value="{{ $item->item_name }}" readonly>
                </div>
                <div class="field">
                    <label>Category</label>
                    <input type="text" name="category" value="{{ old('category', $item->category) }}" placeholder="e.g. Pork, Chicken…">
                </div>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label>Unit *</label>
                    <input type="text" name="unit" required value="{{ old('unit', $item->unit) }}" placeholder="e.g. kg, pcs, liters">
                </div>
                <div class="field">
                    <label>Supplier</label>
                    <input type="text" name="supplier" value="{{ old('supplier', $item->supplier) }}" placeholder="Supplier name…">
                </div>
            </div>

            <div class="section-label" style="margin-top:1.5rem">Stock Thresholds</div>
            <div class="field-grid">
                <div class="field">
                    <label>Reorder Level *</label>
                    <input type="number" name="reorder_level" step="0.01" min="0" required value="{{ old('reorder_level', $item->reorder_level) }}">
                    <div class="help">Alert triggers when qty drops to/below this</div>
                </div>
                <div class="field">
                    <label>Min Stock Level *</label>
                    <input type="number" name="min_stock_level" step="0.01" min="0" required value="{{ old('min_stock_level', $item->min_stock_level) }}">
                    <div class="help">Minimum safe quantity to maintain</div>
                </div>
            </div>

            @if($item->item_type === 'rtc')
            <div class="section-label" style="margin-top:1.5rem">RTC Conversion Rules</div>
            <div class="field-grid">
                <div class="field">
                    <label>Portion Size *</label>
                    <input type="number" name="portion_size" step="0.001" min="0.001" required value="{{ old('portion_size', $item->portion_size) }}">
                    <div class="help">Raw {{ $item->unit }} used per RTC serving</div>
                </div>
                <div class="field">
                    <label>Portion Unit</label>
                    <input type="text" name="portion_unit" value="{{ old('portion_unit', $item->portion_unit ?? $item->unit) }}" placeholder="e.g. kg">
                    <div class="help">Unit for portion measurement</div>
                </div>
            </div>
            @endif

            <div class="btn-row">
                <a href="{{ $item->item_type === 'rtc' ? route('inventory.rtc') : route('inventory.beverages') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
