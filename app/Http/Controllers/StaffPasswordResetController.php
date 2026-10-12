<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetOtp;
use App\Models\StaffPasswordResetRequest;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

    /* ── REQ011: Approve → email a 6-digit reset code ───────── */

    public function approve(StaffPasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $this->authorize('managePasswordResets', User::class);

        if (! $passwordResetRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $user    = $passwordResetRequest->user;
        $minutes = PasswordResetOtp::APPROVED_EXPIRES_MINUTES;

        // Send the code first: if the email fails, the request stays pending so it can be retried.
        try {
            $user->notify(new PasswordResetOtpNotification(
                PasswordResetOtp::issue($user->email, $minutes),
                $minutes,
                URL::temporarySignedRoute('password.otp.enter', now()->addMinutes($minutes), ['email' => $user->email]),
            ));
        } catch (\Throwable $e) {
            report($e);
            PasswordResetOtp::where('email', $user->email)->delete();

            return back()->with('error', 'The reset code email could not be sent, so the request is still pending. Check your mail configuration and try again.');
        }

        $passwordResetRequest->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', "A 6-digit password reset code was emailed to {$user->email}.");
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
