<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // Prevent organizers from reporting their own events
        if (Auth::id() === $event->id_organizer) {
            return back()->withErrors(['error' => 'You cannot report your own event.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $report = new EventReport();
        $report->id_event = $event->id_event;
        $report->id_user = Auth::id();
        $report->reason = $validated['reason'];
        $report->status = 'open';
        $report->save();

        return back()->with('success', 'Event reported successfully.');
    }
}
