{{--
    One raw meat row of the Convert Raw Meat → RTC Servings form.
    $index: row number in the lines[] array ('__I__' inside the <template> for new rows)
    $line:  previously entered values (old input), or [] for an empty row
--}}
<div class="cv-line" data-cv-line>
    <div class="cv-line-hd">
        <label>Raw Meat Item <span style="color:var(--red-600)">*</span></label>
        <button type="button" class="cv-remove" onclick="cvRemoveLine(this)" title="Remove this raw meat" aria-label="Remove this raw meat"><i class="fas fa-trash"></i></button>
    </div>
    <div class="field" style="margin-bottom:.75rem">
        <select name="lines[{{ $index }}][inventory_item_id]" class="cv-item" data-searchable data-search-placeholder="Search raw meat items…" required onchange="cvUpdate()">
            <option value="">Select raw meat item…</option>
            @foreach($rawItems as $item)
            @php
                // Default portion in the unit typed on the form: grams for kilogram meats
                $defaultPortion = '';
                if ($item->portion_size !== null) {
                    $defaultPortion = $item->unit === 'Kilogram' && ($item->portion_unit ?? 'Kilogram') !== 'Gram'
                        ? (float) $item->portion_size * 1000
                        : (float) $item->portion_size;
                }
            @endphp
            <option value="{{ $item->id }}" data-qty="{{ (float) $item->quantity }}" data-unit="{{ $item->unit }}" data-portion="{{ $defaultPortion }}"
                {{ (string) ($line['inventory_item_id'] ?? '') === (string) $item->id ? 'selected' : '' }}>
                {{ $item->item_name }} ({{ number_format($item->quantity, 2) }} @unit($item->unit) available)
            </option>
            @endforeach
        </select>
    </div>
    <div class="cv-grid">
        <div class="field" style="margin-bottom:0">
            <label>Raw Quantity to Use <span style="color:var(--red-600)">*</span></label>
            <div class="cv-input-wrap">
                <input type="number" class="no-spin cv-raw" name="lines[{{ $index }}][raw_quantity]" step="0.01" min="0.01" required
                       placeholder="0.00" value="{{ $line['raw_quantity'] ?? '' }}" oninput="cvUpdate()">
                <span class="cv-suffix cv-raw-unit">Kg</span>
            </div>
        </div>
        <div class="field" style="margin-bottom:0">
            <label>Portion Size (per serving) <span style="color:var(--red-600)">*</span></label>
            <div class="cv-input-wrap">
                <input type="number" class="no-spin cv-portion" name="lines[{{ $index }}][portion_size]" step="any" min="0.001" required
                       placeholder="250" value="{{ $line['portion_size'] ?? '' }}" oninput="cvUpdate()">
                <span class="cv-suffix cv-portion-unit">g</span>
            </div>
        </div>
    </div>
    <div class="cv-line-note"></div>
</div>
