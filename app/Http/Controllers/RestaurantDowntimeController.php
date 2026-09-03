<?php

namespace App\Http\Controllers;

use App\Models\RestaurantDowntime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RestaurantDowntimeController extends Controller
{
    /** Preset durations (minutes) offered in the "Set Downtime" dropdown. */
    private const DURATION_PRESETS = [15, 30, 60, 120, 180, 240];

    /**
     * Mark the restaurant temporarily unavailable until a given time. If a
     * downtime is already active, this just moves its end time / reason
     * instead of stacking a second overlapping window.
     *
     * Preset durations are resolved to an end time from now() here on the
     * server, rather than trusting a client-computed timestamp, so the
     * result isn't thrown off by client clock skew or a stale value left
     * sitting in the form.
     */
    public function store(Request $request): RedirectResponse
    {
        $presets = array_map('strval', self::DURATION_PRESETS);

        $validated = $request->validate([
            'duration_minutes' => ['required', Rule::in([...$presets, 'custom'])],
            'ends_at'           => ['required_if:duration_minutes,custom', 'nullable', 'date', 'after:now'],
            'reason'            => ['nullable', 'string', 'max:255'],
        ], [
            'duration_minutes.required' => 'Please choose how long the restaurant will be unavailable.',
            'duration_minutes.in'       => 'Please choose a valid duration.',
            'ends_at.required_if'       => 'Please choose the date and time service resumes.',
            'ends_at.after'             => 'The downtime must end at a time in the future.',
        ]);

        $endsAt = $validated['duration_minutes'] === 'custom'
            ? Carbon::parse($validated['ends_at'])
            : now()->addMinutes((int) $validated['duration_minutes']);

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
