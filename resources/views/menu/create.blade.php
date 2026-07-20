@extends('layouts.admin')

@section('title', 'Add Menu Item')
@section('page-title', 'Menu Catalog')

@section('breadcrumb')
    <a href="{{ route('menu.index') }}" style="color:var(--primary);text-decoration:none">Menu Catalog</a>
    <i class="fas fa-chevron-right" style="font-size:.65rem;margin:0 .35rem;color:var(--muted)"></i>
    <span>Add Item</span>
@endsection

@section('styles')
<style>
    .form-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(17,24,39,0.06);
        overflow: hidden;
    }
    .form-card-header {
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .75rem;
    }
    .form-card-header h2 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0; }
    .form-card-body { padding: 1.75rem 1.5rem; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; }
    .form-group { display: flex; flex-direction: column; gap: .45rem; }
    .form-group label { font-size: .78rem; font-weight: 700; color: var(--dark); }
    .form-group label span.req { color: var(--primary); margin-left: .15rem; }
    .form-control {
        width: 100%; padding: .62rem .85rem;
        border: 1.5px solid var(--border); border-radius: 10px;
        font-size: .88rem; font-family: inherit; color: var(--dark);
        background: #fff; outline: none;
        transition: border-color .18s, box-shadow .18s;
        box-sizing: border-box;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
    .form-control.is-invalid { border-color: var(--primary); }
    .invalid-feedback { font-size: .75rem; color: var(--primary); font-weight: 500; }
    .form-hint { font-size: .73rem; color: var(--muted); }
    textarea.form-control { resize: vertical; min-height: 90px; }
    .form-divider {
        border: none; border-top: 1.5px solid var(--border);
        margin: 1.75rem 0;
    }
    .section-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--muted);
        margin-bottom: 1.1rem; display: flex; align-items: center; gap: .5rem;
    }
    .section-label::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
    }

    /* Tile pickers */
    .tile-group { display: flex; gap: .65rem; flex-wrap: wrap; }
    .tile-input { display: none; }
    .tile-label {
        flex: 1; min-width: 120px; padding: .8rem 1rem;
        border: 1.5px solid var(--border); border-radius: 12px;
        cursor: pointer; transition: all .18s;
        display: flex; align-items: center; gap: .65rem;
        background: var(--bg);
    }
    .tile-label:hover { border-color: var(--primary); background: rgba(220,38,38,0.04); }
    .tile-input:checked + .tile-label { border-color: var(--primary); background: rgba(220,38,38,0.07); }
    .tile-icon {
        width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem;
    }
    .tile-text { font-size: .85rem; font-weight: 600; color: var(--dark); }
    .tile-sub  { font-size: .7rem; color: var(--muted); margin-top: .1rem; }

    /* Image upload */
    .img-upload-area {
        border: 2px dashed var(--border); border-radius: 14px;
        padding: 1.5rem; text-align: center; cursor: pointer;
        transition: border-color .2s, background .2s;
        background: var(--bg);
    }
    .img-upload-area:hover { border-color: var(--primary); background: rgba(220,38,38,0.03); }
    .img-upload-area input[type=file] { display: none; }
    .img-preview-wrap { display: none; text-align: center; }
    .img-preview-wrap img { max-height: 180px; border-radius: 12px; border: 1.5px solid var(--border); }

    /* RTC section */
    .rtc-card {
        background: rgba(245,158,11,0.05);
        border: 1.5px solid rgba(245,158,11,0.2);
        border-radius: 14px; padding: 1.25rem 1.3rem;
    }
    .rtc-notice {
        background: rgba(37,99,235,0.06);
        border: 1.5px solid rgba(37,99,235,0.15);
        border-radius: 10px; padding: .75rem 1rem;
        font-size: .8rem; color: #1D4ED8; margin-bottom: 1rem;
        display: flex; align-items: flex-start; gap: .5rem;
    }
    #rtcFields { display: none; }

    /* Submit */
    .form-footer {
        padding: 1.2rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
    }
    .btn-cancel {
        padding: .65rem 1.4rem; border: 1.5px solid var(--border);
        border-radius: 10px; background: transparent; font-family: inherit;
        font-size: .88rem; font-weight: 600; cursor: pointer; color: var(--dark);
        text-decoration: none; display: inline-flex; align-items: center; gap: .4rem;
        transition: border-color .18s;
    }
    .btn-cancel:hover { border-color: var(--primary); color: var(--primary); }
    .btn-submit {
        padding: .65rem 1.7rem; background: linear-gradient(90deg,var(--primary),#F97316);
        border: none; border-radius: 10px; color: #fff; font-family: inherit;
        font-size: .88rem; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; gap: .5rem;
        transition: opacity .18s;
    }
    .btn-submit:hover { opacity: .9; }
    .btn-submit:disabled { opacity: .6; cursor: not-allowed; }

    @media (max-width:700px) {
        .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

<form method="POST" action="{{ route('menu.store') }}" enctype="multipart/form-data" id="menuForm">
@csrf

<div class="form-card">
    <div class="form-card-header">
        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,rgba(220,38,38,0.12),rgba(249,115,22,0.08));display:flex;align-items:center;justify-content:center;color:var(--primary)">
            <i class="fas fa-plus"></i>
        </div>
        <h2>Add New Menu Item</h2>
    </div>

    <div class="form-card-body">

        {{-- ── Item Type & Basic Info ─────────────────────────── --}}
        <div class="section-label"><i class="fas fa-tag"></i> Item Type</div>
        <div class="tile-group" style="margin-bottom:1.5rem">
            <input type="radio" name="item_type" id="type_food" value="food" class="tile-input"
                {{ old('item_type', 'food') === 'food' ? 'checked' : '' }}>
            <label for="type_food" class="tile-label">
                <div class="tile-icon" style="background:rgba(37,99,235,0.10);color:#2563EB"><i class="fas fa-bowl-food"></i></div>
                <div>
                    <div class="tile-text">Food</div>
                </div>
            </label>

            <input type="radio" name="item_type" id="type_beverage" value="beverage" class="tile-input"
                {{ old('item_type') === 'beverage' ? 'checked' : '' }}>
            <label for="type_beverage" class="tile-label">
                <div class="tile-icon" style="background:rgba(139,92,246,0.10);color:#7C3AED"><i class="fas fa-glass-water"></i></div>
                <div>
                    <div class="tile-text">Beverage</div>
                </div>
            </label>
        </div>
        @error('item_type')<div class="invalid-feedback" style="margin-top:-.75rem;margin-bottom:.75rem">{{ $message }}</div>@enderror

        {{-- Basic Info --}}
        <div class="section-label"><i class="fas fa-circle-info"></i> Basic Information</div>
        <div class="form-grid-2" style="margin-bottom:1.25rem">
            <div class="form-group">
                <label for="menu_name">Item Name <span class="req">*</span></label>
                <input type="text" id="menu_name" name="menu_name" class="form-control @error('menu_name') is-invalid @enderror"
                    value="{{ old('menu_name') }}" placeholder="e.g. Pork Sisig" required autocomplete="off">
                @error('menu_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="category_id">Category <span class="req">*</span></label>
                <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">— Select Category —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-type="{{ $cat->item_type }}" @selected(old('category_id') == $cat->id)>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group" style="margin-bottom:1.25rem">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                placeholder="Describe this menu item…">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-grid-2" style="margin-bottom:1.5rem">
            <div class="form-group">
                <label for="price">Selling Price (₱) <span class="req">*</span></label>
                <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                    value="{{ old('price') }}" min="0.01" step="0.01" placeholder="0.00" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div></div>
        </div>

        <hr class="form-divider">

        {{-- ── Status ─────────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-toggle-on"></i> Status</div>
        <div class="form-grid-2" style="margin-bottom:1.5rem">
            <div class="form-group">
                <label>Active Status <span class="req">*</span></label>
                <div class="tile-group">
                    <input type="radio" name="is_active" id="active_yes" value="1" class="tile-input"
                        {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                    <label for="active_yes" class="tile-label" style="flex:none;padding:.6rem .9rem">
                        <div class="tile-icon" style="background:rgba(22,163,74,0.10);color:#16A34A;width:28px;height:28px;font-size:.75rem"><i class="fas fa-toggle-on"></i></div>
                        <div class="tile-text" style="font-size:.82rem">Active</div>
                    </label>
                    <input type="radio" name="is_active" id="active_no" value="0" class="tile-input"
                        {{ old('is_active') === '0' ? 'checked' : '' }}>
                    <label for="active_no" class="tile-label" style="flex:none;padding:.6rem .9rem">
                        <div class="tile-icon" style="background:rgba(220,38,38,0.10);color:#DC2626;width:28px;height:28px;font-size:.75rem"><i class="fas fa-toggle-off"></i></div>
                        <div class="tile-text" style="font-size:.82rem">Inactive</div>
                    </label>
                </div>
                <div class="form-hint">Inactive items are hidden from POS and ordering page.</div>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div></div>
        </div>

        <hr class="form-divider">

        {{-- ── Item Image ───────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-image"></i> Item Image</div>
        <div style="margin-bottom:1.5rem">
            <div class="img-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                    onchange="previewImage(this)">
                <i class="fas fa-cloud-arrow-up" style="font-size:2rem;color:var(--muted);margin-bottom:.5rem;display:block"></i>
                <div style="font-size:.88rem;font-weight:600;color:var(--dark)">Click to upload image</div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:.25rem">JPEG, PNG, WebP · max 2 MB</div>
            </div>
            <div class="img-preview-wrap" id="previewWrap">
                <img id="previewImg" src="" alt="Preview" style="max-height:180px;border-radius:12px;border:1.5px solid var(--border)">
                <div style="margin-top:.5rem">
                    <button type="button" onclick="clearImage()" style="font-size:.78rem;color:var(--primary);background:none;border:none;cursor:pointer;font-family:inherit">
                        <i class="fas fa-trash"></i> Remove image
                    </button>
                </div>
            </div>
            @error('image')<div class="invalid-feedback" style="margin-top:.4rem">{{ $message }}</div>@enderror
        </div>

    </div>{{-- /form-card-body --}}

    <div class="form-footer">
        <a href="{{ route('menu.index') }}" class="btn-cancel">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-floppy-disk"></i> Save Menu Item
        </button>
    </div>
</div>
</form>

@endsection

@section('scripts')
<script>
/* ── Image preview ─────────────────────────────────────── */
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('uploadArea').style.display = 'none';
            document.getElementById('previewWrap').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('uploadArea').style.display = 'block';
    document.getElementById('previewWrap').style.display = 'none';
}

/* ── Submit spinner ────────────────────────────────────── */
document.getElementById('menuForm').addEventListener('submit', function() {
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
});

/* ── Filter Category options by selected Item Type ────── */
function filterCategoriesByType() {
    var checked = document.querySelector('input[name="item_type"]:checked');
    if (!checked) return;
    var type = checked.value;
    var select = document.getElementById('category_id');
    var currentValue = select.value;
    var currentValid = false;

    select.querySelectorAll('option[data-type]').forEach(function(opt) {
        var matches = opt.dataset.type === type;
        opt.hidden = !matches;
        opt.disabled = !matches;
        if (matches && opt.value === currentValue) currentValid = true;
    });

    if (!currentValid) select.value = '';
}
document.querySelectorAll('input[name="item_type"]').forEach(function(radio) {
    radio.addEventListener('change', filterCategoriesByType);
});
filterCategoriesByType();
</script>
@endsection
