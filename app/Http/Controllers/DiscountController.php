<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function index(Request $request): View
    {
        $query = Discount::query();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('discount_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($type = $request->input('type')) {
            $query->where('discount_type', $type);
        }
        if ($eligibility = $request->input('eligibility')) {
            $query->where('eligibility_type', $eligibility);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $allowed = ['discount_name', 'discount_type', 'discount_value', 'status', 'created_at', 'updated_at'];
        $sort    = in_array($request->input('sort'), $allowed) ? $request->input('sort') : 'created_at';
        $dir     = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $discounts     = $query->paginate(10)->withQueryString();
        $totalActive   = Discount::active()->count();
        $totalInactive = Discount::inactive()->count();
        $totalPromo    = Discount::where('eligibility_type', 'promotional')->count();
        $totalExpired  = Discount::active()->expired()->count();

        return view('discounts.index', compact(
            'discounts', 'totalActive', 'totalInactive', 'totalPromo', 'totalExpired'
        ));
    }

    public function create(): View
    {
        $types       = Discount::TYPES;
        $eligibility = Discount::ELIGIBILITY;
        return view('discounts.create', compact('types', 'eligibility'));
    }

    public function store(DiscountRequest $request): RedirectResponse
    {
        $discount = Discount::create($request->validated());

        return redirect()->route('discounts.show', $discount)
            ->with('success', "Discount \"{$discount->discount_name}\" created successfully.");
    }

    public function show(Discount $discount): View
    {
        return view('discounts.show', compact('discount'));
    }

    public function edit(Discount $discount): View
    {
        $types       = Discount::TYPES;
        $eligibility = Discount::ELIGIBILITY;
        return view('discounts.edit', compact('discount', 'types', 'eligibility'));
    }

    public function update(DiscountRequest $request, Discount $discount): RedirectResponse
    {
        $discount->update($request->validated());

        return redirect()->route('discounts.show', $discount)
            ->with('success', "Discount \"{$discount->discount_name}\" updated successfully.");
    }

    public function toggleStatus(Discount $discount): RedirectResponse
    {
        $newStatus = $discount->status === 'active' ? 'inactive' : 'active';
        $discount->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return back()->with('success', "Discount \"{$discount->discount_name}\" has been {$label}.");
    }
}
