<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::with('address');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
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

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('customers._results', compact('customers'))->render(),
                'count' => $customers->total(),
            ]);
        }

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

    /**
     * Deactivating needs a reason (the customer sees it when they try to log in);
     * activating clears it.
     */
    public function toggleStatus(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('toggleStatus', $customer);

        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';

        if ($newStatus === 'inactive') {
            // If the reason is rejected, the page reopens the pop-up for this customer
            $request->session()->flash('toggle_customer', [
                'action' => route('customers.toggle-status', $customer),
                'name'   => $customer->full_name,
            ]);

            $data = $request->validate([
                'reason' => ['required', 'string', 'max:255'],
            ], [
                'reason.required' => 'Please enter a reason for deactivating this account.',
                'reason.max'      => 'The reason may not be longer than 255 characters.',
            ]);

            $customer->update([
                'status'              => 'inactive',
                'deactivation_reason' => trim($data['reason']),
                'deactivated_at'      => now(),
            ]);
        } else {
            $customer->update(['status' => 'active', 'deactivation_reason' => null, 'deactivated_at' => null]);
        }

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', "Customer account for \"{$customer->full_name}\" has been {$label}.");
    }
}
