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
        .r-summary .r-row.vat { font-size: .76rem; padding-left: .75rem; }

        .r-footer { text-align: center; margin-top: 1.1rem; font-size: .84rem; font-weight: 600; color: var(--primary); }
        .r-footer-sub { text-align: center; font-size: .72rem; color: var(--muted); margin-top: .2rem; }

        /* Shown inside the billing page's receipt pop-up (?embed=1): the paper only, its buttons are on the pop-up */
        body.embedded { background: #fff; padding: 0; overflow: hidden; }
        body.embedded .receipt-actions { display: none; }
        body.embedded .receipt-paper { box-shadow: none; }

        /* …and compact on screen so it fits the pop-up without scrolling (the printout keeps the full size) */
        @media screen {
            body.embedded .receipt-paper { padding: .9rem 1.25rem; }
            body.embedded .r-logo { width: 34px; height: 34px; font-size: 11px; margin-bottom: .35rem; border-radius: 8px; }
            body.embedded .r-name { font-size: 1rem; }
            body.embedded .r-sub { font-size: .7rem; margin-top: .05rem; }
            body.embedded .r-divider { margin: .55rem 0; }
            body.embedded .r-row { font-size: .75rem; padding: .08rem 0; }
            body.embedded .r-items .item-line { font-size: .76rem; padding: .18rem 0; }
            body.embedded .r-items .item-qty-price { font-size: .7rem; }
            body.embedded .r-summary .r-row.grand { font-size: .9rem; margin-top: .25rem; padding-top: .35rem; }
            body.embedded .r-summary .r-row.vat { font-size: .7rem; }
            body.embedded .r-footer { margin-top: .6rem; font-size: .76rem; }
            body.embedded .r-footer-sub { font-size: .66rem; }
        }

        @media print {
            @page { margin: 0; }
            body { background: #fff; padding: 0; }
            .receipt-actions { display: none; }
            .receipt-paper { box-shadow: none; border-radius: 0; padding: 0; }
        }

        /* ── Thermal printer layout (58mm roll, 48mm / 384-dot printable width, black only) ──
           Switched on just before printing (see the script below) so the on-screen preview keeps its look.
           Pure black, no grey/colour (thermal heads dither them into faint dots), no small text below 7pt. */
        html.thermal-print { zoom: 1 !important; }   /* undo the pop-up's fit-to-screen shrink */
        html.thermal-print body { background: #fff; padding: 0; margin: 0; overflow: visible; color: #000;
            font-family: Arial, Helvetica, sans-serif; }
        html.thermal-print .receipt-actions,
        html.thermal-print .r-logo { display: none; }
        html.thermal-print .receipt-wrap { max-width: none; width: 48mm; margin: 0 auto; }
        html.thermal-print .receipt-paper { box-shadow: none; border-radius: 0; padding: 2mm 0 8mm; }  /* 8mm feed before the tear/cut */
        html.thermal-print .r-name { font-size: 12pt; font-weight: 700; color: #000; }
        html.thermal-print .r-sub { font-size: 7pt; color: #000; margin-top: .5mm; line-height: 1.25; }
        html.thermal-print .r-divider { border-top: 1px dashed #000; margin: 2mm 0; }
        html.thermal-print .r-row { font-size: 8pt; padding: .3mm 0; flex-wrap: wrap; gap: 0 1.5mm; line-height: 1.25; }
        html.thermal-print .r-row .label { color: #000; }
        html.thermal-print .r-row .value { font-weight: 700; margin-left: auto; }   /* a long value drops to its own line, right-aligned */
        html.thermal-print .r-items .item-line { font-size: 8pt; padding: .6mm 0; gap: 1.5mm; line-height: 1.25; }
        html.thermal-print .r-items .item-line > div:last-child { white-space: nowrap; font-weight: 700; }
        html.thermal-print .r-items .item-name { font-weight: 700; }
        html.thermal-print .r-items .item-qty-price { color: #000; font-size: 7.5pt; }
        html.thermal-print .r-summary .r-row.grand { font-size: 11pt; font-weight: 700; border-top: 1px solid #000; margin-top: 1mm; padding-top: 1mm; }
        html.thermal-print .r-summary .r-row .neg { color: #000; }
        html.thermal-print .r-summary .r-row.vat { font-size: 7.5pt; padding-left: 2mm; }
        html.thermal-print .r-footer { color: #000; font-size: 8pt; font-weight: 700; margin-top: 3mm; }
        html.thermal-print .r-footer-sub { color: #000; font-size: 7pt; }
    </style>
</head>
<body class="{{ request()->boolean('embed') ? 'embedded' : '' }}">

    <div class="receipt-wrap">
        <div class="receipt-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
            <button type="button" class="btn btn-outline" onclick="window.print()"><i class="fas fa-file-pdf"></i> Save as PDF</button>
        </div>

        <div class="receipt-paper">
            <div class="r-center">
                <div class="r-logo">BR</div>
                <div class="r-name">BAB'S RESTO</div>
                <div class="r-sub">Aguirre Subd, Purok Roamsciville, Agan Rd., Brgy. Sta. Cruz, Koronadal, Philippines, 9506</div>
                <div class="r-sub">Tel: 0910 741 2539</div>
            </div>

            <hr class="r-divider">

            <div class="r-row"><span class="label">Receipt No.</span><span class="value">{{ $payment->receipt_number }}</span></div>
            <div class="r-row"><span class="label">Trans. No.</span><span class="value">{{ $payment->transaction_number }}</span></div>
            <div class="r-row"><span class="label">Order No.</span><span class="value">#{{ $payment->order?->order_number }}</span></div>
            <div class="r-row"><span class="label">Date &amp; Time</span><span class="value">{{ $payment->payment_date?->format('M d, Y h:i A') }}</span></div>
            <div class="r-row"><span class="label">Cashier</span><span class="value">{{ $payment->cashier?->staff?->full_name ?? $payment->cashier?->email ?? '—' }}</span></div>
            <div class="r-row"><span class="label">Customer</span><span class="value">{{ $payment->order?->customer_name ?? 'Walk-in' }}</span></div>
            <div class="r-row"><span class="label">Order Type</span>
                <span class="value">
                    {{ $payment->order?->order_type_label }}
                    @if($payment->order?->walkInOrder?->table_number) · Table {{ $payment->order->walkInOrder->table_number }} @endif
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
                @if($payment->invoice->isVatExempt())
                    {{-- Senior Citizen / PWD: VAT is removed before the discount --}}
                    <div class="r-row">
                        <span class="label">Less VAT ({{ \App\Models\Invoice::VAT_RATE * 100 }}%)</span>
                        <span class="value neg">- ₱{{ number_format($payment->invoice->vat_exemption, 2) }}</span>
                    </div>
                @endif
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
                {{-- Prices already include 12% VAT; this shows the split (none on a Senior/PWD sale) --}}
                @if($payment->invoice->isVatExempt())
                    <div class="r-row vat"><span class="label">VAT-Exempt Sales</span><span class="value">₱{{ number_format($payment->invoice->vat_exempt_sales, 2) }}</span></div>
                @else
                    <div class="r-row vat"><span class="label">VATable Sales</span><span class="value">₱{{ number_format($payment->invoice->vatable_sales, 2) }}</span></div>
                @endif
                <div class="r-row vat"><span class="label">VAT ({{ \App\Models\Invoice::VAT_RATE * 100 }}%)</span><span class="value">₱{{ number_format($payment->invoice->vat_amount, 2) }}</span></div>
                @php $paidOnline = $payment->order?->amountPaidOnline() ?? 0; @endphp
                @if($paidOnline > 0)
                    <div class="r-row"><span class="label">Paid Online (GCash)</span><span class="value">₱{{ number_format($paidOnline, 2) }}</span></div>
                @endif
                @if($paidOnline > 0 && (float) $payment->amount_paid <= 0)
                    {{-- Fully paid online: nothing was collected at the counter --}}
                    <div class="r-row"><span class="label">Balance Due</span><span class="value">₱0.00</span></div>
                @else
                    <div class="r-row"><span class="label">Amount Received</span><span class="value">₱{{ number_format($payment->amount_received, 2) }}</span></div>
                    <div class="r-row"><span class="label">Change</span><span class="value">₱{{ number_format($payment->change_amount, 2) }}</span></div>
                @endif
                <div class="r-row"><span class="label">Payment Method</span><span class="value">{{ $payment->modeOfPayment?->method_name }}</span></div>
            </div>

            <div class="r-footer">Thank you for dining with BAB'S RESTO!</div>
            <div class="r-footer-sub">This receipt was generated electronically.</div>
        </div>
    </div>

    <script>
        /* Prints on the 58mm thermal roll: right before the print dialog opens, switch to the thermal
           layout, measure how long the receipt is, and set the page to exactly 58mm × that length —
           so the printer feeds only as much paper as the receipt needs (and "Save as PDF" gives a
           receipt-sized PDF). Everything goes back to the normal look once the dialog closes. */
        (function () {
            const PAPER_WIDTH_MM = 58;
            const PX_TO_MM = 25.4 / 96;

            window.addEventListener('beforeprint', () => {
                document.documentElement.classList.add('thermal-print');
                const paper = document.querySelector('.receipt-paper');
                const lengthMm = Math.ceil(paper.getBoundingClientRect().height * PX_TO_MM) + 2;

                let style = document.getElementById('thermalPage');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'thermalPage';
                    document.head.appendChild(style);
                }
                style.textContent = `@page { size: ${PAPER_WIDTH_MM}mm ${lengthMm}mm; margin: 0; }`;
            });

            window.addEventListener('afterprint', () => {
                document.documentElement.classList.remove('thermal-print');
            });
        })();

        /* For printing straight to the thermal printer over USB (public/js/thermal-printer.js):
           draws the receipt, in the thermal layout, as a picture exactly as wide as the printer's
           print head (384 dots), then puts the page back to its normal look. */
        window.renderThermalReceipt = async function (dotsWide) {
            const root = document.documentElement;
            root.classList.add('thermal-print');
            try {
                await document.fonts.ready;
                const paper = document.querySelector('.receipt-paper');
                return await html2canvas(paper, {
                    scale: dotsWide / paper.getBoundingClientRect().width,   // 48mm of receipt → 384 dots
                    backgroundColor: '#ffffff',
                    logging: false,
                    windowWidth: root.scrollWidth,
                    windowHeight: root.scrollHeight,
                });
            } finally {
                root.classList.remove('thermal-print');
            }
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>

</body>
</html>
