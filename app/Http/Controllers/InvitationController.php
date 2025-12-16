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

        // Validate email instead of raw user ID
        $validated = $request->validate([
            'invitee_email' => ['required', 'email', 'exists:user,email'],
        ]);

        $inviteeUser = User::where('email', $validated['invitee_email'])->first();
        if (!$inviteeUser) {
            return back()->withErrors(['invitee_email' => 'User email not found.']);
        }

        // Organizer cannot invite themselves
        if ($inviteeUser->id_user === $event->id_organizer) {
            return back()->withErrors(['invitee_email' => 'You cannot invite yourself.']);
        }

        // Already participating?
        $alreadyParticipating = DB::table('participation')
            ->where('id_event', $event->id_event)
            ->where('id_user', $inviteeUser->id_user)
            ->exists();
        if ($alreadyParticipating) {
            return back()->withErrors(['invitee_email' => 'User is already a participant.']);
        }

        // Duplicate invitation (pending or accepted)
        $exists = Invitation::where('id_event', $event->id_event)
            ->where('id_invitee', $inviteeUser->id_user)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['invitee_email' => 'User already has an active invitation.']);
        }

        // Create invitation with sequence safety (fix sequence misalignment if needed)
        $invitation = null;
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

        // Optional notification insert (ignore errors in prototype)
        try {
            DB::table('notification')->insert([
                'id_user'       => $inviteeUser->id_user,
                'message'       => 'You have been invited to an event',
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
            abort(403, 'You are not the invitee.');
        }

        // BR13: Admins cannot participate in events
        if (Gate::allows('admin')) {
            return back()->with('error', 'Administrators cannot participate in events.');
        }

        if ($invitation->status !== 'pending') {
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
        } catch (\Throwable $e) {
            // ignore if constraint/trigger rejects
        }

        return back()->with('success', 'Invitation accepted.');
    }

    /**
     * RU10 Decline invitation.
     */
    public function decline(Invitation $invitation)
    {
        if (!Auth::check() || Auth::id() !== $invitation->id_invitee) {
            abort(403, 'You are not the invitee.');
        }
        if ($invitation->status !== 'pending') {
            return back()->withErrors(['invitation' => 'Invitation already responded.']);
        }

        $invitation->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Invitation declined.');
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
