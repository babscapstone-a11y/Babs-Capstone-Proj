<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MenuItem;
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

        return view('dashboard', compact(
            'totalStaff', 'activeStaff', 'pendingResets',
            'totalMenuItems', 'activeMenuItems', 'availableMenuItems',
            'totalCustomers', 'activeCustomers'
        ));
    }
}
