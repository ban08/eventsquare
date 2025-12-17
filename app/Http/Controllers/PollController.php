<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Poll;
use App\Models\PollOption;
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

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255'],
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
