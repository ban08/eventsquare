<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Store a newly created report for an event (RU11).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Event $event)
    {
        // Prevent admins from reporting events
        if (Gate::allows('admin')) {
            return back()->withErrors(['error' => 'Administrators cannot report events.']);
        }

        // Prevent organizers from reporting their own events
        if (Auth::id() === $event->id_organizer) {
            return back()->withErrors(['error' => 'You cannot report your own event.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Check for existing report
        $existingReport = EventReport::where('id_event', $event->id_event)
            ->where('id_user', Auth::id())
            ->first();

        if ($existingReport) {
            $existingReport->update([
                'reason' => $validated['reason'],
                'status' => 'open', // Re-open if it was dismissed/resolved
                'created_at' => now(), // Update timestamp to bump it up
            ]);
            return back()->with('success', 'Your previous report for this event has been updated.');
        }

        $report = new EventReport();
        $report->id_event = $event->id_event;
        $report->id_user = Auth::id();
        $report->reason = $validated['reason'];
        $report->status = 'open';
        $report->save();

        return back()->with('success', 'Event reported successfully.');
    }
}
