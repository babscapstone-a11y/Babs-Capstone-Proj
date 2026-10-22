{{--
    One item row of the "Add Item to Purchase Order" window.
    $index: row number in the items[] array ('__I__' inside the <template> for new rows)
    $row:   previously entered values (old input), or [] for an empty row
--}}
<div class="po-add-line" data-po-add-line>
    <div class="po-add-line-hd">
        <label>Inventory Item <span style="color:var(--red-600)">*</span></label>
        <button type="button" class="po-add-remove" onclick="poRemoveLine(this)" title="Remove this item" aria-label="Remove this item"><i class="fas fa-trash"></i></button>
    </div>
    <div class="field" style="margin-bottom:.75rem">
        <select name="items[{{ $index }}][inventory_item_id]" class="po-add-item" data-searchable data-search-placeholder="Search inventory items…" required onchange="poAddUpdate()">
            <option value="">Select item…</option>
            @foreach($availableItems as $ai)
            <option value="{{ $ai->id }}" data-unit="@unit($ai->unit)" data-stock="{{ number_format($ai->quantity, 2) }}"
                    data-threshold="{{ rtrim(rtrim(number_format((float) $ai->reorder_level, 2, '.', ''), '0'), '.') }}" data-type="{{ $ai->item_type }}"
                    {{ (string) ($row['inventory_item_id'] ?? '') === (string) $ai->id ? 'selected' : '' }}>
                {{ $ai->item_name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="field" style="margin-bottom:0">
        <label>Quantity to Purchase <span style="color:var(--red-600)">*</span></label>
        <input type="number" class="no-spin po-add-qty" name="items[{{ $index }}][quantity_to_purchase]" step="0.01" min="0.01" required
               placeholder="0.00" value="{{ $row['quantity_to_purchase'] ?? '' }}">
        <div class="field-hint po-add-hint">Current stock: — &nbsp;|&nbsp; Threshold: —</div>
    </div>
</div>
