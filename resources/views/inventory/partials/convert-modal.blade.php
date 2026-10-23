{{--
    Convert Raw Meat → RTC Servings form + its review step. Shared by the RTC Inventory and
    Conversion History pages. A dish can use several raw meats (+ Add Another Raw Meat), each with
    its own raw quantity (kg, or pieces) and portion per serving (grams, or pieces).
    Open with cvOpen(), or cvOpenFor(menuItemId, portion, portionUnit) to preselect a dish.
    Needs: $menuItems, $rawItems, $submitClass (button colour class of the page).
--}}
@php
    $cvHasErrors = $errors->has('menu_item_id') || $errors->has('lines') || $errors->has('lines.*');
    $cvOldLines  = array_values(old('lines', [[]])) ?: [[]];
@endphp
<style>
#convertModal .modal{max-width:620px}
#convertModal .modal-body,#convertConfirmModal .modal-body{max-height:68vh;overflow-y:auto}
.cv-line{border:1.5px solid var(--border);border-radius:12px;padding:.85rem .95rem;margin-bottom:.75rem;background:var(--surface-2)}
.cv-line-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem}
.cv-line-hd label{font-size:.8rem;font-weight:600;color:var(--dark)}
.cv-remove{width:28px;height:28px;border-radius:8px;border:none;background:transparent;color:var(--muted);cursor:pointer;font-size:.8rem}
.cv-remove:hover{background:var(--red-100);color:var(--primary)}
.cv-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
@media (max-width:520px){.cv-grid{grid-template-columns:1fr}}
.cv-input-wrap{position:relative}
.cv-input-wrap input{padding-right:3rem !important}
.cv-suffix{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);font-size:.78rem;font-weight:700;color:var(--muted);pointer-events:none}
.cv-line-note{font-size:.75rem;color:var(--muted);margin-top:.45rem;min-height:1em}
.cv-add{width:100%;justify-content:center;border-style:dashed !important;margin-bottom:.25rem}
.cv-warn{background:var(--amber-50, #FFFBEB);border:1px solid rgba(245,158,11,.35);color:var(--amber-700, #B45309);border-radius:10px;padding:.6rem .8rem;font-size:.78rem;margin-top:.6rem}
.cv-sum-table{width:100%;border-collapse:collapse;font-size:.8rem;margin-top:.6rem}
.cv-sum-table th{text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.04em;color:var(--blue-700);padding:.3rem .4rem;border-bottom:1px solid var(--blue-200)}
.cv-sum-table td{padding:.4rem;border-bottom:1px solid var(--blue-200);color:var(--dark)}
.cv-sum-table tr:last-child td{border-bottom:none}
.cv-limit{font-weight:800;color:var(--blue-700)}
</style>

<div class="modal-backdrop" id="convertModal">
    <div class="modal">
        <div class="modal-hd">
            <h3><i class="fas fa-arrows-rotate"></i> Convert Raw Meat → RTC Servings</h3>
            <button type="button" class="modal-close-btn" onclick="closeLocalModal('convertModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.conversions.store') }}" id="convertForm">
            @csrf
            <div class="modal-body">
                @if($cvHasErrors)
                <div class="error-msg"><i class="fas fa-circle-exclamation"></i> Please fix the following errors:<ul style="margin:.4rem 0 0 1rem;padding:0">@foreach(collect($errors->get('menu_item_id'))->merge($errors->get('lines'))->merge(collect($errors->get('lines.*'))->flatten())->unique() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <div class="error-msg" id="cvClientError" style="display:none"></div>

                <div class="field">
                    <label>Menu Item <span style="color:var(--red-600)">*</span></label>
                    <select name="menu_item_id" id="cvMenuItem" data-searchable data-search-placeholder="Search menu items…" required>
                        <option value="">Select menu item…</option>
                        @foreach($menuItems as $menuItem)
                        <option value="{{ $menuItem->id }}" {{ (string) old('menu_item_id') === (string) $menuItem->id ? 'selected' : '' }}>{{ $menuItem->menu_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="cvLines">
                    @foreach($cvOldLines as $i => $line)
                        @include('inventory.partials.convert-line', ['index' => $i, 'line' => $line])
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline btn-sm cv-add" onclick="cvAddLine()"><i class="fas fa-plus"></i> Add Another Raw Meat</button>

                <div class="conv-calc" id="cvPreview" style="display:none">
                    <div class="calc-label"><i class="fas fa-calculator"></i> Conversion Preview</div>
                    <div class="conv-result"><span id="cvPreviewUnits">0</span> <span style="font-size:.8rem">RTC Servings</span></div>
                    <div id="cvPreviewWarn"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeLocalModal('convertModal')">Cancel</button>
                <button type="button" class="btn {{ $submitClass }}" onclick="cvReview()"><i class="fas fa-arrow-right"></i> Review Conversion</button>
            </div>
        </form>
    </div>
</div>

<template id="cvLineTemplate">
    @include('inventory.partials.convert-line', ['index' => '__I__', 'line' => []])
</template>

<div class="modal-backdrop" id="convertConfirmModal">
    <div class="modal" style="max-width:560px">
        <div class="modal-hd">
            <h3><i class="fas fa-clipboard-check"></i> Confirm Conversion</h3>
            <button type="button" class="modal-close-btn" onclick="closeLocalModal('convertConfirmModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="field" style="margin-bottom:.6rem">
                <label style="margin-bottom:.15rem">Menu Item</label>
                <div style="font-weight:700;color:var(--dark)" id="cvConfirmName">—</div>
            </div>
            <div class="conv-calc">
                <div class="calc-label"><i class="fas fa-calculator"></i> Summary</div>
                <div class="conv-result"><span id="cvConfirmUnits">0</span> <span style="font-size:.8rem">RTC Servings</span></div>
                <table class="cv-sum-table">
                    <thead><tr><th>Raw Meat</th><th>Raw Used</th><th>Per Serving</th><th>Makes</th><th>Remaining</th></tr></thead>
                    <tbody id="cvConfirmRows"></tbody>
                </table>
                <div id="cvConfirmWarn"></div>
            </div>
            <p style="margin-top:1rem;font-size:.82rem;color:var(--muted)">This will deduct the raw stock above and add RTC servings. Please confirm the numbers are correct.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="cvBack()">Back</button>
            <button type="button" class="btn {{ $submitClass }}" onclick="document.getElementById('convertForm').submit()"><i class="fas fa-check"></i> Confirm &amp; Save</button>
        </div>
    </div>
</div>

<script>
(function () {
    let nextIndex = {{ count($cvOldLines) }};
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const num = (n, d) => (+n).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: d });

    // Kilogram meats: raw in Kg, portion in grams. Other meats (pieces): both in their own unit.
    const isKg     = unit => unit === 'Kilogram';
    const unitText = unit => unit === 'Piece' ? 'pc' : unitShort(unit);

    function rows() { return Array.from(document.querySelectorAll('#cvLines [data-cv-line]')); }

    // Everything entered on one row, in the meat's own unit for the maths
    function readLine(row) {
        const opt = row.querySelector('.cv-item').selectedOptions[0];
        const unit = opt && opt.value ? opt.dataset.unit : 'Kilogram';
        const raw = parseFloat(row.querySelector('.cv-raw').value) || 0;
        const entered = parseFloat(row.querySelector('.cv-portion').value) || 0;
        const portion = isKg(unit) ? entered / 1000 : entered;
        return {
            row, opt, unit, raw, entered, portion,
            chosen: !!(opt && opt.value),
            name: opt && opt.value ? opt.text.split('(')[0].trim() : '',
            avail: opt && opt.value ? parseFloat(opt.dataset.qty) || 0 : 0,
            servings: raw > 0 && portion > 0 ? Math.floor(raw / portion + 1e-9) : 0,
            rawLabel: num(raw, 2) + ' ' + unitText(unit),
            portionLabel: num(entered, 2) + ' ' + (isKg(unit) ? 'g' : unitText(unit)),
        };
    }

    // Per-row units, notes and remove buttons, plus the overall preview
    window.cvUpdate = function () {
        const all = rows();
        all.forEach(row => {
            const l = readLine(row);

            row.querySelector('.cv-raw-unit').textContent = unitText(l.unit);
            row.querySelector('.cv-portion-unit').textContent = isKg(l.unit) ? 'g' : unitText(l.unit);
            row.querySelector('.cv-portion-label').textContent = isKg(l.unit) ? 'grams' : (l.unit === 'Piece' ? 'pieces' : unitText(l.unit));
            row.querySelector('.cv-portion').placeholder = isKg(l.unit) ? '250' : '1';
            row.querySelector('.cv-remove').style.visibility = all.length > 1 ? 'visible' : 'hidden';

            const note = row.querySelector('.cv-line-note');
            if (!l.chosen) { note.textContent = ''; return; }
            note.innerHTML = 'Available: <strong>' + num(l.avail, 2) + ' ' + esc(unitText(l.unit)) + '</strong>'
                + (l.servings ? ' &bull; Makes <strong>' + l.servings + '</strong> serving' + (l.servings === 1 ? '' : 's') : '');
        });

        const lines = all.map(readLine);
        const ready = lines.every(l => l.chosen && l.servings > 0);
        document.getElementById('cvPreview').style.display = ready ? '' : 'none';
        if (ready) {
            document.getElementById('cvPreviewUnits').textContent = Math.min(...lines.map(l => l.servings));
            document.getElementById('cvPreviewWarn').innerHTML = mismatchNote(lines);
        }
    };

    // Meats that would make different numbers of servings: the scarcest one decides
    function mismatchNote(lines) {
        if (lines.length < 2) return '';
        const min = Math.min(...lines.map(l => l.servings));
        const extra = lines.filter(l => l.servings > min);
        if (!extra.length) return '';
        return '<div class="cv-warn"><i class="fas fa-triangle-exclamation"></i> Only <strong>' + min + '</strong> servings can be made because of '
            + esc(lines.find(l => l.servings === min).name) + '. ' + extra.map(l => esc(l.name) + ' is enough for ' + l.servings).join(', ')
            + ', but the full raw quantity entered will still be deducted. Lower it if you want to keep the extra in stock.</div>';
    }

    window.cvAddLine = function () {
        const html = document.getElementById('cvLineTemplate').innerHTML.replaceAll('__I__', nextIndex++);
        document.getElementById('cvLines').insertAdjacentHTML('beforeend', html);
        cvUpdate();
    };

    window.cvRemoveLine = function (btn) {
        if (rows().length > 1) btn.closest('[data-cv-line]').remove();
        cvUpdate();
    };

    function resetForm() {
        document.getElementById('cvClientError').style.display = 'none';
        document.getElementById('cvMenuItem').value = '';
        rows().slice(1).forEach(r => r.remove());
        const first = rows()[0];
        first.querySelectorAll('select, input').forEach(el => { el.value = ''; });
    }

    window.cvOpen = function () {
        resetForm();
        cvUpdate();
        openLocalModal('convertModal');
    };

    // From an RTC dish's row: preselect the dish and its usual portion (stored in grams for kg meats)
    window.cvOpenFor = function (menuItemId, portion, portionUnit) {
        resetForm();
        document.getElementById('cvMenuItem').value = menuItemId;
        if (portion) rows()[0].querySelector('.cv-portion').value = portionUnit === 'Kilogram' ? portion * 1000 : portion;
        cvUpdate();
        openLocalModal('convertModal');
    };

    window.cvReview = function () {
        const errBox = document.getElementById('cvClientError');
        const fail = msg => { errBox.textContent = msg; errBox.style.display = ''; errBox.scrollIntoView({ block: 'nearest' }); };
        errBox.style.display = 'none';

        const menuOpt = document.getElementById('cvMenuItem').selectedOptions[0];
        if (!menuOpt || !menuOpt.value) return fail('Please select a menu item.');

        const lines = rows().map(readLine);
        const seen = new Set();
        for (const l of lines) {
            if (!l.chosen) return fail('Please select a raw meat item on every row.');
            if (seen.has(l.opt.value)) return fail(l.name + ' is selected more than once.');
            seen.add(l.opt.value);
            if (l.raw <= 0) return fail(l.name + ': enter a raw quantity greater than 0.');
            if (l.entered <= 0) return fail(l.name + ': enter a portion size greater than 0.');
            if (l.raw > l.avail) return fail('Insufficient ' + l.name + ' stock. Available: ' + num(l.avail, 2) + ' ' + unitText(l.unit) + '.');
            if (l.servings < 1) return fail(l.name + ': this quantity is too small for even 1 serving of ' + l.portionLabel + '.');
        }

        const units = Math.min(...lines.map(l => l.servings));
        document.getElementById('cvConfirmName').textContent = menuOpt.text.trim();
        document.getElementById('cvConfirmUnits').textContent = units;
        document.getElementById('cvConfirmRows').innerHTML = lines.map(l =>
            '<tr><td style="font-weight:700">' + esc(l.name) + '</td><td>' + esc(l.rawLabel) + '</td><td>' + esc(l.portionLabel)
            + '</td><td class="' + (l.servings === units ? 'cv-limit' : '') + '">' + l.servings + '</td><td>'
            + esc(num(l.avail - l.raw, 2) + ' ' + unitText(l.unit)) + '</td></tr>').join('');
        document.getElementById('cvConfirmWarn').innerHTML = mismatchNote(lines);

        closeLocalModal('convertModal');
        openLocalModal('convertConfirmModal');
    };

    window.cvBack = function () {
        closeLocalModal('convertConfirmModal');
        openLocalModal('convertModal');
    };

    document.addEventListener('DOMContentLoaded', function () {
        cvUpdate();
        @if($cvHasErrors)
        openLocalModal('convertModal');
        @endif
    });
})();
</script>
