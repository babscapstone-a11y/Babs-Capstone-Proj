@extends('layouts.customer-app')
@section('title', "Checkout – Bab's Resto")

@section('styles')
<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both; }

.checkout-wrap { max-width: 980px; margin: 0 auto; }

.page-title {
    font-size: 1.5rem; font-weight: 900; color: var(--dark);
    display: flex; align-items: center; gap: .6rem; margin-bottom: 1.5rem;
}
.page-title i { color: var(--primary); }

.checkout-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }
@media (max-width: 860px) { .checkout-grid { grid-template-columns: 1fr; } }

.card {
    background: var(--white); border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    overflow: hidden; margin-bottom: 1.25rem;
}
.card-header {
    padding: 1rem 1.4rem; border-bottom: 1px solid var(--border);
    background: #FAFBFC;
}
.card-header h2 {
    font-size: .95rem; font-weight: 700; color: var(--dark);
    display: flex; align-items: center; gap: .55rem;
}
.card-header h2 i { color: var(--primary); }
.card-body { padding: 1.25rem 1.4rem; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 480px) { .info-grid { grid-template-columns: 1fr; } }
.info-item .label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .2rem; }
.info-item .value { font-size: .92rem; font-weight: 600; color: var(--dark); }

/* Order type / payment selector */
.option-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.option-grid.three { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 700px) { .option-grid.three { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .option-grid, .option-grid.three { grid-template-columns: 1fr; } }

.option-card {
    position: relative; border: 1.5px solid var(--border); border-radius: 14px;
    padding: 1rem; cursor: pointer; transition: all .18s; text-align: center;
}
.option-card:hover { border-color: var(--primary); }
.option-card input { position: absolute; opacity: 0; pointer-events: none; }
.option-card .oc-icon { font-size: 1.4rem; color: var(--muted); margin-bottom: .5rem; transition: color .18s; }
.option-card .oc-label { font-weight: 700; font-size: .87rem; color: var(--dark); }
.option-card .oc-sub { font-size: .72rem; color: var(--muted); margin-top: .15rem; }
.option-card.selected { border-color: var(--primary); background: #FEF2F2; }
.option-card.selected .oc-icon { color: var(--primary); }
.option-card.disabled { opacity: .5; cursor: not-allowed; }
.option-card.disabled:hover { border-color: var(--border); }
.oc-badge {
    position: absolute; top: -8px; right: 10px;
    background: var(--accent); color: #fff; font-size: .62rem; font-weight: 700;
    padding: .15rem .5rem; border-radius: 20px;
}

.field { margin-top: 1rem; }
.field label { display: block; font-size: .8rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
.field input, .field textarea {
    width: 100%; padding: .7rem .9rem; border: 1.5px solid var(--border); border-radius: 10px;
    font-family: inherit; font-size: .87rem; color: var(--text); transition: border-color .15s;
}
.field input:focus, .field textarea:focus { outline: none; border-color: var(--primary); }
.field textarea { resize: vertical; min-height: 80px; }
.field .hint { font-size: .74rem; color: var(--muted); margin-top: .3rem; }
.field.hidden { display: none; }
.hidden { display: none !important; }

/* Order summary sidebar */
.summary-card { position: sticky; top: calc(var(--nav-h) + 1.5rem); }
.summary-item { display: flex; justify-content: space-between; padding: .45rem 0; font-size: .83rem; }
.summary-item .si-name { color: var(--text); }
.summary-item .si-qty { color: var(--muted); font-size: .76rem; }
.summary-row { display: flex; justify-content: space-between; padding: .5rem 0; font-size: .87rem; }
.summary-row.total {
    border-top: 2px solid var(--border); margin-top: .6rem; padding-top: .9rem;
    font-size: 1.15rem; font-weight: 800; color: var(--dark);
}
.summary-row.total .amt { color: var(--primary); }

.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .85rem 1.25rem; border-radius: 12px;
    font-size: .92rem; font-weight: 700; font-family: inherit;
    cursor: pointer; border: none; transition: all .18s; text-decoration: none;
    width: 100%;
}
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover:not(:disabled) { background: var(--primary-dk); transform: translateY(-1px); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn-outline { background: var(--white); border: 1.5px solid var(--border); color: var(--text); }
.btn-outline:hover { border-color: var(--primary); color: var(--primary); }

.spin {
    width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.4);
    border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
<div class="page-wrap checkout-wrap">
    <div class="page-title fade-up"><i class="fas fa-receipt"></i> Checkout</div>

    <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" enctype="multipart/form-data">
        @csrf
        <div class="checkout-grid">

            {{-- Left column --}}
            <div class="fade-up">

                {{-- Customer Information --}}
                <div class="card">
                    <div class="card-header"><h2><i class="fas fa-user"></i> Customer Information</h2></div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="label">Customer Name</div>
                                <div class="value">{{ $customer->full_name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="label">Contact Number</div>
                                <div class="value">{{ $customer->contact_no ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Type --}}
                <div class="card">
                    <div class="card-header"><h2><i class="fas fa-utensils"></i> Order Type</h2></div>
                    <div class="card-body">
                        <div class="option-grid three">
                            <label class="option-card selected" data-type="dine_in">
                                <input type="radio" name="order_type" value="dine_in" checked>
                                <div class="oc-icon"><i class="fas fa-utensils"></i></div>
                                <div class="oc-label">Dine-In</div>
                            </label>
                            <label class="option-card" data-type="takeout">
                                <input type="radio" name="order_type" value="takeout">
                                <div class="oc-icon"><i class="fas fa-bag-shopping"></i></div>
                                <div class="oc-label">Take-Out</div>
                            </label>
                            <label class="option-card" data-type="online">
                                <input type="radio" name="order_type" value="online">
                                <div class="oc-icon"><i class="fas fa-mobile-screen-button"></i></div>
                                <div class="oc-label">Online Pre-Order</div>
                                <div class="oc-sub">Pay online, pick up later</div>
                            </label>
                        </div>

                        {{-- Dine-In fields --}}
                        <div class="field" id="tableNumberField">
                            <label for="table_number">Table Number <span style="color:var(--muted);font-weight:500">(optional — assigned at the counter if left blank)</span></label>
                            <input type="number" id="table_number" name="table_number" min="1" max="999" value="{{ old('table_number') }}" placeholder="e.g. 12">
                        </div>

                        {{-- Take-Out info --}}
                        <div class="field hidden" id="pickupTimeField">
                            <label>Estimated Pick-Up Time</label>
                            <div class="hint" style="font-size:.85rem;color:var(--dark);font-weight:600">
                                <i class="fas fa-clock" style="color:var(--accent)"></i> Approximately 30 minutes after order confirmation
                            </div>
                        </div>

                        {{-- Online Pre-Order: scheduled pickup --}}
                        <div class="field hidden" id="onlinePickupField">
                            <label for="pickup_date">Scheduled Pick-up Date &amp; Time</label>
                            <div style="display:flex;gap:.6rem">
                                <input type="date" id="pickup_date" placeholder="Date" style="flex:1">
                                <input type="time" id="pickup_time" placeholder="Time" style="flex:1">
                            </div>
                            <input type="hidden" name="pickup_at" id="pickup_at">
                            <div class="hint">Choose when you'll pick up your order.</div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method (Dine-In / Take-Out) --}}
                <div class="card" id="cashPaymentCard">
                    <div class="card-header"><h2><i class="fas fa-wallet"></i> Payment Method</h2></div>
                    <div class="card-body">
                        <div class="option-grid">
                            <label class="option-card selected" data-pay="cash">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <div class="oc-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="oc-label">Cash</div>
                            </label>
                            <label class="option-card disabled">
                                <span class="oc-badge">Coming Soon</span>
                                <input type="radio" disabled>
                                <div class="oc-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="oc-label">Cashless</div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Down Payment (Online Pre-Order only) --}}
                <div class="card hidden" id="downPaymentCard">
                    <div class="card-header"><h2><i class="fas fa-qrcode"></i> Down Payment ({{ \App\Models\Order::DOWN_PAYMENT_PERCENT }}% required)</h2></div>
                    <div class="card-body">
                        <div class="hint" style="font-size:.85rem;color:var(--dark);font-weight:600;margin-bottom:.9rem">
                            Required down-payment: <span id="requiredDownPaymentDisplay">₱0.00</span>
                        </div>

                        <div class="field" style="margin-top:0">
                            <label for="down_payment_method">Payment Method</label>
                            <select id="down_payment_method" name="down_payment_method" style="width:100%;padding:.7rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-family:inherit;font-size:.87rem">
                                <option value="">Select method...</option>
                                <option value="gcash">GCash</option>
                                <option value="maya">Maya</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other Electronic Payment</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="down_payment_reference">Reference Number</label>
                            <input type="text" id="down_payment_reference" name="down_payment_reference" placeholder="e.g. GCash reference number">
                        </div>
                        <div class="field">
                            <label for="down_payment_amount">Amount Paid</label>
                            <input type="number" id="down_payment_amount" name="down_payment_amount" min="1" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field">
                            <label for="proof_image">Proof of Payment (screenshot/receipt)</label>
                            <input type="file" id="proof_image" name="proof_image" accept="image/*">
                            <div class="hint">Your order will be forwarded to the kitchen only after a cashier verifies this payment.</div>
                        </div>
                    </div>
                </div>

                {{-- Special Instructions --}}
                <div class="card">
                    <div class="card-header"><h2><i class="fas fa-note-sticky"></i> Special Instructions</h2></div>
                    <div class="card-body">
                        <div class="field" style="margin-top:0">
                            <textarea name="special_instructions" placeholder="e.g. Less spicy, no onion, extra sauce...">{{ old('special_instructions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: summary --}}
            <div class="card summary-card fade-up">
                <div class="card-header"><h2><i class="fas fa-list-ul"></i> Order Summary</h2></div>
                <div class="card-body">
                    @foreach($cart->items as $item)
                    <div class="summary-item">
                        <div>
                            <div class="si-name">{{ $item->menuItem->menu_name }}</div>
                            <div class="si-qty">{{ $item->quantity }} × ₱{{ number_format($item->unit_price, 2) }}</div>
                            @if($item->notes)
                            <div style="font-size:.72rem;color:var(--muted);font-style:italic;margin-top:.1rem">
                                <i class="fas fa-note-sticky"></i> {{ $item->notes }}
                            </div>
                            @endif
                        </div>
                        <div style="font-weight:700;color:var(--dark)">₱{{ number_format($item->unit_price * $item->quantity, 2) }}</div>
                    </div>
                    @endforeach

                    <div class="summary-row total"><span>Grand Total</span><span class="amt">₱{{ number_format($cart->total, 2) }}</span></div>

                    <button type="submit" class="btn btn-primary" id="confirmOrderBtn" style="margin-top:1.1rem">
                        <i class="fas fa-check-circle"></i> <span>Confirm Order</span>
                    </button>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline" style="margin-top:.6rem">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
/* Order type selector */
const orderTypeCards = document.querySelectorAll('.option-card[data-type]');
const tableNumberField = document.getElementById('tableNumberField');
const pickupTimeField  = document.getElementById('pickupTimeField');
const onlinePickupField = document.getElementById('onlinePickupField');
const cashPaymentCard  = document.getElementById('cashPaymentCard');
const downPaymentCard  = document.getElementById('downPaymentCard');
const cartTotal = {{ (float) $cart->total }};
const downPaymentPercent = {{ \App\Models\Order::DOWN_PAYMENT_PERCENT }};

orderTypeCards.forEach(card => {
    card.addEventListener('click', () => {
        orderTypeCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;

        const type = card.dataset.type;
        tableNumberField.classList.toggle('hidden', type !== 'dine_in');
        pickupTimeField.classList.toggle('hidden', type !== 'takeout');
        onlinePickupField.classList.toggle('hidden', type !== 'online');
        cashPaymentCard.classList.toggle('hidden', type === 'online');
        downPaymentCard.classList.toggle('hidden', type !== 'online');

        if (type === 'online') {
            const required = (cartTotal * downPaymentPercent / 100).toFixed(2);
            document.getElementById('requiredDownPaymentDisplay').textContent = '₱' + required;
            if (! document.getElementById('down_payment_amount').value) {
                document.getElementById('down_payment_amount').value = required;
            }
        }
    });
});

/* Payment method selector */
document.querySelectorAll('.option-card[data-pay]').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.option-card[data-pay]').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
    });
});

/* Combine pickup date + time into a single datetime field before submit */
function syncPickupAt() {
    const date = document.getElementById('pickup_date').value;
    const time = document.getElementById('pickup_time').value;
    document.getElementById('pickup_at').value = (date && time) ? `${date} ${time}:00` : '';
}
document.getElementById('pickup_date').addEventListener('change', syncPickupAt);
document.getElementById('pickup_time').addEventListener('change', syncPickupAt);

/* Confirm & submit with duplicate-prevention */
const form = document.getElementById('checkoutForm');
const confirmBtn = document.getElementById('confirmOrderBtn');

form.addEventListener('submit', (e) => {
    syncPickupAt();

    if (! confirm('Are you sure you want to place this order?')) {
        e.preventDefault();
        return;
    }
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spin"></span> <span>Placing Order...</span>';
});
</script>
@endsection
