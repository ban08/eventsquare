<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    /**
     * Store a newly created poll in storage.
     */
    public function store(Request $request, Event $event)
    {
        // Authorization: Only organizer can create polls
        if (Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can create polls.');
        }

        // Check if event is canceled
        if ($event->effective_status === 'canceled') {
            return back()->with('error', 'Cannot create polls for canceled events.');
        }

        // Activity deadline: 24h before event
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            return back()->with('error', 'Poll creation is locked 24 hours before the event.');
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        DB::transaction(function () use ($event, $validated) {
            $poll = Poll::create([
                'id_event' => $event->id_event,
                'question' => $validated['question'],
                'created_at' => now(),
            ]);

            foreach ($validated['options'] as $index => $label) {
                PollOption::create([
                    'id_poll' => $poll->id_poll,
                    'label' => $label,
                    'position' => $index + 1,
                ]);
            }
        });

        return redirect()->route('events.show', $event)
            ->with('success', 'Poll created successfully.');
    }

    /**
     * Vote on a poll.
     */
    public function vote(Request $request, Poll $poll)
    {
        $user = Auth::user();
        
        // Check if event is canceled
        if ($poll->event->effective_status === 'canceled') {
            return back()->with('error', 'Voting is disabled for canceled events.');
        }

        // Activity deadline: 24h before event
        if ($poll->event->start_at->copy()->subHours(24)->isPast()) {
            return back()->with('error', 'Voting is locked 24 hours before the event.');
        }

        // Check if user is participant
        $participation = $poll->event->participations()->where('id_user', $user->id_user)->first();

        if (!$participation) {
            return back()->with('error', 'You must join the event to vote.');
        }

        $validated = $request->validate([
            'option_id' => ['required', 'exists:poll_option,id_option'],
        ]);
        
        // Verify option belongs to poll
        $option = $poll->options()->where('id_option', $validated['option_id'])->firstOrFail();

        // Create or update vote
        // PK is composite: id_poll, id_participation
        
        // Check existing
        $existingVote = PollVote::where('id_poll', $poll->id_poll)
            ->where('id_participation', $participation->id_participation)
            ->first();

        if ($existingVote) {
            // Update
            PollVote::where('id_poll', $poll->id_poll)
                ->where('id_participation', $participation->id_participation)
                ->update(['id_option' => $option->id_option]);
        } else {
            // Create
            PollVote::create([
                'id_poll' => $poll->id_poll,
                'id_option' => $option->id_option,
                'id_participation' => $participation->id_participation,
                'created_at' => now(),
            ]);
        }

        if ($request->header('X-Requested-With') === 'XMLHttpRequest' || str_contains($request->header('Accept'), 'application/json')) {
            return response()->json([
                'success' => true,
                'html' => view('events.partials.poll-card', [
                    'poll' => $poll->refresh()->load([
                        'options' => fn($q) => $q->withCount('votes'),
                        'votes'
                    ]),
                    'event' => $poll->event
                ])->render()
            ], 200);
        }

        return back()->with('success', 'Vote recorded.');
    }

    /**
     * Remove a vote from a poll.
     */
    public function removeVote(Request $request, Poll $poll)
    {
        $user = Auth::user();
        // Check if user is participant
        $participation = $poll->event->participations()->where('id_user', $user->id_user)->first();

        if (!$participation) {
            return back()->with('error', 'You are not a participant.');
        }

        // Check if event is canceled
        if ($poll->event->effective_status === 'canceled') {
             return back()->with('error', 'Voting is disabled for canceled events.');
        }

        // Activity deadline: 24h before event
        if ($poll->event->start_at->copy()->subHours(24)->isPast()) {
            return back()->with('error', 'Voting is locked 24 hours before the event.');
        }

        PollVote::where('id_poll', $poll->id_poll)
            ->where('id_participation', $participation->id_participation)
            ->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('events.partials.poll-card', [
                    'poll' => $poll->refresh()->load([
                        'options' => fn($q) => $q->withCount('votes'),
                        'votes'
                    ]),
                    'event' => $poll->event
                ])->render()
            ], 200);
        }

        return back()->with('success', 'Vote removed.');
    }

    /**
     * Delete a poll.
     */
    public function destroy(Poll $poll)
    {
        $event = $poll->event;

        // Authorization: Only organizer can delete polls
        if (Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can delete polls.');
        }

        $poll->delete();

        return redirect()->route('events.show', $event)
            ->with('success', 'Poll deleted successfully.');
    }
}
