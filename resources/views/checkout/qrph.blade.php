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

.qr-box { text-align: center; }
.qr-box img {
    max-width: 240px; width: 100%; border: 1.5px solid var(--border);
    border-radius: 12px; padding: .6rem; background: #fff;
}
.qr-amount { font-size: 1.4rem; font-weight: 800; color: var(--primary); margin-top: .9rem; }
.qr-amount-label { font-size: .76rem; color: var(--muted); }
.qr-order-number { font-size: .8rem; color: var(--muted); margin-top: .3rem; }

.hint-box {
    background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 10px;
    padding: .75rem .9rem; margin-top: 1.1rem; font-size: .84rem; color: #92400E; text-align: left;
}

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
@keyframes spin { to { transform: rotate(360deg); } }

.submitted-box {
    text-align: center; padding: 1.5rem 1rem; color: #15803D;
}
.submitted-box i { font-size: 2.2rem; margin-bottom: .6rem; display: block; }
</style>
@endsection

@section('content')
<div class="page-wrap qrph-wrap">
    <div class="page-title"><i class="fas fa-qrcode"></i> Scan to Pay</div>

    <div class="card">
        <div class="card-header"><h2><i class="fas fa-mobile-screen-button"></i> GCash QR Code</h2></div>
        <div class="card-body">
            <div class="qr-box">
                <img src="{{ $paymentProof->paymongo_checkout_url }}" alt="Scan with GCash to pay">
                <div class="qr-amount">₱{{ number_format($paymentProof->amount, 2) }}</div>
                <div class="qr-amount-label">{{ $paymentProof->payment_type_label }}</div>
                <div class="qr-order-number">Order #{{ $order->order_number }}</div>
            </div>
            <div class="hint-box">
                <i class="fas fa-circle-info"></i> Open your GCash app, scan this QR code, and pay the exact amount above. Then take a screenshot of the payment confirmation and upload it below — our cashier will verify it and confirm your order shortly.
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
            <div class="card-header"><h2><i class="fas fa-upload"></i> Upload Payment Proof</h2></div>
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
</script>
@endsection
