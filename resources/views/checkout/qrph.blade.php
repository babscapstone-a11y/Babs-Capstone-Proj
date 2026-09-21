@extends('layouts.customer-app')
@section('title', "Scan to Pay – Bab's Resto")

@section('styles')
<style>
.qrph-wrap { max-width: 560px; margin: 0 auto; }

.page-title {
    font-size: 1.5rem; font-weight: 900; color: var(--dark);
    display: flex; align-items: center; gap: .6rem; margin-bottom: 1.5rem;
}
.page-title i { color: var(--primary); }

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

.qr-box { display: flex; flex-direction: column; align-items: center; text-align: center; }
.qr-box img {
    display: block; max-width: 240px; width: 100%; aspect-ratio: 1 / 1; object-fit: contain;
    border: 1.5px solid var(--border); border-radius: 12px; padding: 1rem; background: #fff;
    margin: 0 auto; transition: opacity .2s;
}
.qr-amount { font-size: 1.4rem; font-weight: 800; color: var(--primary); margin-top: .9rem; }
.qr-amount-label { font-size: .76rem; color: var(--muted); }
.qr-order-number { font-size: .8rem; color: var(--muted); margin-top: .3rem; }
.qr-waiting { font-size: .8rem; color: var(--muted); margin-top: .6rem; display: flex; align-items: center; justify-content: center; gap: .4rem; }

.hint-box {
    background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px;
    padding: .75rem .9rem; margin-top: 1.1rem; font-size: .84rem; color: #92400E; text-align: left;
}
.hint-box.error { background: rgba(220,38,38,.08); border-color: rgba(220,38,38,.25); color: #B91C1C; }

.field { margin-top: 1rem; }
.field label { display: block; font-size: .8rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
.field input {
    width: 100%; padding: .7rem .9rem; border: 1.5px solid var(--border); border-radius: 10px;
    font-family: inherit; font-size: .87rem; color: var(--text); transition: border-color .15s;
}
.field input:focus { outline: none; border-color: var(--primary); }
.field .hint { font-size: .74rem; color: var(--muted); margin-top: .3rem; }

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
.spin-muted { border: 2px solid rgba(107,114,128,.25); border-top-color: var(--muted); }
@keyframes spin { to { transform: rotate(360deg); } }

.submitted-box {
    text-align: center; padding: 1.5rem 1rem; color: #15803D;
}
.submitted-box i { font-size: 2.2rem; margin-bottom: .6rem; display: block; }
</style>
@endsection

@section('content')
<div class="page-wrap qrph-wrap" id="qrphWrap">
    <div class="page-title"><i class="fas fa-qrcode"></i> Scan to Pay</div>

@if($paymentProof->status === 'paid')
    <div class="card">
        <div class="card-body">
            <div class="submitted-box">
                <i class="fas fa-circle-check"></i>
                <div style="font-weight:700;font-size:1.05rem">Payment Confirmed!</div>
                <div style="font-size:.85rem;color:var(--muted);margin-top:.3rem">We automatically confirmed your GCash payment. Your order is now awaiting cashier review.</div>
            </div>
        </div>
    </div>
@else
    @php $qrIssue = $paymentProof->status === 'failed' ? 'failed' : ($paymentProof->isQrExpired() ? 'expired' : null); @endphp

    <div class="card" id="qrCard">
        <div class="card-header"><h2><i class="fas fa-mobile-screen-button"></i> GCash QR Code</h2></div>
        <div class="card-body">
            <div class="qr-box">
                <img src="{{ $paymentProof->paymongo_checkout_url }}" alt="Scan with GCash to pay" id="qrImage" style="{{ $qrIssue ? 'opacity:.35' : '' }}">
                <div class="qr-amount">₱{{ number_format($paymentProof->amount, 2) }}</div>
                <div class="qr-amount-label">{{ $paymentProof->payment_type_label }}</div>
                <div class="qr-order-number">Order #{{ $order->order_number }}</div>
                @unless($qrIssue)
                <div class="qr-waiting" id="qrWaiting"><span class="spin spin-muted"></span> Waiting for payment confirmation…</div>
                @endunless
            </div>
            <div id="qrStatusBanner">
                @if($qrIssue === 'failed')
                    <div class="hint-box error"><i class="fas fa-triangle-exclamation"></i> We couldn't confirm this payment automatically. If you already paid, upload a screenshot below.</div>
                @elseif($qrIssue === 'expired')
                    <div class="hint-box error"><i class="fas fa-clock"></i> This QR code has expired. If you already paid, upload a screenshot below — otherwise please contact us.</div>
                @else
                    <div class="hint-box"><i class="fas fa-circle-info"></i> Open your GCash app and scan this QR code to pay the exact amount above. We'll confirm it automatically — no need to stay on this page. If it doesn't confirm within a few minutes, upload a screenshot below as backup.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="card" id="proofCard">
        @if($paymentProof->proof_image)
            <div class="card-body">
                <div class="submitted-box">
                    <i class="fas fa-circle-check"></i>
                    <div style="font-weight:700">Payment screenshot submitted</div>
                    <div style="font-size:.85rem;color:var(--muted);margin-top:.3rem">We'll verify your payment and confirm this order shortly.</div>
                </div>
            </div>
        @else
            <div class="card-header"><h2><i class="fas fa-upload"></i> Upload Payment Proof (Backup)</h2></div>
            <div class="card-body">
                <form id="proofForm" enctype="multipart/form-data">
                    @csrf
                    <div class="field" style="margin-top:0">
                        <label for="reference_number">GCash Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" placeholder="e.g. 1234 5678 9012" required>
                        <div class="hint">Found in your GCash payment confirmation.</div>
                    </div>
                    <div class="field">
                        <label for="proof_image">Screenshot of Payment</label>
                        <input type="file" id="proof_image" name="proof_image" accept="image/*" required>
                        <div class="hint">Image files only, max 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitProofBtn" style="margin-top:1.1rem">
                        <i class="fas fa-check-circle"></i> <span>Submit Payment Proof</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
@endif

    <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> View Order
    </a>
</div>
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const proofForm = document.getElementById('proofForm');

if (proofForm) {
    proofForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('submitProofBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spin"></span> <span>Submitting...</span>';

        try {
            const res = await fetch('{{ route("checkout.qrph.proof", $order) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: new FormData(proofForm),
            });

            if (res.redirected) {
                window.location.href = res.url;
                return;
            }

            const data = await res.json().catch(() => ({}));
            showToast(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Something went wrong. Please try again.'), 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Submit Payment Proof</span>';
        } catch (err) {
            showToast('Something went wrong. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Submit Payment Proof</span>';
        }
    });
}

/* Auto-confirmation polling — mirrors the cashier billing screen's QR
   polling. Only runs while the payment is still awaiting confirmation. */
const initialStatus = @json($paymentProof->status);
let qrphPollTimer = null;

if (initialStatus === 'awaiting_payment') {
    qrphPollTimer = setInterval(checkQrphStatus, 3000);
}

async function checkQrphStatus() {
    try {
        const res = await fetch('{{ route("checkout.qrph.status", $order) }}', {
            headers: { Accept: 'application/json' },
        });
        if (! res.ok) return;
        const data = await res.json();

        if (data.status === 'paid') {
            clearInterval(qrphPollTimer);
            showQrphPaidState();
        } else if (data.status === 'failed' || data.status === 'expired') {
            clearInterval(qrphPollTimer);
            showQrphIssueState(data.status);
        }
    } catch (err) {
        // Transient network hiccup — just try again on the next tick.
    }
}

function showQrphPaidState() {
    document.getElementById('qrphWrap').innerHTML = `
        <div class="page-title"><i class="fas fa-qrcode"></i> Scan to Pay</div>
        <div class="card"><div class="card-body"><div class="submitted-box">
            <i class="fas fa-circle-check"></i>
            <div style="font-weight:700;font-size:1.05rem">Payment Confirmed!</div>
            <div style="font-size:.85rem;color:var(--muted);margin-top:.3rem">We automatically confirmed your GCash payment. Your order is now awaiting cashier review.</div>
        </div></div></div>
        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> View Order</a>
    `;
    showToast('Payment confirmed!', 'success');
}

function showQrphIssueState(status) {
    const banner = document.getElementById('qrStatusBanner');
    const waiting = document.getElementById('qrWaiting');
    const img = document.getElementById('qrImage');
    if (waiting) waiting.remove();
    if (img) img.style.opacity = '.35';
    if (banner) {
        const msg = status === 'expired'
            ? 'This QR code has expired. If you already paid, upload a screenshot below — otherwise please contact us.'
            : "We couldn't confirm this payment automatically. If you already paid, upload a screenshot below.";
        banner.innerHTML = `<div class="hint-box error"><i class="fas fa-triangle-exclamation"></i> ${msg}</div>`;
    }
}
</script>
@endsection
