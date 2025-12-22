<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display the notifications page.
     * Shows notifications and pending invitations (RU10).
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        // Get unread notifications only
        $notifications = Notification::with(['event', 'invitation'])
            ->where('id_user', $userId)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Get pending invitations (RU10)
        // Filter out invitations for events that have already ended (completed)
        $pendingInvitations = Invitation::with(['event.organizer'])
            ->where('id_invitee', $userId)
            ->where('status', 'pending')
            ->whereHas('event', function ($query) {
                $query->where('end_at', '>', now());
            })
            ->orderByDesc('sent_at')
            ->get();

        // Get responded invitations history
        // Filter: Only future events.
        // Show 'accepted' only if currently participating.
        // Show 'declined' only if NOT participating.
        $respondedInvitations = Invitation::with(['event'])
            ->where('id_invitee', $userId)
            ->where('status', '!=', 'pending')
            ->whereHas('event', function ($query) {
                $query->where('start_at', '>', now());
            })
            ->where(function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('status', 'accepted')
                      ->whereExists(function ($sq) use ($userId) {
                          $sq->select(DB::raw(1))
                             ->from('participation')
                             ->whereColumn('participation.id_event', 'invitation.id_event')
                             ->where('participation.id_user', $userId)
                             ->whereNull('left_at');
                      });
                })
                ->orWhere(function ($q) use ($userId) {
                    $q->where('status', 'declined')
                      ->whereNotExists(function ($sq) use ($userId) {
                          $sq->select(DB::raw(1))
                             ->from('participation')
                             ->whereColumn('participation.id_event', 'invitation.id_event')
                             ->where('participation.id_user', $userId)
                             ->whereNull('left_at');
                      });
                });
            })
            ->orderByDesc('responded_at')
            ->limit(20)
            ->get();

        // Count unread notifications
        $unreadCount = Notification::where('id_user', $userId)
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', compact(
            'notifications',
            'pendingInvitations',
            'respondedInvitations',
            'unreadCount'
        ));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if (!Auth::check() || Auth::id() !== $notification->id_user) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized.');
        }

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        // Redirect back to the activity tab
        return redirect()->route('notifications.index', ['tab' => 'activity'])
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        if (!Auth::check()) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        Notification::where('id_user', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }

        // Redirect back to the activity tab
        return redirect()->route('notifications.index', ['tab' => 'activity'])
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Get the current unread notification count.
     */
    public function check()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        $count = Notification::where('id_user', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
