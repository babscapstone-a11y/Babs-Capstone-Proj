{{--
    Shared "Adjust Stock" modal (Raw Meats, RTC Units, Beverages). Self-contained: own styles + script.
    Posts to inventory.adjustments.store, then the page reloads with a success message.

    @include('inventory.partials.adjust-modal', [
        'field'      => 'inventory_item_id' | 'menu_item_id',   // stock item, or a menu item's RTC servings
        'itemLabel'  => 'Raw Meat Item',
        'options'    => [['id' => 1, 'name' => 'Beef', 'qty' => 8.0, 'unit' => 'Kg'], ...],
        'decimals'   => 2,                                      // 0 for whole units (bottles, servings)
        'searchHint' => 'Search raw meat items…',
    ])
    Open with openAdjust() or openAdjustFor(id) (e.g. from a table row).
--}}
@php $step = $decimals > 0 ? '0.' . str_repeat('0', $decimals - 1) . '1' : '1'; @endphp
<style>
.sa-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:999;display:none;align-items:center;justify-content:center;padding:1rem}
.sa-backdrop.open{display:flex}
.sa-modal{background:var(--surface);border-radius:20px;width:100%;max-width:460px;max-height:calc(100vh - 2rem);overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.18)}
.sa-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.sa-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem;margin:0}
.sa-hd h3 i{color:#7C3AED}
.sa-close{width:32px;height:32px;border-radius:8px;border:none;background:var(--surface-2);cursor:pointer;color:var(--muted)}
.sa-close:hover{background:var(--red-100);color:var(--primary)}
.sa-body{padding:1.5rem}
.sa-field{margin-bottom:1.1rem}
.sa-field > label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.sa-field .req{color:var(--red-600)}
.sa-field .hint{font-weight:400;color:var(--muted);font-size:.74rem}
.sa-field input,.sa-field select,.sa-field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.sa-field input:focus,.sa-field select:focus,.sa-field textarea:focus{border-color:var(--primary)}
.sa-types{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.sa-type{padding:.55rem .8rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;font-size:.81rem;font-weight:600;font-family:inherit;color:var(--dark);display:flex;align-items:center;justify-content:center;gap:.4rem}
.sa-type.selected{border-color:var(--primary);background:var(--red-50);color:var(--primary)}
.sa-preview{background:var(--surface-2);border-radius:12px;padding:1rem 1.2rem;border:1px solid var(--border);display:none}
.sa-preview div{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;color:var(--dark)}
.sa-preview div:last-child{margin-bottom:0;font-weight:700;color:var(--primary)}
.sa-error{background:var(--red-50);border:1.5px solid var(--red-200);color:var(--red-700);border-radius:10px;padding:.6rem .9rem;font-size:.8rem;margin-bottom:1rem;display:none}
.sa-foot{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.sa-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:1.5px solid var(--border);background:var(--surface);color:var(--dark)}
.sa-btn.primary{background:#7C3AED;border-color:#7C3AED;color:#fff}
.sa-btn.primary:hover{background:#6D28D9}
</style>

<div class="sa-backdrop" id="stockAdjustModal" role="dialog" aria-modal="true" aria-labelledby="stockAdjustTitle">
    <div class="sa-modal">
        <div class="sa-hd">
            <h3 id="stockAdjustTitle"><i class="fas fa-pen-to-square"></i> Adjust Stock</h3>
            <button type="button" class="sa-close" onclick="closeAdjust()" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" id="stockAdjustForm" novalidate>
            @csrf
            <input type="hidden" name="adjustment_type" id="saType" value="damaged">
            <div class="sa-body">
                <div class="sa-error" id="saError"></div>

                <div class="sa-field">
                    <label for="saItem">{{ $itemLabel }} <span class="req">*</span></label>
                    <select name="{{ $field }}" id="saItem" data-searchable data-search-placeholder="{{ $searchHint }}" onchange="saPreview()">
                        <option value="">Select…</option>
                        @foreach($options as $o)
                            <option value="{{ $o['id'] }}" data-qty="{{ $o['qty'] }}" data-unit="{{ $o['unit'] }}">{{ $o['name'] }} ({{ number_format($o['qty'], $decimals) }} {{ $o['unit'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sa-field">
                    <label>Reason <span class="req">*</span></label>
                    <div class="sa-types">
                        <button type="button" class="sa-type selected" data-type="damaged"><i class="fas fa-heart-crack"></i> Damaged</button>
                        <button type="button" class="sa-type" data-type="expired"><i class="fas fa-hourglass-end"></i> Expired</button>
                        <button type="button" class="sa-type" data-type="missing"><i class="fas fa-circle-question"></i> Missing</button>
                        <button type="button" class="sa-type" data-type="correction"><i class="fas fa-pen"></i> Correction</button>
                    </div>
                </div>

                <div class="sa-field">
                    <label for="saQty">Quantity to Deduct <span class="req">*</span><br>
                        <span class="hint">How much to remove from stock — up to what's on hand</span></label>
                    <input type="number" class="no-spin" name="quantity_deducted" id="saQty" min="0" step="{{ $step }}" placeholder="e.g. 2" oninput="saPreview()">
                </div>

                <div class="sa-field">
                    <label for="saRemarks">Remarks</label>
                    <textarea name="remarks" id="saRemarks" rows="2" maxlength="1000" style="resize:none" placeholder="Additional details…"></textarea>
                </div>

                <div class="sa-preview" id="saPreviewBox">
                    <div><span>Before</span><span id="saBefore">—</span></div>
                    <div><span>Deduct</span><span id="saChange">—</span></div>
                    <div><span>After</span><span id="saAfter">—</span></div>
                </div>
            </div>
            <div class="sa-foot">
                <button type="button" class="sa-btn" onclick="closeAdjust()">Cancel</button>
                <button type="submit" class="sa-btn primary"><i class="fas fa-check"></i> Review Adjustment</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var DECIMALS = {{ (int) $decimals }};
    var modal = document.getElementById('stockAdjustModal');
    var form  = document.getElementById('stockAdjustForm');
    var $ = function (id) { return document.getElementById(id); };
    var fmt = function (n) { return Number(n).toFixed(DECIMALS); };

    // Deduct only: "qty" is how much to remove (a positive number)
    function values() {
        var opt = $('saItem').selectedOptions[0];
        var qty = parseFloat($('saQty').value);
        if (!opt || !opt.value) return null;
        var before = parseFloat(opt.dataset.qty) || 0;
        qty = isNaN(qty) ? 0 : qty;
        return { name: opt.textContent.split(' (')[0].trim(), unit: opt.dataset.unit, before: before, qty: qty, after: before - qty };
    }

    window.saPreview = function () {
        var v = values(), box = $('saPreviewBox');
        $('saError').style.display = 'none';
        if (v) $('saQty').max = v.before;
        if (!v || v.qty <= 0) { box.style.display = 'none'; return; }
        $('saBefore').textContent = fmt(v.before) + ' ' + v.unit;
        $('saChange').textContent = '-' + fmt(v.qty) + ' ' + v.unit;
        $('saAfter').textContent  = fmt(Math.max(0, v.after)) + ' ' + v.unit;
        box.style.display = 'block';
    };

    window.openAdjust = function () {
        form.reset();
        modal.querySelectorAll('.sa-type').forEach(function (b) { b.classList.toggle('selected', b.dataset.type === 'damaged'); });
        $('saType').value = 'damaged';
        $('saError').style.display = 'none';
        saPreview();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    };
    // From a table row: open with that item already chosen (extra arguments are ignored)
    window.openAdjustFor = function (id) {
        openAdjust();
        $('saItem').value = String(id);
        $('saItem').dispatchEvent(new Event('change'));
        $('saQty').focus();
    };
    window.closeAdjust = function () {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    };

    modal.addEventListener('click', function (e) { if (e.target === modal) closeAdjust(); });
    modal.querySelectorAll('.sa-type').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modal.querySelectorAll('.sa-type').forEach(function (b) { b.classList.toggle('selected', b === btn); });
            $('saType').value = btn.dataset.type;
        });
    });

    // Check the form, then confirm with the shared admin pop-up before saving
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var v = values(), err = null;
        if (!v) err = 'Please choose an item to adjust.';
        else if (!(v.qty > 0)) err = 'Please enter a quantity to deduct greater than 0.';
        else if (DECIMALS === 0 && v.qty % 1 !== 0) err = 'Please enter a whole number.';
        else if (v.qty > v.before) err = 'You can only deduct up to what is in stock (' + fmt(v.before) + ' ' + v.unit + ').';
        if (err) { $('saError').textContent = err; $('saError').style.display = 'block'; return; }

        // The form stays open underneath, so Cancel on the confirmation goes back to it
        openModal({
            type: 'warn',
            iconClass: 'fas fa-pen-to-square',
            title: 'Confirm Adjustment?',
            desc: v.name + ': ' + fmt(v.before) + ' → ' + fmt(v.after) + ' ' + v.unit
                + ' (-' + fmt(v.qty) + ', ' + modal.querySelector('.sa-type.selected').textContent.trim() + ').',
            confirmText: 'Confirm Adjustment',
            onConfirm: function () { form.submit(); },
        });
    });
})();
</script>
