<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $po->po_number }} – BAB'S RESTO Purchase Order</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Poppins', system-ui, sans-serif; background: #F8FAFC; color: #111827; font-size: 13px; }

    /* Screen-only elements */
    .screen-only { display: block; }
    .print-only { display: none; }

    .screen-toolbar {
        background: #111827; color: #fff; padding: .75rem 2rem;
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 100;
    }
    .screen-toolbar .brand { font-size: .9rem; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
    .screen-toolbar .brand-badge { width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg,#DC2626,#F97316); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .75rem; }
    .screen-toolbar .actions { display: flex; gap: .65rem; }
    .tbtn { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem 1rem; border-radius: 8px; font-size: .8rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; transition: all .18s; text-decoration: none; }
    .tbtn-primary { background: #DC2626; color: #fff; }
    .tbtn-primary:hover { background: #B91C1C; }
    .tbtn-outline { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; }
    .tbtn-outline:hover { background: rgba(255,255,255,.18); }

    .doc-wrapper { max-width: 860px; margin: 2rem auto; padding: 0 1.5rem; }

    /* Document / Paper */
    .document {
        background: #fff;
        border-radius: 12px;
        border: 1px solid rgba(17,24,39,0.1);
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        padding: 48px 56px;
        margin-bottom: 2rem;
    }

    /* Header */
    .doc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 2px solid #DC2626; }
    .resto-brand { display: flex; align-items: center; gap: .85rem; }
    .brand-logo { width: 54px; height: 54px; border-radius: 12px; background: linear-gradient(135deg,#DC2626,#F97316); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 1.2rem; flex-shrink: 0; }
    .brand-info .name { font-size: 1.25rem; font-weight: 900; color: #111827; letter-spacing: -.01em; }
    .brand-info .address { font-size: .75rem; color: #6B7280; margin-top: .15rem; line-height: 1.5; }
    .po-meta { text-align: right; }
    .po-meta .doc-type { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #DC2626; margin-bottom: .35rem; }
    .po-meta .po-num { font-size: 1.4rem; font-weight: 900; color: #111827; letter-spacing: -.01em; }
    .po-meta .po-date { font-size: .78rem; color: #6B7280; margin-top: .25rem; }
    .po-meta .status-pill { display: inline-flex; align-items: center; gap: .3rem; padding: .25rem .7rem; border-radius: 50px; font-size: .68rem; font-weight: 700; text-transform: uppercase; margin-top: .35rem; }
    .status-finalized { background: #DCFCE7; color: #15803D; }
    .status-draft { background: #FEF3C7; color: #B45309; }
    .status-stocked { background: #DBEAFE; color: #1D4ED8; }

    /* Details row */
    .details-row { display: flex; gap: 2rem; margin-bottom: 1.75rem; padding: 1rem 1.25rem; background: #F8FAFC; border-radius: 10px; border: 1px solid rgba(17,24,39,0.07); flex-wrap: wrap; }
    .detail-item .dl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9CA3AF; margin-bottom: .2rem; }
    .detail-item .dv { font-size: .88rem; font-weight: 700; color: #111827; }

    /* Section headers */
    .section-title { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #6B7280; margin-bottom: .65rem; display: flex; align-items: center; gap: .5rem; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: rgba(17,24,39,0.08); }

    /* Table */
    .po-tbl { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: .82rem; }
    .po-tbl thead tr { background: #111827; }
    .po-tbl th { padding: .55rem .85rem; text-align: left; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,0.75); }
    .po-tbl th:last-child { text-align: right; }
    .po-tbl td { padding: .75rem .85rem; border-bottom: 1px solid rgba(17,24,39,0.07); color: #111827; vertical-align: middle; }
    .po-tbl td:last-child { text-align: right; font-weight: 700; color: #DC2626; }
    .po-tbl tr:hover td { background: #FAFAFA; }
    .po-tbl tfoot td { border-top: 2px solid #111827; font-weight: 700; padding-top: .65rem; }
    .type-pill-rtc { background: #EFF6FF; color: #1D4ED8; padding: .15rem .45rem; border-radius: 5px; font-size: .62rem; font-weight: 700; text-transform: uppercase; }
    .type-pill-bev { background: #F5F3FF; color: #6D28D9; padding: .15rem .45rem; border-radius: 5px; font-size: .62rem; font-weight: 700; text-transform: uppercase; }
    .status-low { background: #FEF3C7; color: #B45309; padding: .15rem .45rem; border-radius: 5px; font-size: .62rem; font-weight: 700; }
    .status-out { background: #FEE2E2; color: #B91C1C; padding: .15rem .45rem; border-radius: 5px; font-size: .62rem; font-weight: 700; }

    /* Blank line to hand-write the actual purchase on a draft printout */
    .write-in { display: inline-block; min-width: 90px; border-bottom: 1px solid #9CA3AF; height: 1.2em; text-align: left; color: #9CA3AF; font-weight: 400; }
    .grand-total { display: flex; justify-content: flex-end; align-items: center; gap: 1.25rem; padding: .85rem 1rem; background: #F8FAFC; border-radius: 8px; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6B7280; }
    .grand-total strong { font-size: 1.1rem; color: #DC2626; letter-spacing: 0; }

    /* Notes */
    .notes-section { background: #FFFBEB; border-left: 3px solid #F59E0B; border-radius: 0 8px 8px 0; padding: .85rem 1rem; margin-bottom: 1.5rem; font-size: .82rem; color: #111827; line-height: 1.6; }
    .notes-section .notes-lbl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9CA3AF; margin-bottom: .35rem; }

    /* Signature section */
    .sig-section { display: flex; gap: 2.5rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(17,24,39,0.1); }
    .sig-block { flex: 1; }
    .sig-line { border-bottom: 1.5px solid #111827; margin-bottom: .4rem; height: 44px; }
    .sig-label { font-size: .72rem; font-weight: 600; color: #6B7280; }
    .sig-sublabel { font-size: .65rem; color: #9CA3AF; margin-top: .1rem; }

    /* Footer */
    .doc-footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px dashed rgba(17,24,39,0.12); text-align: center; font-size: .68rem; color: #9CA3AF; line-height: 1.7; }

    /* Print styles */
    @media print {
        .screen-only, .screen-toolbar { display: none !important; }
        .print-only { display: block !important; }
        body { background: #fff; font-size: 11px; }
        .doc-wrapper { max-width: 100%; margin: 0; padding: 0; }
        .document { border-radius: 0; border: none; box-shadow: none; padding: 24px 32px; margin: 0; }
        @page { margin: 1cm; size: A4; }
        .po-tbl tr:hover td { background: transparent; }
    }
    </style>
</head>
<body>

{{-- Screen-only toolbar --}}
<div class="screen-only screen-toolbar">
    <div class="brand">
        <div class="brand-badge">BR</div>
        <span>BAB'S RESTO &nbsp;/ Purchase Order {{ $po->po_number }}</span>
    </div>
    <div class="actions">
        <a href="{{ route('purchase-orders.show', $po) }}" class="tbtn tbtn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="tbtn tbtn-primary"><i class="fas fa-print"></i> Print / Save as PDF</button>
    </div>
</div>

<div class="doc-wrapper screen-only" style="margin-bottom:2.5rem;text-align:center;font-size:.8rem;color:#6B7280">
    <i class="fas fa-info-circle"></i> Use <strong>Ctrl+P</strong> (or ⌘+P on Mac) and select "Save as PDF" to download a PDF copy.
</div>

{{-- The Printable Document --}}
<div class="doc-wrapper">
    <div class="document">

        {{-- Letterhead --}}
        <div class="doc-header">
            <div class="resto-brand">
                <div class="brand-logo">BR</div>
                <div class="brand-info">
                    <div class="name">BAB'S RESTO</div>
                    <div class="address">
                        Restaurant & Catering Services<br>
                        Contact: (032) 123-4567
                    </div>
                </div>
            </div>
            <div class="po-meta">
                <div class="doc-type">Purchase Order</div>
                <div class="po-num">{{ $po->po_number }}</div>
                <div class="po-date">{{ $po->created_at->format('F d, Y') }}</div>
                <div>
                    <span class="status-pill {{ $po->isStockedIn() ? 'status-stocked' : ($po->isFinalized() ? 'status-finalized' : 'status-draft') }}">
                        {{ $po->status_label }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="details-row">
            <div class="detail-item"><div class="dl">PO Number</div><div class="dv">{{ $po->po_number }}</div></div>
            <div class="detail-item"><div class="dl">Date Prepared</div><div class="dv">{{ $po->created_at->format('M d, Y') }}</div></div>
            <div class="detail-item"><div class="dl">Prepared By</div><div class="dv">{{ $po->preparedBy?->name ?? 'Administrator' }}</div></div>
            <div class="detail-item"><div class="dl">Total Items</div><div class="dv">{{ $po->items->count() }}</div></div>
            @if($po->isFinalized())
            <div class="detail-item"><div class="dl">Finalized On</div><div class="dv">{{ $po->finalized_at?->format('M d, Y') }}</div></div>
            @endif
        </div>

        {{-- Notes --}}
        @if($po->notes)
        <div class="notes-section">
            <div class="notes-lbl">Notes / Instructions</div>
            {{ $po->notes }}
        </div>
        @endif

        {{-- Items: drafts print blank "Qty Bought / Amount Paid" columns to fill in while buying --}}
        @foreach([
            ['type' => 'rtc',      'label' => 'RTC Raw Meat', 'decimals' => 2],
            ['type' => 'beverage', 'label' => 'Beverages',    'decimals' => 0],
        ] as $section)
        @php $sectionItems = $po->items->where('item_type', $section['type'])->values(); @endphp
        @if($sectionItems->isNotEmpty())
        <div class="section-title" @if(! $loop->first) style="margin-top:1.5rem" @endif>{{ $section['label'] }}</div>
        <table class="po-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Current Stock</th>
                    <th>Qty to Purchase</th>
                    <th>Qty Bought</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionItems as $i => $item)
                <tr>
                    <td style="color:#9CA3AF;font-size:.78rem">{{ $i+1 }}</td>
                    <td><strong>{{ $item->item_name }}</strong>@if($item->category)<div style="font-size:.7rem;color:#9CA3AF">{{ $item->category }}</div>@endif</td>
                    <td style="color:#6B7280">{{ number_format($item->current_stock, $section['decimals']) }} {{ $item->unit }}</td>
                    <td style="font-weight:700">{{ number_format($item->quantity_to_purchase, $section['decimals']) }} {{ $item->unit }}</td>
                    @if($item->quantity_received !== null)
                    <td>{{ number_format($item->quantity_received, $section['decimals']) }} {{ $item->unit }}</td>
                    <td>{{ $item->amount_paid !== null ? '₱' . number_format($item->amount_paid, 2) : '—' }}</td>
                    @else
                    <td><span class="write-in"></span></td>
                    <td><span class="write-in">₱</span></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right;font-size:.72rem;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:.06em">{{ $sectionItems->count() }} item(s) &nbsp;·&nbsp; Subtotal Paid</td>
                    <td>@if($sectionItems->whereNotNull('amount_paid')->isNotEmpty())₱{{ number_format($sectionItems->sum('amount_paid'), 2) }}@else<span class="write-in">₱</span>@endif</td>
                </tr>
            </tfoot>
        </table>
        @endif
        @endforeach

        <div class="grand-total">
            <span>Total Amount Paid</span>
            <strong>@if($po->items->whereNotNull('amount_paid')->isNotEmpty())₱{{ number_format($po->total_amount_paid, 2) }}@else<span class="write-in" style="min-width:140px">₱</span>@endif</strong>
        </div>

        {{-- Signature section --}}
        <div class="sig-section">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Prepared By</div>
                <div class="sig-sublabel">{{ $po->preparedBy?->name ?? 'Administrator' }} &nbsp;/&nbsp; Administrator</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Approved By</div>
                <div class="sig-sublabel">Restaurant Manager / Owner</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">Received By (Supplier)</div>
                <div class="sig-sublabel">Supplier Representative &nbsp;/&nbsp; Date</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="doc-footer">
            <strong>BAB'S RESTO</strong> — Purchase Order {{ $po->po_number }}<br>
            This document was generated on {{ now()->format('F d, Y h:i A') }}<br>
            @if($po->isStockedIn())
            Items on this purchase order were stocked in on {{ $po->stocked_in_at?->format('F d, Y') }}.
            @else
            This purchase order is for reference only. Inventory quantities will be updated after actual stock-in.
            @endif
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
<script>
// Auto-trigger print dialog when page loads (optional — uncomment to enable)
// window.addEventListener('load', () => setTimeout(() => window.print(), 500));
</script>
</body>
</html>
