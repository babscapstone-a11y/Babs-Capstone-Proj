<?php

namespace App\Http\Controllers;

use App\Models\RestaurantDowntime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RestaurantDowntimeController extends Controller
{
    /**
     * Mark the restaurant temporarily unavailable until a given time. If a
     * downtime is already active, this just moves its end time / reason
     * instead of stacking a second overlapping window.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ends_at' => ['required', 'date', 'after:now'],
            'reason'  => ['nullable', 'string', 'max:255'],
        ]);

        $active = RestaurantDowntime::current();

        if ($active) {
            $active->update([
                'ends_at' => $validated['ends_at'],
                'reason'  => $validated['reason'] ?? $active->reason,
            ]);

            return back()->with('success', 'Downtime updated — service now resumes at ' . $active->fresh()->ends_at->format('M d, h:i A') . '.');
        }

        $downtime = RestaurantDowntime::create([
            'starts_at' => now(),
            'ends_at'   => $validated['ends_at'],
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
