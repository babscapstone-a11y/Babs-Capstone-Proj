<?php

namespace App\Http\Controllers;

use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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

    /* ── REQ010: Show confirmation to create a request ─────── */

    public function create(User $user): View
    {
        $this->authorize('resetPassword', $user);

        $hasPending = $user->passwordResetRequests()->pending()->exists();

        return view('users.password-reset-confirm', compact('user', 'hasPending'));
    }

    /* ── REQ010: Store the reset request ────────────────────── */

    public function store(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $hasPending = $user->passwordResetRequests()->pending()->exists();

        if ($hasPending) {
            return redirect()->route('users.show', $user)
                ->with('error', 'A password reset request is already pending for this staff member.');
        }

        StaffPasswordResetRequest::create([
            'user_id'      => $user->id,
            'requested_by' => auth()->id(),
            'status'       => 'pending',
        ]);

        return redirect()->route('password-reset-requests.index')
            ->with('success', "Password reset request created for {$user->name}. Approve it to send the email.");
    }

    /* ── REQ011: Approve → send Laravel reset email ─────────── */

    public function approve(StaffPasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $this->authorize('managePasswordResets', User::class);

        if (! $passwordResetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $passwordResetRequest->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $status = Password::sendResetLink(
            ['email' => $passwordResetRequest->user->email]
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', 'Request approved but failed to send the reset email. Check your mail configuration.');
        }

        return back()->with('success', "Password reset email sent to {$passwordResetRequest->user->email}.");
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
