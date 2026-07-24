@extends('layouts.cashier')

@section('title', 'Payment Dashboard')

@section('styles')
<style>
    .summary-row {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .summary-card {
        background: var(--white); border-radius: 14px; padding: 1.1rem 1.3rem;
        border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(17,24,39,0.05);
        display: flex; align-items: center; gap: .9rem;
    }
    .summary-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .summary-icon.pending   { background: rgba(245,158,11,0.14); color: var(--accent); }
    .summary-icon.completed { background: rgba(37,99,235,0.14);  color: #2563EB; }
    .summary-icon.sales     { background: rgba(22,163,74,0.14);  color: #16A34A; }
    .summary-icon.revenue   { background: rgba(220,38,38,0.12);  color: var(--primary); }
    .summary-count { font-size: 1.6rem; font-weight: 800; color: var(--dark); line-height: 1; }
    .summary-label  { font-size: .78rem; color: var(--muted); font-weight: 600; margin-top: .2rem; }

    .quick-row { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; align-items: stretch; }
    .quick-search-form { display: flex; gap: .6rem; }
    .quick-search-input {
        flex: 1; border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px;
        padding: .7rem 1rem; font-size: .9rem; font-family: inherit; outline: none;
        transition: all .2s;
    }
    .quick-search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,38,38,0.08); }
    .quick-payment-box {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .6rem; text-align: center;
    }
    .quick-payment-box i { font-size: 1.6rem; color: var(--primary); }

    .txn-table { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .txn-table th {
        text-align: left; padding: .7rem 1rem; background: rgba(17,24,39,0.03);
        color: var(--muted); font-weight: 700; font-size: .74rem; text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid var(--border);
    }
    .txn-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); }
    .txn-table tr:last-child td { border-bottom: none; }
    .txn-empty { text-align: center; color: var(--muted); padding: 2.5rem 1rem; }
    .txn-amount { font-weight: 700; color: #16A34A; }
    .txn-link { color: var(--primary); font-weight: 600; }
    .txn-link:hover { text-decoration: underline; }

    @media (max-width: 1100px) { .summary-row { grid-template-columns: repeat(2, 1fr); } .quick-row { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .summary-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon pending"><i class="fas fa-hourglass-half"></i></div>
        <div><div class="summary-count">{{ $pendingPayments }}</div><div class="summary-label">Pending Payments</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon completed"><i class="fas fa-receipt"></i></div>
        <div><div class="summary-count">{{ $completedToday }}</div><div class="summary-label">Completed Transactions Today</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon sales"><i class="fas fa-chart-line"></i></div>
        <div><div class="summary-count">₱{{ number_format($dailySales, 2) }}</div><div class="summary-label">Daily Sales</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon revenue"><i class="fas fa-sack-dollar"></i></div>
        <div><div class="summary-count">₱{{ number_format($totalRevenueToday, 2) }}</div><div class="summary-label">Total Revenue Today</div></div>
    </div>
</div>

<div class="quick-row">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-magnifying-glass"></i> Quick Search</h3></div>
        <div class="card-body">
            <form class="quick-search-form" action="{{ route('cashier.billing') }}" method="GET">
                <input type="text" name="q" class="quick-search-input"
                       placeholder="Search by order number, customer name, or table number...">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body quick-payment-box">
            <i class="fas fa-cash-register"></i>
            <div style="font-weight:700">Quick Payment</div>
            <a href="{{ route('cashier.billing') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Go to Billing Screen
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-clock-rotate-left"></i> Recent Transactions</h3></div>
    <div class="card-body" style="padding:0">
        @if($recentTransactions->isEmpty())
            <div class="txn-empty">No transactions recorded yet today.</div>
        @else
            <div style="overflow-x:auto">
                <table class="txn-table">
                    <thead>
                        <tr>
                            <th>Transaction #</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Amount</th>
                            <th>Time</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $payment)
                            <tr>
                                <td>{{ $payment->transaction_number }}</td>
                                <td>{{ $payment->order?->order_number }}</td>
                                <td>{{ $payment->order?->customer_name ?? 'Walk-in' }}</td>
                                <td>{{ $payment->cashier?->staff?->full_name ?? $payment->cashier?->email ?? '—' }}</td>
                                <td class="txn-amount">₱{{ number_format($payment->amount_paid, 2) }}</td>
                                <td>{{ $payment->payment_date?->format('h:i A') }}</td>
                                <td><a href="{{ route('cashier.receipts.show', $payment) }}" class="txn-link" target="_blank">View Receipt</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
