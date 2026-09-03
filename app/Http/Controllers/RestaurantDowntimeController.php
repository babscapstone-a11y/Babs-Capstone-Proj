<?php

namespace App\Http\Controllers;

use App\Models\RestaurantDowntime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RestaurantDowntimeController extends Controller
{
    /**
     * Mark the restaurant temporarily unavailable until a given date and
     * time (picked as two separate dropdowns, not a duration — the admin
     * sets the actual moment service resumes). If a downtime is already
     * active, this just moves its end time / reason instead of stacking a
     * second overlapping window.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'downtime_date' => ['required', 'date'],
            'downtime_time' => ['required', 'date_format:H:i'],
            'reason'        => ['nullable', 'string', 'max:255'],
        ], [
            'downtime_date.required' => 'Please choose a date.',
            'downtime_time.required' => 'Please choose a time.',
        ]);

        $endsAt = Carbon::parse($validated['downtime_date'] . ' ' . $validated['downtime_time']);

        if ($endsAt->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'downtime_time' => 'That date and time has already passed — please choose a time in the future.',
            ]);
        }

        $active = RestaurantDowntime::current();

        if ($active) {
            $active->update([
                'ends_at' => $endsAt,
                'reason'  => $validated['reason'] ?? $active->reason,
            ]);

            return back()->with('success', 'Downtime updated — service now resumes at ' . $active->fresh()->ends_at->format('M d, h:i A') . '.');
        }

        $downtime = RestaurantDowntime::create([
            'starts_at' => now(),
            'ends_at'   => $endsAt,
            'reason'    => $validated['reason'] ?? null,
            'set_by_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Restaurant marked temporarily unavailable until ' . $downtime->ends_at->format('M d, h:i A') . '.');
    }

    /** Resume service before the scheduled end time. */
    public function end(Request $request): RedirectResponse
    {
        $active = RestaurantDowntime::current();

        if (! $active) {
            return back()->with('error', 'There is no active downtime to end.');
        }

        $active->update([
            'ends_at'        => now(),
            'ended_early_at' => now(),
            'ended_by_id'    => $request->user()->id,
        ]);

        return back()->with('success', 'Restaurant service has been resumed.');
    }
}
