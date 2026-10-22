<?php

namespace App\Http\Controllers;

use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffPasswordResetController extends Controller
{
    /* ── REQ011: List all pending requests ──────────────────── */

    public function index(): View
    {
        $this->authorize('managePasswordResets', User::class);

        $requests = StaffPasswordResetRequest::with(['user', 'requestedBy', 'processedBy'])
            ->latest()
            ->paginate(20);

        $pendingCount = StaffPasswordResetRequest::pending()->count();

        return view('users.password-resets', compact('requests', 'pendingCount'));
    }

    /* ── REQ011: Approve → reset to the default password ────── */

    /**
     * Puts the account back on its default password (last name + birthday). The staff
     * member logs in with it and is then made to set a new password.
     */
    public function approve(StaffPasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $this->authorize('managePasswordResets', User::class);

        if (! $passwordResetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $user    = $passwordResetRequest->user->load('staff');
        $default = $user->resetToDefaultPassword();

        // Without a birthday there's no default to reset to: the request stays pending
        if ($default === null) {
            return back()->with('error', "{$user->name} has no birthday on file, so their password can't be reset to the default. Add it in Edit Staff Account, then approve again.");
        }

        $passwordResetRequest->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()
            ->with('success', "{$user->name}'s password was reset to the default. They will be asked to set a new one when they log in.")
            ->with('defaultPassword', $default);
    }

    /* ── REQ011: Reject ─────────────────────────────────────── */

    public function reject(Request $request, StaffPasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $this->authorize('managePasswordResets', User::class);

        if (! $passwordResetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $passwordResetRequest->update([
            'status'       => 'rejected',
            'note'         => $request->input('note'),
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Password reset request rejected.');
    }
}
