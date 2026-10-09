{{-- "Actual purchase" cells on the PO edit page: quantity bought, amount paid, and a live check against the ordered quantity --}}
@php
    $receivedVal = old('received.'.$item->id, $item->quantity_received !== null ? number_format($item->quantity_received, $decimals, '.', '') : '');
    $paidVal     = old('paid.'.$item->id, $item->amount_paid !== null ? number_format($item->amount_paid, 2, '.', '') : '');
@endphp
<td class="rcv-col">
    <div class="qty-wrap">
        <input type="number" name="received[{{ $item->id }}]" value="{{ $receivedVal }}"
               class="qty-input rcv-input js-received" step="{{ $step }}" min="0" placeholder="—"
               title="Quantity actually bought ({{ $item->unit }}). Enter 0 if this item was not bought."
               oninput="updateReceivingRow(this)">
        <span class="field-hint" style="margin:0">{{ $item->unit }}</span>
    </div>
</td>
<td class="rcv-col">
    <div class="qty-wrap">
        <div class="peso-wrap">₱
            <input type="number" name="paid[{{ $item->id }}]" value="{{ $paidVal }}"
                   class="qty-input rcv-input js-paid" step="0.01" min="0" placeholder="0.00"
                   title="Total amount paid for this item"
                   oninput="updateReceivingRow(this)">
        </div>
        <span class="unit-cost-hint js-unit-cost" data-unit="{{ $item->unit }}"></span>
    </div>
</td>
<td class="rcv-col">
    <span class="js-rcv-badge {{ $item->receiving_badge_class }}">{{ $item->receiving_label }}</span>
</td>
