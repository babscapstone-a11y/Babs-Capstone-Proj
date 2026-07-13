@extends('layouts.admin')

@section('title', 'Edit – ' . $menu->menu_name)
@section('page-title', 'Menu Catalog')

@section('breadcrumb')
    <a href="{{ route('menu.index') }}" style="color:var(--primary);text-decoration:none">Menu Catalog</a>
    <i class="fas fa-chevron-right" style="font-size:.65rem;margin:0 .35rem;color:var(--muted)"></i>
    <a href="{{ route('menu.show', $menu) }}" style="color:var(--primary);text-decoration:none">{{ Str::limit($menu->menu_name, 30) }}</a>
    <i class="fas fa-chevron-right" style="font-size:.65rem;margin:0 .35rem;color:var(--muted)"></i>
    <span>Edit</span>
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
        display: flex; align-items: center; justify-content: space-between;
    }
    .form-card-header h2 { font-size: 1rem; font-weight: 700; color: var(--dark); margin: 0; }
    .form-card-body { padding: 1.75rem 1.5rem; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
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
    .form-divider { border: none; border-top: 1.5px solid var(--border); margin: 1.75rem 0; }
    .section-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--muted);
        margin-bottom: 1.1rem; display: flex; align-items: center; gap: .5rem;
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

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

    /* Image */
    .current-image-wrap {
        display: flex; align-items: center; gap: 1rem;
        background: var(--bg); border: 1.5px solid var(--border);
        border-radius: 12px; padding: .85rem 1rem; margin-bottom: .85rem;
    }
    .current-image-wrap img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; }
    .img-upload-area {
        border: 2px dashed var(--border); border-radius: 14px;
        padding: 1.2rem; text-align: center; cursor: pointer;
        transition: border-color .2s;
        background: var(--bg);
    }
    .img-upload-area:hover { border-color: var(--primary); }
    .img-upload-area input[type=file] { display: none; }
    .img-preview-wrap { display: none; text-align: center; margin-top: .75rem; }
    .img-preview-wrap img { max-height: 160px; border-radius: 12px; border: 1.5px solid var(--border); }

    /* RTC */
    .rtc-card {
        background: rgba(245,158,11,0.05);
        border: 1.5px solid rgba(245,158,11,0.2);
        border-radius: 14px; padding: 1.25rem 1.3rem;
    }
    #rtcFields { display: none; }

    /* Footer */
    .form-footer {
        padding: 1.2rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        flex-wrap: wrap;
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
    }
    .btn-submit:hover { opacity: .9; }
    .btn-submit:disabled { opacity: .6; cursor: not-allowed; }

    /* Confirm dialog overlay */
    #confirmOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.5); z-index: 1000;
        align-items: center; justify-content: center;
    }

    @media (max-width:700px) { .form-grid-2 { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<form method="POST" action="{{ route('menu.update', $menu) }}" enctype="multipart/form-data" id="menuForm">
@csrf @method('PUT')

<div class="form-card">
    <div class="form-card-header">
        <div style="display:flex;align-items:center;gap:.75rem">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(249,115,22,0.08));display:flex;align-items:center;justify-content:center;color:#D97706">
                <i class="fas fa-pen"></i>
            </div>
            <h2>Edit: {{ $menu->menu_name }}</h2>
        </div>
        <div style="display:flex;gap:.5rem">
            @if($menu->is_active)
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(22,163,74,0.10);border:1px solid rgba(22,163,74,0.25);color:#15803D;border-radius:50px;font-size:.72rem;font-weight:700;padding:.22rem .65rem">
                    <i class="fas fa-circle" style="font-size:.4rem"></i> Active
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(220,38,38,0.10);border:1px solid rgba(220,38,38,0.25);color:#B91C1C;border-radius:50px;font-size:.72rem;font-weight:700;padding:.22rem .65rem">
                    <i class="fas fa-circle" style="font-size:.4rem"></i> Inactive
                </span>
            @endif
        </div>
    </div>

    <div class="form-card-body">

        {{-- Item Type --}}
        <div class="section-label"><i class="fas fa-tag"></i> Item Type</div>
        <div class="tile-group" style="margin-bottom:1.5rem">
            <input type="radio" name="item_type" id="type_food" value="food" class="tile-input"
                {{ old('item_type', $menu->item_type) === 'food' ? 'checked' : '' }}>
            <label for="type_food" class="tile-label">
                <div class="tile-icon" style="background:rgba(37,99,235,0.10);color:#2563EB"><i class="fas fa-bowl-food"></i></div>
                <div>
                    <div class="tile-text">Food</div>
                </div>
            </label>
            <input type="radio" name="item_type" id="type_beverage" value="beverage" class="tile-input"
                {{ old('item_type', $menu->item_type) === 'beverage' ? 'checked' : '' }}>
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
                    value="{{ old('menu_name', $menu->menu_name) }}" required>
                @error('menu_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="category_id">Category <span class="req">*</span></label>
                <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">— Select Category —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $menu->category_id) == $cat->id)>{{ $cat->category_name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group" style="margin-bottom:1.25rem">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $menu->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-grid-2" style="margin-bottom:1.5rem">
            <div class="form-group">
                <label for="price">Selling Price (₱) <span class="req">*</span></label>
                <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                    value="{{ old('price', $menu->price) }}" min="0.01" step="0.01" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div></div>
        </div>

        <hr class="form-divider">

        {{-- Status --}}
        <div class="section-label"><i class="fas fa-toggle-on"></i> Status</div>
        <div class="form-grid-2" style="margin-bottom:1.5rem">
            <div class="form-group">
                <label>Active Status <span class="req">*</span></label>
                <div class="tile-group">
                    <input type="radio" name="is_active" id="active_yes" value="1" class="tile-input"
                        {{ old('is_active', $menu->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <label for="active_yes" class="tile-label" style="flex:none;padding:.6rem .9rem">
                        <div class="tile-icon" style="background:rgba(22,163,74,0.10);color:#16A34A;width:28px;height:28px;font-size:.75rem"><i class="fas fa-toggle-on"></i></div>
                        <div class="tile-text" style="font-size:.82rem">Active</div>
                    </label>
                    <input type="radio" name="is_active" id="active_no" value="0" class="tile-input"
                        {{ old('is_active', $menu->is_active ? '1' : '0') === '0' ? 'checked' : '' }}>
                    <label for="active_no" class="tile-label" style="flex:none;padding:.6rem .9rem">
                        <div class="tile-icon" style="background:rgba(220,38,38,0.10);color:#DC2626;width:28px;height:28px;font-size:.75rem"><i class="fas fa-toggle-off"></i></div>
                        <div class="tile-text" style="font-size:.82rem">Inactive</div>
                    </label>
                </div>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div></div>
        </div>

        <hr class="form-divider">

        {{-- Image --}}
        <div class="section-label"><i class="fas fa-image"></i> Item Image</div>
        <div style="margin-bottom:1.5rem">
            @if($menu->image && Storage::disk('public')->exists($menu->image))
            <div class="current-image-wrap">
                <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->menu_name }}">
                <div>
                    <div style="font-size:.82rem;font-weight:600;color:var(--dark)">Current Image</div>
                    <div style="font-size:.72rem;color:var(--muted)">Upload a new image below to replace it.</div>
                </div>
            </div>
            @endif
            <div class="img-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                    onchange="previewImage(this)">
                <i class="fas fa-cloud-arrow-up" style="font-size:1.6rem;color:var(--muted);margin-bottom:.35rem;display:block"></i>
                <div style="font-size:.83rem;font-weight:600;color:var(--dark)">Upload replacement image (optional)</div>
                <div style="font-size:.72rem;color:var(--muted);margin-top:.2rem">JPEG, PNG, WebP · max 2 MB</div>
            </div>
            <div class="img-preview-wrap" id="previewWrap">
                <img id="previewImg" src="" alt="New Image Preview">
                <div style="margin-top:.4rem">
                    <button type="button" onclick="clearImage()" style="font-size:.75rem;color:var(--primary);background:none;border:none;cursor:pointer;font-family:inherit">
                        <i class="fas fa-trash"></i> Remove new image
                    </button>
                </div>
            </div>
            @error('image')<div class="invalid-feedback" style="margin-top:.4rem">{{ $message }}</div>@enderror
        </div>

    </div>{{-- /form-card-body --}}

    <div class="form-footer">
        <a href="{{ route('menu.show', $menu) }}" class="btn-cancel">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>
        <button type="button" class="btn-submit" onclick="openConfirmDialog()" id="saveBtn">
            <i class="fas fa-floppy-disk"></i> Save Changes
        </button>
    </div>
</div>
</form>

{{-- Confirm dialog --}}
<div id="confirmOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:18px;padding:2rem;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="text-align:center;margin-bottom:1.25rem">
            <div style="width:52px;height:52px;border-radius:13px;background:rgba(245,158,11,0.12);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#D97706;margin:0 auto .85rem">
                <i class="fas fa-pen"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:var(--dark);margin:0 0 .5rem">Save Changes?</h3>
            <p style="font-size:.85rem;color:var(--muted);margin:0">Your changes to <strong>{{ $menu->menu_name }}</strong> will be saved.</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <button type="button" onclick="closeConfirmDialog()" style="flex:1;padding:.65rem;border:1.5px solid var(--border);border-radius:10px;background:transparent;font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer">Cancel</button>
            <button type="button" onclick="submitForm()" style="flex:1;padding:.65rem;background:linear-gradient(90deg,var(--primary),#F97316);border:none;border-radius:10px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer">
                <i class="fas fa-floppy-disk"></i> Save
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
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
function openConfirmDialog()  { document.getElementById('confirmOverlay').style.display = 'flex'; }
function closeConfirmDialog() { document.getElementById('confirmOverlay').style.display = 'none'; }
function submitForm() {
    document.getElementById('saveBtn').disabled = true;
    document.getElementById('menuForm').submit();
}
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmDialog();
});
</script>
@endsection
