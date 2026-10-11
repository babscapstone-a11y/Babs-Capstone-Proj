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

/* Order type selector */
.option-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media (max-width: 480px) { .option-grid { grid-template-columns: 1fr; } }

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
.option-card.unavailable { cursor: not-allowed; opacity: .5; }
.option-card.unavailable:hover { border-color: var(--border); }

.pickup-row { display: flex; gap: .6rem; }
.pickup-row.time-only #pickup_date_btn { display: none; }
.pickup-row.time-only .cd-field { flex: 0 1 calc(50% - .3rem); }
@media (max-width: 480px) { .pickup-row.time-only .cd-field { flex: 1; } }

.party-stepper { display: inline-flex; align-items: stretch; border: 1.5px solid var(--border); border-radius: 10px; overflow: hidden; }
.party-stepper:focus-within { border-color: var(--primary); }
.party-stepper button { width: 44px; border: 0; background: #F9FAFB; color: var(--dark); cursor: pointer; font-size: .8rem; }
.party-stepper button:hover { background: #FEF2F2; color: var(--primary); }
.field .party-stepper input { width: 64px; border: 0; border-radius: 0; text-align: center; font-weight: 700; font-size: .95rem; padding: .65rem .3rem; }

.field { margin-top: 1rem; }
.field label { display: block; font-size: .8rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
.field input, .field textarea {
    width: 100%; padding: .7rem .9rem; border: 1.5px solid var(--border); border-radius: 10px;
    font-family: inherit; font-size: .87rem; color: var(--text); transition: border-color .15s;
}
.field input:focus, .field textarea:focus { outline: none; border-color: var(--primary); }
.field textarea { resize: vertical; min-height: 80px; }
.field .hint { font-size: .74rem; color: var(--muted); margin-top: .3rem; }

/* Date calendar + clock dial (partials/date-picker, partials/clock-dial) in the customer red */
:root { --cd-accent: var(--primary); --cd-accent-rgb: 220,38,38; }
.field .cd-field { height: auto; padding: .7rem .9rem; border-color: var(--border); font-size: .87rem; color: var(--text); }
.field .cd-field:hover, .field .cd-field.active { border-color: var(--primary); }

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

    <form id="checkoutForm">
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
                        <div class="option-grid">
                            <label class="option-card selected" data-type="online" data-schedule="advance">
                                <input type="radio" name="order_type" value="online" checked>
                                <div class="oc-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="oc-label">Advance Order</div>
                                <div class="oc-sub">Schedule up to {{ \App\Models\Order::ADVANCE_MAX_DAYS }} days ahead</div>
                            </label>
                            <label class="option-card" data-type="online" data-schedule="pickup">
                                <input type="radio" name="order_type" value="online">
                                <div class="oc-icon"><i class="fas fa-mobile-screen-button"></i></div>
                                <div class="oc-label">Pick-Up</div>
                                <div class="oc-sub">Pick up later today</div>
                            </label>
                        </div>

                        {{-- Scheduled pickup: Advance Order = date + time, Pick-Up = time only (today) --}}
                        <div class="field" id="onlinePickupField">
                            <label id="pickupLabel">Scheduled Pick-up Date &amp; Time</label>
                            <div class="pickup-row">
                                <input type="hidden" id="pickup_date"
                                       data-min="{{ now()->format('Y-m-d') }}" data-max="{{ now()->addDays(\App\Models\Order::ADVANCE_MAX_DAYS)->format('Y-m-d') }}">
                                <button type="button" class="cd-field" id="pickup_date_btn" data-picker="date" data-for="pickup_date"
                                        aria-haspopup="dialog" aria-label="Pick-up date" onclick="DatePick.open(this)">
                                    <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
                                </button>
                                <input type="hidden" id="pickup_time" data-min-time="11:00" data-max-time="21:00">
                                <button type="button" class="cd-field" data-picker="time" data-for="pickup_time"
                                        aria-haspopup="dialog" aria-label="Pick-up time" onclick="ClockDial.open(this)">
                                    <span class="cd-text"></span><i class="fas fa-clock"></i>
                                </button>
                            </div>
                            <input type="hidden" name="pickup_at" id="pickup_at">
                            <div class="hint" id="pickupHint"></div>

                            {{-- Advance Order only: customers can book ahead to dine in --}}
                            <div id="partySizeField" style="margin-top:1rem">
                                <label for="party_size">No. of Persons</label>
                                <div class="party-stepper">
                                    <button type="button" data-step="-1" aria-label="Fewer persons"><i class="fas fa-minus"></i></button>
                                    <input type="text" inputmode="numeric" id="party_size" maxlength="2" placeholder="0" autocomplete="off">
                                    <button type="button" data-step="1" aria-label="More persons"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="hint">Dining in? Tell us how many people are coming so we can prepare your table.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment via GCash QR --}}
                <div class="card" id="paymentCard">
                    <div class="card-header"><h2><i class="fas fa-qrcode"></i> Pay with GCash (Scan QR)</h2></div>
                    <div class="card-body">
                        <div class="option-grid">
                            @php $halfAllowed = $cart->total >= \App\Models\Order::HALF_PAYMENT_MIN_TOTAL; @endphp
                            <label class="option-card {{ $halfAllowed ? 'selected' : 'unavailable' }}" data-payment-type="half"
                                   @unless($halfAllowed) aria-disabled="true" @endunless>
                                <input type="radio" name="payment_type" value="half" @if($halfAllowed) checked @else disabled @endif>
                                <div class="oc-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                                <div class="oc-label">Pay Half Now</div>
                                <div class="oc-sub">
                                    @if($halfAllowed)
                                        <span class="payment-amount" data-type="half">₱0.00</span>
                                    @else
                                        For orders ₱{{ number_format(\App\Models\Order::HALF_PAYMENT_MIN_TOTAL) }} and up
                                    @endif
                                </div>
                            </label>
                            <label class="option-card {{ $halfAllowed ? '' : 'selected' }}" data-payment-type="full">
                                <input type="radio" name="payment_type" value="full" @unless($halfAllowed) checked @endunless>
                                <div class="oc-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="oc-label">Pay in Full</div>
                                <div class="oc-sub"><span class="payment-amount" data-type="full">₱0.00</span></div>
                            </label>
                        </div>
                        <div class="hint" style="margin-top:.9rem">
                            You'll be shown a QR code to scan with your GCash app — payment is confirmed automatically. If it doesn't confirm right away, you can upload a screenshot as backup. Any remaining balance is settled at pickup.
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
                        <i class="fas fa-check-circle"></i> <span>Show GCash QR Code</span>
                    </button>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline" style="margin-top:.6rem">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@include('partials.clock-dial')
@include('partials.date-picker')
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/* Order type selector — Advance Order / Pick-Up both submit order_type=online;
   "schedule" (advance | pickup) decides which pick-up dates are allowed. */
const orderTypeCards = document.querySelectorAll('.option-card[data-type]');
const cartTotal = {{ (float) $cart->total }};
const halfPaymentPercent = {{ \App\Models\Order::HALF_PAYMENT_PERCENT }};
let schedule = 'advance';

orderTypeCards.forEach(card => {
    card.addEventListener('click', () => {
        orderTypeCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
        if (schedule !== card.dataset.schedule) {
            schedule = card.dataset.schedule;
            applySchedule();
        }
    });
});

/* Payment option selector — Pay Half Now / Pay in Full (Pay Half is unavailable below ₱500) */
const paymentCards = document.querySelectorAll('.option-card[data-payment-type]');

paymentCards.forEach(card => {
    card.addEventListener('click', (e) => {
        if (card.classList.contains('unavailable')) {
            e.preventDefault();
            showToast('Pay Half Now is only available for orders of ₱{{ number_format(\App\Models\Order::HALF_PAYMENT_MIN_TOTAL) }} and up.', 'error');
            return;
        }
        paymentCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
    });
});

const halfAmountEl = document.querySelector('.payment-amount[data-type="half"]');
if (halfAmountEl) halfAmountEl.textContent = '₱' + (cartTotal * halfPaymentPercent / 100).toFixed(2);
document.querySelector('.payment-amount[data-type="full"]').textContent = '₱' + cartTotal.toFixed(2);

/* Restaurant hours: pickup can only be scheduled between these hours (24h). */
const OPEN_HOUR = 11;  // 11:00 AM
const CLOSE_HOUR = 21; // 9:00 PM
const MIN_LEAD_MINUTES = 30; // kitchen needs at least this much notice

const pickupDateInput = document.getElementById('pickup_date');
const pickupTimeInput = document.getElementById('pickup_time');

/* Combine pickup date + time into a single datetime field before submit */
function syncPickupAt() {
    const date = pickupDateInput.value;
    const time = pickupTimeInput.value;
    document.getElementById('pickup_at').value = (date && time) ? `${date} ${time}:00` : '';
}

function isWithinBusinessHours(time) {
    if (! time) return true;
    const [hour, minute] = time.split(':').map(Number);
    const minutes = hour * 60 + minute;
    return minutes >= OPEN_HOUR * 60 && minutes <= CLOSE_HOUR * 60;
}

function meetsLeadTime(date, time) {
    if (! date || ! time) return true;
    const pickup = new Date(`${date}T${time}:00`);
    const minAllowed = new Date(Date.now() + MIN_LEAD_MINUTES * 60000);
    return pickup >= minAllowed;
}

/* Re-validates the current date + time together; clears and warns if either
   the business-hours or lead-time rule is broken. Returns whether it's valid. */
function validatePickupFields() {
    const date = pickupDateInput.value;
    const time = pickupTimeInput.value;

    if (time && ! isWithinBusinessHours(time)) {
        showToast("Please choose a pick-up time between 11:00 AM and 9:00 PM, our restaurant hours.", 'error');
        ClockDial.set('pickup_time', '');
        syncPickupAt();
        return false;
    }

    if (date && time && ! meetsLeadTime(date, time)) {
        showToast(`Please choose a pick-up time at least ${MIN_LEAD_MINUTES} minutes from now, so the kitchen has time to prepare your order.`, 'error');
        ClockDial.set('pickup_time', '');
        syncPickupAt();
        return false;
    }

    syncPickupAt();
    return true;
}

pickupDateInput.addEventListener('change', validatePickupFields);
pickupTimeInput.addEventListener('change', validatePickupFields);

/* Advance Order: pick a date (today … +{{ \App\Models\Order::ADVANCE_MAX_DAYS }} days) and a time.
   Pick-Up: the date is fixed to today, so only the time is shown. */
const TODAY = @json(now()->format('Y-m-d'));
const pickupRow = document.querySelector('.pickup-row');
const to12h = (hhmm) => { const [h, m] = hhmm.split(':').map(Number); return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`; };

// Earliest same-day pick-up: 30 minutes from now (+1 minute so it's still valid a moment later
// when the customer confirms), rounded up to 5 minutes, not before opening. Null after closing time.
function earliestTodayTime() {
    const t = new Date(Date.now() + (MIN_LEAD_MINUTES + 1) * 60000);
    let mins = Math.max(OPEN_HOUR * 60, Math.ceil((t.getHours() * 60 + t.getMinutes() + t.getSeconds() / 60) / 5) * 5);
    if (mins > CLOSE_HOUR * 60) return null;
    return String(Math.floor(mins / 60)).padStart(2, '0') + ':' + String(mins % 60).padStart(2, '0');
}

// When the pick-up date is today, grey out times that are already too soon (re-run every minute)
function refreshTimeLimit() {
    const isToday = pickupDateInput.value === TODAY;
    const earliest = isToday ? earliestTodayTime() : '11:00';
    pickupTimeInput.dataset.minTime = earliest || '23:59';   // after closing: every time greyed out

    if (schedule === 'pickup') {
        document.getElementById('pickupHint').textContent = earliest
            ? `Pick-up is for today. Choose a time from ${to12h(earliest)} to 9:00 PM.`
            : 'Same-day pick-up is closed for today. Please choose Advance Order to schedule another day.';
    }
}
pickupDateInput.addEventListener('change', refreshTimeLimit);

/* No. of Persons (Advance Order only): digits only, − / + step between 1 and the maximum */
const PARTY_SIZE_MAX = {{ \App\Models\Order::PARTY_SIZE_MAX }};
const partySizeInput = document.getElementById('party_size');
partySizeInput.addEventListener('input', () => {
    partySizeInput.value = partySizeInput.value.replace(/\D/g, '');
    if (+partySizeInput.value > PARTY_SIZE_MAX) partySizeInput.value = PARTY_SIZE_MAX;
});
document.querySelectorAll('.party-stepper button').forEach(btn => {
    btn.addEventListener('click', () => {
        const next = (parseInt(partySizeInput.value, 10) || 0) + Number(btn.dataset.step);
        partySizeInput.value = Math.min(PARTY_SIZE_MAX, Math.max(1, next));
    });
});

function applySchedule() {
    const timeOnly = schedule === 'pickup';
    pickupRow.classList.toggle('time-only', timeOnly);
    document.getElementById('partySizeField').style.display = timeOnly ? 'none' : '';
    document.getElementById('pickupLabel').textContent = timeOnly ? 'Pick-up Time (Today)' : 'Scheduled Pick-up Date & Time';

    if (timeOnly) {
        DatePick.set('pickup_date', TODAY);
        refreshTimeLimit();
    } else {
        const d = pickupDateInput.value;
        if (d && (d < pickupDateInput.dataset.min || d > pickupDateInput.dataset.max)) DatePick.set('pickup_date', '');
        refreshTimeLimit();
        document.getElementById('pickupHint').textContent =
            "Choose a day from today up to {{ \App\Models\Order::ADVANCE_MAX_DAYS }} days ahead. We're open 11:00 AM – 9:00 PM, and same-day orders need at least 30 minutes' notice.";
    }

    // Quietly drop a time that no longer fits the new choice
    const time = pickupTimeInput.value;
    if (time && (! isWithinBusinessHours(time) || ! meetsLeadTime(pickupDateInput.value, time))) ClockDial.set('pickup_time', '');
    syncPickupAt();
}

applySchedule();
setInterval(refreshTimeLimit, 60000);

/* Confirm & submit to PayMongo with duplicate-prevention */
const form = document.getElementById('checkoutForm');
const confirmBtn = document.getElementById('confirmOrderBtn');

form.addEventListener('submit', (e) => {
    e.preventDefault();

    if (! pickupDateInput.value || ! pickupTimeInput.value) {
        showToast(schedule === 'pickup' ? 'Please choose a pick-up time.' : 'Please choose a pick-up date and time.', 'error');
        return;
    }

    if (! validatePickupFields()) {
        return;
    }

    if (schedule === 'advance' && ! (parseInt(partySizeInput.value, 10) >= 1)) {
        showToast('Please enter the number of persons for your Advance Order.', 'error');
        partySizeInput.focus();
        return;
    }

    openConfirmModal({
        title: 'Place this order?',
        desc: 'Are you sure you want to place this order?',
        confirmText: 'Place Order',
        onConfirm: submitCheckout,
    });
});

async function submitCheckout() {
    closeConfirmModal();

    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spin"></span> <span>Generating QR Code...</span>';

    const orderType = form.querySelector('input[name="order_type"]:checked').value;
    const paymentType = form.querySelector('input[name="payment_type"]:checked').value;

    try {
        const res = await fetch('{{ route("checkout.qrph.create") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                order_type: orderType,
                schedule: schedule,
                party_size: schedule === 'advance' ? parseInt(partySizeInput.value, 10) : null,
                payment_type: paymentType,
                pickup_at: document.getElementById('pickup_at').value,
                special_instructions: form.querySelector('textarea[name="special_instructions"]').value,
            }),
        });
        const data = await res.json().catch(() => ({}));

        if (! res.ok) {
            showToast(data.message || 'Something went wrong. Please try again.', 'error');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Show GCash QR Code</span>';
            return;
        }

        window.location.href = data.redirect_url;
    } catch (err) {
        showToast('Something went wrong. Please try again.', 'error');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Show GCash QR Code</span>';
    }
}
</script>
@endsection
