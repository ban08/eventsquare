<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class InvitationController extends Controller
{
    /**
     * OR03 Invite a user to an event.
     */
    public function invite(Request $request, Event $event)
    {
        // Authorization: only organizer
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can invite users.');
        }

        // OR03: Cannot invite to canceled, completed, or deleted events
        // Use effective_status to catch events that are past their end date
        $effectiveStatus = $event->effective_status;
        if (in_array($effectiveStatus, ['canceled', 'completed', 'deleted'])) {
            return back()->withErrors(['invitee_email' => 'Cannot send invitations to ' . $effectiveStatus . ' events.']);
        }

        // Validate email instead of raw user ID
        $validated = $request->validate([
            'invitee_email' => ['required', 'email', 'exists:user,email'],
        ]);

        $inviteeUser = User::where('email', $validated['invitee_email'])->first();
        if (!$inviteeUser) {
            return back()->withErrors(['invitee_email' => 'User email not found.']);
        }

        // Prevent inviting deleted users
        if ($inviteeUser->status === 'deleted') {
            return back()->withErrors(['invitee_email' => 'Cannot invite a deleted user.']);
        }

        // Prevent inviting admins
        if (\App\Models\Admin::where('email', $inviteeUser->email)->exists()) {
            return back()->withErrors(['invitee_email' => 'Cannot invite an administrator.']);
        }

        // Organizer cannot invite themselves
        if ($inviteeUser->id_user === $event->id_organizer) {
            return back()->withErrors(['invitee_email' => 'You cannot invite yourself.']);
        }

        // Already participating?
        $alreadyParticipating = DB::table('participation')
            ->where('id_event', $event->id_event)
            ->where('id_user', $inviteeUser->id_user)
            ->whereNull('left_at') // Only check active participants
            ->exists();
        if ($alreadyParticipating) {
            return back()->withErrors(['invitee_email' => 'User is already a participant.']);
        }

        // Check if event is full
        if ($event->is_full) {
            return back()->withErrors(['invitee_email' => 'Event is full. Cannot send more invitations.']);
        }

        // Check for pending application (Auto-approve if exists)
        $pendingApplication = \App\Models\Application::where('id_event', $event->id_event)
            ->where('id_user', $inviteeUser->id_user)
            ->where('status', 'pending')
            ->first();

        if ($pendingApplication) {
            DB::transaction(function () use ($event, $inviteeUser, $pendingApplication) {
                // Approve application
                $pendingApplication->update([
                    'status' => 'approved',
                    'decided_at' => now()
                ]);

                // Create participation
                $event->participants()->attach($inviteeUser->id_user, ['joined_at' => now()]);
            });

            return back()->with('success', 'User had a pending application and has been added to the event!');
        }

        // Check for existing invitation (any status)
        $existingInvitation = Invitation::where('id_event', $event->id_event)
            ->where('id_invitee', $inviteeUser->id_user)
            ->first();

        $invitation = null;

        if ($existingInvitation) {
            if ($existingInvitation->status === 'pending') {
                return back()->withErrors(['invitee_email' => 'User already has an active invitation.']);
            }

            // Reactivate invitation (works for accepted-but-left, declined, canceled, etc.)
            $existingInvitation->update([
                'status' => 'pending',
                'sent_at' => now(),
                'responded_at' => null
            ]);
            $invitation = $existingInvitation;
        } else {
            // Create invitation with sequence safety (fix sequence misalignment if needed)
            try {
                $invitation = Invitation::create([
                    'id_event'   => $event->id_event,
                    'id_invitee' => $inviteeUser->id_user,
                    'status'     => 'pending',
                    'sent_at'    => now(),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                // Sequence likely behind due to manual seed inserts specifying id_invitation.
                $max = DB::table('invitation')->max('id_invitation');
                if ($max === null) { $max = 0; }
                // Reset sequence to max+1 (PostgreSQL specific)
                try {
                    DB::statement("SELECT setval(pg_get_serial_sequence('invitation','id_invitation'), " . ($max + 1) . ")");
                } catch (\Throwable $seqE) {
                    // ignore if fails
                }
                // Retry once
                $invitation = Invitation::create([
                    'id_event'   => $event->id_event,
                    'id_invitee' => $inviteeUser->id_user,
                    'status'     => 'pending',
                    'sent_at'    => now(),
                ]);
            }
        }

        // Optional notification insert (ignore errors in prototype)
        try {
            DB::table('notification')->insert([
                'id_user'       => $inviteeUser->id_user,
                'message'       => 'invited',
                'id_event'      => $event->id_event,
                'id_invitation' => $invitation->id_invitation,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // silently ignore in prototype
        }

        return back()->with('success', 'Invitation sent successfully to ' . $inviteeUser->email);
    }

    /**
     * RU10 Accept invitation.
     */
    public function accept(Invitation $invitation)
    {
        if (!Auth::check() || Auth::id() !== $invitation->id_invitee) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'You are not the invitee.'], 403);
            }
            abort(403, 'You are not the invitee.');
        }

        // BR13: Admins cannot participate in events
        if (Gate::allows('admin')) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Administrators cannot participate in events.'], 403);
            }
            return back()->with('error', 'Administrators cannot participate in events.');
        }

        // Load the event to check its status
        $event = Event::find($invitation->id_event);
        if (!$event) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Event no longer exists.'], 404);
            }
            return back()->withErrors(['invitation' => 'Event no longer exists.']);
        }

        // Organizer cannot accept invitation (should not happen, but safety first)
        if ($event->id_organizer === $invitation->id_invitee) {
             if (request()->wantsJson()) {
                return response()->json(['message' => 'Organizer cannot join their own event.'], 422);
            }
            return back()->withErrors(['invitation' => 'Organizer cannot join their own event.']);
        }

        // Registration closes 24 hours before event start
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Registration closed 24 hours before event start.'], 422);
            }
            return back()->withErrors(['invitation' => 'Registration closed 24 hours before event start.']);
        }

        // Cannot accept invitation to canceled, completed, or deleted events
        // Use effective_status to catch events that are past their end date
        $effectiveStatus = $event->effective_status;
        if (in_array($effectiveStatus, ['canceled', 'completed', 'deleted'])) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Cannot join a ' . $effectiveStatus . ' event.'], 422);
            }
            return back()->withErrors(['invitation' => 'Cannot join a ' . $effectiveStatus . ' event.']);
        }

        // Check if event is full
        if ($event->is_full) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Event is full. Cannot accept invitation.'], 422);
            }
            return back()->withErrors(['invitation' => 'Event is full. Cannot accept invitation.']);
        }

        // Idempotency: If already accepted, allow re-joining if they left
        if ($invitation->status === 'accepted') {
            $participation = DB::table('participation')
                ->where('id_event', $invitation->id_event)
                ->where('id_user', $invitation->id_invitee)
                ->first();
            
            if (!$participation) {
                // Should exist if accepted, but maybe deleted manually? Re-insert.
                try {
                    DB::table('participation')->insert([
                        'id_event' => $invitation->id_event,
                        'id_user'  => $invitation->id_invitee,
                        'joined_at'=> now(),
                    ]);
                } catch (\Throwable $e) { }
            } elseif ($participation->left_at !== null) {
                // User left, but wants to re-join using the same accepted invite
                DB::table('participation')
                    ->where('id_participation', $participation->id_participation)
                    ->update([
                        'left_at' => null,
                        'joined_at' => now()
                    ]);

                if (request()->wantsJson()) {
                    return response()->json(['success' => true, 'message' => 'Welcome back! You have re-joined the event.']);
                }

                return back()->with('success', 'Welcome back! You have re-joined the event.');
            }

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'You are already participating.']);
            }
            return back()->with('success', 'You are already participating.');
        }

        if ($invitation->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Invitation already responded.'], 422);
            }
            return back()->withErrors(['invitation' => 'Invitation already responded.']);
        }

        $invitation->update([
            'status'       => 'accepted',
            'responded_at' => now(),
        ]);

        // Automatically create participation if not already present
        try {
            DB::table('participation')->insert([
                'id_event' => $invitation->id_event,
                'id_user'  => $invitation->id_invitee,
                'joined_at'=> now(),
            ]);

            // Clean up any pending application for this user (since they just joined via invite)
            \App\Models\Application::where('id_event', $invitation->id_event)
                ->where('id_user', $invitation->id_invitee)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved', 
                    'decided_at' => now()
                ]);

            // Notify organizer
            \App\Models\Notification::create([
                'id_user' => $event->id_organizer,
                'message' => 'user joined',
                'id_event' => $event->id_event,
                'id_invitation' => $invitation->id_invitation,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore if constraint/trigger rejects
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Invitation accepted.']);
        }

        return back()->with('success', 'Invitation accepted.');
    }

    /**
     * RU10 Decline invitation.
     */
    public function decline(Invitation $invitation)
    {
        if (!Auth::check() || Auth::id() !== $invitation->id_invitee) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'You are not the invitee.'], 403);
            }
            abort(403, 'You are not the invitee.');
        }

        // Idempotency: If already declined, return success
        if ($invitation->status === 'declined') {
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Invitation declined.']);
            }
            return back()->with('success', 'Invitation declined.');
        }

        if ($invitation->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Invitation already responded.'], 422);
            }
            return back()->withErrors(['invitation' => 'Invitation already responded.']);
        }

        $invitation->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Invitation declined.']);
        }

        return back()->with('success', 'Invitation declined.');
    }

    /**
     * Cancel an invitation (Organizer only).
     */
    public function destroy(Invitation $invitation)
    {
        $event = $invitation->event;
        
        // Authorization: only organizer
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can cancel invitations.');
        }

        // Only pending invitations can be canceled
        if ($invitation->status !== 'pending') {
             return back()->withErrors(['invitation' => 'Only pending invitations can be canceled.']);
        }

        // Delete associated notification manually since ON DELETE is SET NULL
        \App\Models\Notification::where('id_invitation', $invitation->id_invitation)->delete();

        // Delete the invitation
        $invitation->delete();

        return back()->with('success', 'Invitation canceled.');
    }

    /**
     * List current user's invitations (pending first then others).
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $pending = Invitation::with(['event'])
            ->where('id_invitee', Auth::id())
            ->where('status', 'pending')
            ->whereHas('event', function ($query) {
                $query->where('end_at', '>', now());
            })
            ->orderByDesc('sent_at')
            ->get();

        $responded = Invitation::with(['event'])
            ->where('id_invitee', Auth::id())
            ->where('status', '!=', 'pending')
            ->orderByDesc('responded_at')
            ->limit(20)
            ->get();

        return view('invitations.index', compact('pending', 'responded'));
    }
}
