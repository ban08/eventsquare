<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function accept(Application $application)
    {
        $event = $application->event;

        // Authorization: Only organizer can accept
        if (Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can manage applications.');
        }

        if ($application->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        // Check capacity
        if ($event->is_full) {
            return back()->with('error', 'Event is full. Cannot accept more participants.');
        }

        DB::transaction(function () use ($application, $event) {
            // Update application status
            $application->update([
                'status' => 'approved',
                'decided_at' => now(),
            ]);

            // Add to participants
            $event->participants()->attach($application->id_user, ['joined_at' => now()]);

            // Notify user
            Notification::create([
                'id_user' => $application->id_user,
                'message' => 'application accepted',
                'id_event' => $event->id_event,
                'id_application' => $application->id_application,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Application accepted. User added to participants.');
    }

    public function reject(Application $application)
    {
        $event = $application->event;

        // Authorization: Only organizer can reject
        if (Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can manage applications.');
        }

        if ($application->status !== 'pending') {
            return back()->with('error', 'This application has already been processed.');
        }

        DB::transaction(function () use ($application, $event) {
            // Update application status
            $application->update([
                'status' => 'rejected',
                'decided_at' => now(),
            ]);

            // Notify user
            Notification::create([
                'id_user' => $application->id_user,
                'message' => 'application rejected',
                'id_event' => $event->id_event,
                'id_application' => $application->id_application,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Application rejected.');
    }
}
