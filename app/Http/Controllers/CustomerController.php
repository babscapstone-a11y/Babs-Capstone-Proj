<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::with('address');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%")
                  ->orWhere('id', is_numeric($search) ? $search : 0);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sortField = in_array($request->input('sort'), ['full_name', 'created_at'])
            ? $request->input('sort')
            : 'created_at';
        $sortDir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortField === 'full_name') {
            $query->orderBy('first_name', $sortDir)->orderBy('last_name', $sortDir);
        } else {
            $query->orderBy('created_at', $sortDir);
        }

        $customers = $query->paginate(15)->withQueryString();

        $totalCustomers    = Customer::count();
        $activeCustomers   = Customer::where('status', 'active')->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();

        return view('customers.index', compact(
            'customers', 'totalCustomers', 'activeCustomers', 'inactiveCustomers'
        ));
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load('address');

        return view('customers.show', compact('customer'));
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $this->authorize('toggleStatus', $customer);

        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';

        $customer->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', "Customer account for \"{$customer->full_name}\" has been {$label}.");
    }
}
