<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /* ── Index ─────────────────────────────────────────────── */

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['role', 'staff'])
            ->whereHas('role', fn ($q) => $q->whereIn('role_name', ['admin', 'cashier', 'kitchen_staff', 'table_server']));

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->get('role')) {
            $query->where('role_id', $roleId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $users  = $query->latest()->paginate(15)->withQueryString();
        $roles  = Role::whereIn('role_name', ['admin', 'cashier', 'kitchen_staff', 'table_server'])->get();
        $pendingResetCount = StaffPasswordResetRequest::pending()->count();

        return view('users.index', compact('users', 'roles', 'pendingResetCount'));
    }

    /* ── Create ─────────────────────────────────────────────── */

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::whereIn('role_name', ['admin', 'cashier', 'kitchen_staff', 'table_server'])->get();

        return view('users.create', compact('roles'));
    }

    /* ── Store ──────────────────────────────────────────────── */

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'email'    => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role_id'  => $request->role_id,
                'status'   => $request->status,
            ]);

            Staff::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'contact_no' => $request->phone ?? '',
                'user_id'    => $user->id,
            ]);
        });

        return redirect()->route('users.index')
            ->with('success', 'Staff account created successfully.');
    }

    /* ── Show ───────────────────────────────────────────────── */

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['role', 'staff', 'passwordResetRequests.requestedBy', 'passwordResetRequests.processedBy']);
        $pendingReset = $user->passwordResetRequests()->pending()->latest()->first();

        return view('users.show', compact('user', 'pendingReset'));
    }

    /* ── Edit ───────────────────────────────────────────────── */

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::whereIn('role_name', ['admin', 'cashier', 'kitchen_staff', 'table_server'])->get();
        $user->load(['role', 'staff']);

        return view('users.edit', compact('user', 'roles'));
    }

    /* ── Update ─────────────────────────────────────────────── */

    public function update(UpdateStaffRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $user->update([
                'email'    => $request->email,
                'username' => $request->username,
                'role_id'  => $request->role_id,
                'status'   => $request->status,
            ]);

            if ($user->staff) {
                $user->staff->update([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'contact_no' => $request->phone ?? $user->staff->contact_no,
                ]);
            } else {
                Staff::create([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'contact_no' => $request->phone ?? '',
                    'user_id'    => $user->id,
                ]);
            }
        });

        return redirect()->route('users.show', $user)
            ->with('success', 'Staff account updated successfully.');
    }

    /* ── Toggle Status (REQ012) ─────────────────────────────── */

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $action = $user->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', "Staff account has been {$action}.");
    }
}
