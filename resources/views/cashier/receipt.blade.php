<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number }} – BAB'S RESTO</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">

    <style>
        :root { --primary: #DC2626; --dark: #111827; --muted: #6B7280; --border: rgba(17,24,39,0.1); }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', system-ui, sans-serif; background: #F1F5F9; margin: 0; padding: 2rem 1rem; color: var(--dark); }

        .receipt-wrap { max-width: 420px; margin: 0 auto; }
        .receipt-actions { display: flex; gap: .6rem; margin-bottom: 1rem; }
        .btn {
            flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            padding: .65rem; border-radius: 10px; font-size: .86rem; font-weight: 600; font-family: inherit;
            cursor: pointer; border: none; text-decoration: none;
        }
        .btn-primary { background: linear-gradient(90deg, var(--primary), #F97316); color: #fff; }
        .btn-outline { background: #fff; border: 1.5px solid var(--border); color: var(--dark); }

        .receipt-paper {
            background: #fff; border-radius: 14px; padding: 1.75rem 1.5rem;
            box-shadow: 0 8px 30px rgba(17,24,39,0.08);
        }
        .r-center { text-align: center; }
        .r-logo {
            width: 46px; height: 46px; border-radius: 10px; margin: 0 auto .6rem;
            background: linear-gradient(135deg, var(--primary), #F97316);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 14px;
        }
        .r-name { font-weight: 800; font-size: 1.15rem; letter-spacing: .02em; }
        .r-sub { font-size: .78rem; color: var(--muted); margin-top: .15rem; }

        .r-divider { border: none; border-top: 1.5px dashed var(--border); margin: 1rem 0; }

        .r-row { display: flex; justify-content: space-between; font-size: .82rem; padding: .18rem 0; }
        .r-row .label { color: var(--muted); }
        .r-row .value { font-weight: 600; text-align: right; }

        .r-items .item-line { display: flex; justify-content: space-between; font-size: .84rem; padding: .35rem 0; }
        .r-items .item-name { font-weight: 600; }
        .r-items .item-qty-price { color: var(--muted); font-size: .76rem; }

        .r-summary .r-row.grand { font-size: 1rem; font-weight: 800; border-top: 1.5px solid var(--border); margin-top: .4rem; padding-top: .55rem; }
        .r-summary .r-row .neg { color: var(--primary); }

        .r-footer { text-align: center; margin-top: 1.1rem; font-size: .84rem; font-weight: 600; color: var(--primary); }
        .r-footer-sub { text-align: center; font-size: .72rem; color: var(--muted); margin-top: .2rem; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-actions { display: none; }
            .receipt-paper { box-shadow: none; border-radius: 0; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="receipt-wrap">
        <div class="receipt-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
            <button type="button" class="btn btn-outline" onclick="window.print()"><i class="fas fa-file-pdf"></i> Save as PDF</button>
        </div>

        <div class="receipt-paper">
            <div class="r-center">
                <div class="r-logo">BR</div>
                <div class="r-name">BAB'S RESTO</div>
                <div class="r-sub">123 Sampaguita St., Brgy. San Isidro, Quezon City</div>
                <div class="r-sub">Tel: (02) 8123-4567</div>
            </div>

            <hr class="r-divider">

            <div class="r-row"><span class="label">Receipt No.</span><span class="value">{{ $payment->receipt_number }}</span></div>
            <div class="r-row"><span class="label">Transaction No.</span><span class="value">{{ $payment->transaction_number }}</span></div>
            <div class="r-row"><span class="label">Order No.</span><span class="value">#{{ $payment->order?->order_number }}</span></div>
            <div class="r-row"><span class="label">Date &amp; Time</span><span class="value">{{ $payment->payment_date?->format('M d, Y h:i A') }}</span></div>
            <div class="r-row"><span class="label">Cashier</span><span class="value">{{ $payment->cashier?->staff?->full_name ?? $payment->cashier?->email ?? '—' }}</span></div>
            <div class="r-row"><span class="label">Customer</span><span class="value">{{ $payment->order?->customer_name ?? 'Walk-in' }}</span></div>
            <div class="r-row"><span class="label">Order Type</span>
                <span class="value">
                    {{ $payment->order?->order_type_label }}
                    @if($payment->order?->dineInOrder?->table_number) · Table {{ $payment->order->dineInOrder->table_number }} @endif
                </span>
            </div>

            <hr class="r-divider">

            <div class="r-items">
                @foreach($payment->order?->details ?? [] as $item)
                    <div class="item-line">
                        <div>
                            <div class="item-name">{{ $item->item_name }}</div>
                            <div class="item-qty-price">{{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</div>
                        </div>
                        <div>₱{{ number_format($item->subtotal, 2) }}</div>
                    </div>
                @endforeach
            </div>

            <hr class="r-divider">

            <div class="r-summary">
                <div class="r-row"><span class="label">Subtotal</span><span class="value">₱{{ number_format($payment->invoice->subtotal, 2) }}</span></div>
                @if($payment->invoice->discount_amount > 0)
                    <div class="r-row">
                        <span class="label">Discount{{ $payment->invoice->discount ? ' (' . $payment->invoice->discount->discount_name . ')' : '' }}</span>
                        <span class="value neg">- ₱{{ number_format($payment->invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                @if($payment->invoice->service_charge > 0)
                    <div class="r-row"><span class="label">Service Charge</span><span class="value">₱{{ number_format($payment->invoice->service_charge, 2) }}</span></div>
                @endif
                <div class="r-row grand"><span class="label">Grand Total</span><span class="value">₱{{ number_format($payment->invoice->final_total, 2) }}</span></div>
                <div class="r-row"><span class="label">Amount Received</span><span class="value">₱{{ number_format($payment->amount_received, 2) }}</span></div>
                <div class="r-row"><span class="label">Change</span><span class="value">₱{{ number_format($payment->change_amount, 2) }}</span></div>
                <div class="r-row"><span class="label">Payment Method</span><span class="value">{{ $payment->modeOfPayment?->method_name }}</span></div>
            </div>

            <div class="r-footer">Thank you for dining with BAB'S RESTO!</div>
            <div class="r-footer-sub">This receipt was generated electronically.</div>
        </div>
    </div>

</body>
</html>
