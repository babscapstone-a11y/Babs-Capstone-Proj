<?php

namespace App\Http\Controllers;

use App\Models\CancellationRequest;
use App\Models\Customer;
use App\Models\InventoryItem;
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

        $todaySales = (float) ($salesChartData[6] ?? 0);
        $todayTransactions = Payment::whereDate('payment_date', today())->count();

        // ── Order stats (online orders still awaiting approval aren't "active" yet) ──
        $liveOrders = fn () => Order::where(function ($q) {
            $q->where('order_type', '!=', 'online')->orWhere('approval_status', 'approved');
        });
        $activeOrders = $liveOrders()
            ->whereHas('orderStatus', fn ($q) => $q->whereIn('status_name', ['Pending', 'Processing', 'Ready']))
            ->count();
        $completedOrdersToday = $liveOrders()
            ->whereHas('orderStatus', fn ($q) => $q->whereIn('status_name', ['Served', 'Packaged', 'Completed']))
            ->whereDate('updated_at', today())
            ->count();

        // ── Orders Overview widget: orders placed per day, last 7 days ──
        $dailyOrders = Order::whereBetween('created_at', [today()->subDays(6)->startOfDay(), today()->endOfDay()])
            ->whereHas('orderStatus', fn ($q) => $q->where('status_name', '!=', 'Cancelled'))
            ->get(['id', 'created_at'])
            ->groupBy(fn (Order $o) => $o->created_at->format('Y-m-d'))
            ->map->count();

        $ordersChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $ordersChartData[] = (int) $dailyOrders->get(today()->subDays($i)->format('Y-m-d'), 0);
        }

        // ── Inventory Status widget ──
        $inventoryTotal = InventoryItem::where('is_active', true)->count();
        $inventoryOut   = InventoryItem::where('is_active', true)->outOfStock()->count();
        $inventoryLow   = InventoryItem::where('is_active', true)->lowStock()->count();
        $inventoryOk    = max(0, $inventoryTotal - $inventoryOut - $inventoryLow);
        $lowStockItems  = InventoryItem::where('is_active', true)
            ->where(fn ($q) => $q->outOfStock()->orWhere(fn ($q2) => $q2->lowStock()))
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $activeDowntime   = RestaurantDowntime::current();
        $upcomingDowntime = RestaurantDowntime::next();

        return view('dashboard', compact(
            'totalStaff', 'activeStaff', 'pendingResets',
            'totalMenuItems', 'activeMenuItems', 'availableMenuItems',
            'totalCustomers', 'activeCustomers',
            'pendingCancellations', 'approvedCancellationsToday', 'rejectedCancellationsToday',
            'cancelledOrders', 'recentCancellationRequests',
            'salesChartLabels', 'salesChartData',
            'todaySales', 'todayTransactions', 'activeOrders', 'completedOrdersToday', 'ordersChartData',
            'inventoryTotal', 'inventoryOut', 'inventoryLow', 'inventoryOk', 'lowStockItems',
            'activeDowntime', 'upcomingDowntime'
        ));
    }
}
