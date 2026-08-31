<?php

namespace App\Http\Controllers;

use App\Models\CancellationRequest;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantDowntime;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $staffRoles = ['admin', 'cashier', 'kitchen_staff', 'table_server'];

        $totalStaff  = User::whereHas('role', fn ($q) => $q->whereIn('role_name', $staffRoles))->count();
        $activeStaff = User::whereHas('role', fn ($q) => $q->whereIn('role_name', $staffRoles))
                           ->where('status', 'active')->count();
        $pendingResets = StaffPasswordResetRequest::pending()->count();

        $totalMenuItems     = MenuItem::count();
        $activeMenuItems    = MenuItem::where('is_active', true)->count();
        $availableMenuItems = MenuItem::where('is_active', true)->where('is_available', true)->count();

        $totalCustomers  = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();

        // ── Order Cancellation Review Module (REQ047–REQ050) ───────────
        $pendingCancellations = CancellationRequest::pending()->count();
        $approvedCancellationsToday = CancellationRequest::approved()->whereDate('review_date', today())->count();
        $rejectedCancellationsToday = CancellationRequest::rejected()->whereDate('review_date', today())->count();
        $cancelledOrders = Order::whereHas('orderStatus', fn ($q) => $q->where('status_name', 'Cancelled'))->count();

        $recentCancellationRequests = CancellationRequest::with(['order.orderStatus', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        // ── Sales Overview widget: net sales for the last 7 days ───────
        $dailySales = Payment::with('invoice')
            ->whereBetween('payment_date', [today()->subDays(6)->startOfDay(), today()->endOfDay()])
            ->get()
            ->groupBy(fn (Payment $p) => $p->payment_date->format('Y-m-d'))
            ->map(fn ($group) => (float) $group->sum(fn (Payment $p) => (float) ($p->invoice->final_total ?? $p->amount_paid)));

        $salesChartLabels = [];
        $salesChartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $salesChartLabels[] = $day->format('M d');
            $salesChartData[]   = round($dailySales->get($day->format('Y-m-d'), 0.0), 2);
        }

        $activeDowntime = RestaurantDowntime::current();

        return view('dashboard', compact(
            'totalStaff', 'activeStaff', 'pendingResets',
            'totalMenuItems', 'activeMenuItems', 'availableMenuItems',
            'totalCustomers', 'activeCustomers',
            'pendingCancellations', 'approvedCancellationsToday', 'rejectedCancellationsToday',
            'cancelledOrders', 'recentCancellationRequests',
            'salesChartLabels', 'salesChartData',
            'activeDowntime'
        ));
    }
}
