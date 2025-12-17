<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get unread notifications first, then read ones
        $notifications = Notification::with(['event', 'invitation'])
            ->where('id_user', $userId)
            ->orderByRaw('read_at IS NOT NULL') // Unread first
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
        $respondedInvitations = Invitation::with(['event'])
            ->where('id_invitee', $userId)
            ->where('status', '!=', 'pending')
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
}
