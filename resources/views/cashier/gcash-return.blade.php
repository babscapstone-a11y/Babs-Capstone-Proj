<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Status – Bab's Resto</title>
    <style>
        :root { --primary: #DC2626; --dark: #111827; --muted: #6B7280; --border: #E5E7EB; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: #FAFAFA; color: var(--dark); padding: 1.5rem;
        }
        .card {
            max-width: 380px; width: 100%; background: #fff; border-radius: 18px;
            border: 1px solid var(--border); box-shadow: 0 4px 24px rgba(0,0,0,.06);
            padding: 2.25rem 1.75rem; text-align: center;
        }
        .brand { font-weight: 900; letter-spacing: .04em; color: var(--primary); font-size: .8rem; margin-bottom: 1.5rem; }
        .icon {
            width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 1.1rem;
            display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff;
        }
        .icon.paid { background: #16A34A; }
        .icon.failed { background: var(--primary); }
        .icon.pending { background: #F59E0B; }
        h1 { font-size: 1.15rem; margin: 0 0 .5rem; }
        p { color: var(--muted); font-size: .92rem; line-height: 1.5; margin: 0; }
        .amount { font-weight: 800; color: var(--dark); }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">BAB'S RESTO</div>
        @if($intent->status === 'paid')
            <div class="icon paid">&#10003;</div>
            <h1>Payment Successful</h1>
            <p>Your GCash payment of <span class="amount">₱{{ number_format($intent->grand_total, 2) }}</span> for Order #{{ $intent->order->order_number }} was received. You may return this device to the cashier.</p>
        @elseif($intent->status === 'failed' || $intent->status === 'cancelled')
            <div class="icon failed">&#10005;</div>
            <h1>Payment Not Completed</h1>
            <p>We couldn't confirm this GCash payment. Please return this device to the cashier to try again.</p>
        @else
            <div class="icon pending">&#8635;</div>
            <h1>Still Processing</h1>
            <p>We're confirming your payment. Please return this device to the cashier, who will see the result on their screen shortly.</p>
        @endif
    </div>
</body>
</html>
