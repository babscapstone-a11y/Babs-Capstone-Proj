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
     * Mark the restaurant unavailable for a start/end window — e.g. right
     * now until service resumes, or a holiday closure scheduled well in
     * advance. If the new window overlaps one that's already stored, this
     * just moves that window's start/end/reason instead of stacking a
     * second overlapping one.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'downtime_start_date' => ['required', 'date'],
            'downtime_start_time' => ['required', 'date_format:H:i'],
            'downtime_date'       => ['required', 'date'],
            'downtime_time'       => ['required', 'date_format:H:i'],
            'reason'              => ['nullable', 'string', 'max:255'],
        ], [
            'downtime_start_date.required' => 'Please choose a start date.',
            'downtime_start_time.required' => 'Please choose a start time.',
            'downtime_date.required'       => 'Please choose an end date.',
            'downtime_time.required'       => 'Please choose an end time.',
        ]);

        $startsAt = Carbon::parse($validated['downtime_start_date'] . ' ' . $validated['downtime_start_time']);
        $endsAt   = Carbon::parse($validated['downtime_date'] . ' ' . $validated['downtime_time']);

        if ($endsAt->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'downtime_time' => 'That end date and time has already passed — please choose a time in the future.',
            ]);
        }

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'downtime_time' => 'The end date and time must be after the start.',
            ]);
        }

        $overlapping = RestaurantDowntime::where('ends_at', '>', $startsAt)
            ->where('starts_at', '<', $endsAt)
            ->orderByDesc('starts_at')
            ->first();

        if ($overlapping) {
            $overlapping->update([
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
                'reason'    => $validated['reason'] ?? $overlapping->reason,
            ]);

            return back()->with('success', 'Downtime updated — restaurant unavailable from ' . $overlapping->fresh()->starts_at->format('M d, h:i A') . ' until ' . $overlapping->fresh()->ends_at->format('M d, h:i A') . '.');
        }

        $downtime = RestaurantDowntime::create([
            'starts_at' => $startsAt,
            'ends_at'   => $endsAt,
            'reason'    => $validated['reason'] ?? null,
            'set_by_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Restaurant scheduled unavailable from ' . $downtime->starts_at->format('M d, h:i A') . ' until ' . $downtime->ends_at->format('M d, h:i A') . '.');
    }

    /** Remove a downtime that hasn't started yet — e.g. a holiday closure the admin no longer needs. */
    public function cancel(RestaurantDowntime $downtime): RedirectResponse
    {
        if (! $downtime->starts_at->isFuture()) {
            return back()->with('error', 'That downtime has already started — use "End Downtime Now" instead.');
        }

        $downtime->delete();

        return back()->with('success', 'Scheduled downtime cancelled.');
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
