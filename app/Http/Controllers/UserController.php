<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    /* ── Index ─────────────────────────────────────────────── */

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['role', 'staff'])
            ->whereHas('role', fn ($q) => $q->whereIn('role_name', ['admin', 'cashier', 'kitchen_staff', 'table_server']));

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('staff', fn ($s) => $s->where('first_name', 'like', "%{$search}%")
                                                      ->orWhere('last_name', 'like', "%{$search}%"))
                  // Matches the role as shown ("Kitchen Staff") or as stored ("kitchen_staff")
                  ->orWhereHas('role', fn ($r) => $r->where('role_name', 'like', '%' . str_replace(' ', '_', $search) . '%'));
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

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('users._results', compact('users'))->render(),
                'count' => $users->total(),
                'stats' => [
                    'total'         => $users->total(),
                    'active'        => $users->getCollection()->where('status', 'active')->count(),
                    'inactive'      => $users->getCollection()->where('status', 'inactive')->count(),
                    'pendingResets' => $pendingResetCount,
                ],
            ]);
        }

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
        // The account starts on a system-made default password (last name + birthday);
        // the staff member must set their own on first log-in
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'email'    => $request->email,
                'username' => $request->username,
                'password' => Hash::make(Str::random(40)), // replaced by the default just below
                'role_id'  => $request->role_id,
                'status'   => 'active',
            ]);

            Staff::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'birthday'   => $request->birthday,
                'contact_no' => $request->phone ?? '',
                'user_id'    => $user->id,
            ]);

            $user->load('staff')->resetToDefaultPassword();

            return $user;
        });

        return redirect()->route('users.show', $user)
            ->with('success', 'Staff account created successfully.')
            ->with('defaultPassword', $user->staff->defaultPassword());
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
                    'birthday'   => $request->birthday ?? $user->staff->birthday,
                    'contact_no' => $request->phone ?? $user->staff->contact_no,
                ]);
            } else {
                Staff::create([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'birthday'   => $request->birthday,
                    'contact_no' => $request->phone ?? '',
                    'user_id'    => $user->id,
                ]);
            }
        });

        return redirect()->route('users.show', $user)
            ->with('success', 'Staff account updated successfully.');
    }

    /* ── Reset to Default Password ──────────────────────────── */

    /**
     * Admin resets a staff member's password back to the default (last name + birthday).
     * They must set a new one the next time they log in.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->load('staff');
        $default = $user->resetToDefaultPassword();

        if ($default === null) {
            return back()->with('error', 'Add this staff member\'s birthday first (Edit Staff Account), so a default password can be made.');
        }

        // Any open reset request for this account is settled by this reset
        $user->passwordResetRequests()->pending()->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()
            ->with('success', 'Password reset to the default. They will be asked to set a new one when they log in.')
            ->with('defaultPassword', $default);
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
