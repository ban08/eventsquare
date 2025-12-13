<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;
use Carbon\Carbon;

class EventController extends Controller
{
        public function home()
    {
        $events = Event::query()
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(6)
            ->get();

        return view('home', [
            'events' => $events,
        ]);
    }

    public function index(Request $request)
    {
        $query = Event::query()
            ->where('visibility', 'public')
            ->where('status', 'published');

        $search = trim((string) $request->input('q', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('title', 'ILIKE', $like)
                  ->orWhere('description', 'ILIKE', $like)
                  ->orWhere('venue', 'ILIKE', $like);
            })->orderBy('start_at', 'asc');
        } else {
            $query->orderBy('start_at', 'asc');
        }

        $events = $query->paginate(10)->withQueryString();

        return view('events.index', [
            'events' => $events,
            'search' => $search,
        ]);
    }

    public function searchApi(Request $request)
    {
        $query = Event::query()
            ->where('visibility', 'public')
            ->where('status', 'published');

        $search = trim((string) $request->input('q', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('title', 'ILIKE', $like)
                  ->orWhere('description', 'ILIKE', $like)
                  ->orWhere('venue', 'ILIKE', $like);
            })->orderBy('start_at', 'asc');
        } else {
            $query->orderBy('start_at', 'asc');
        }

        $events = $query
            ->select([
                'id_event as id',
                'title',
                'start_at as startAt',
                'end_at as endAt',
                'venue',
            ])
            ->paginate(10);

        return response()->json($events->items());
    }

    // Show the form to create a new event.
    // Only for users who are logged in.
    public function create()
    {
        // If the user is NOT logged in, send them to the login page
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get all tags from the database so we can show them in the form.
        $tags = Tag::all();

        // Show the "create event" view and pass the tags to it.
        return view('events.create', compact('tags'));
    }



    // Store a newly created event in the database.
    public function store(Request $request)
    {
        // Make sure only logged-in users can create events.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Validate the form data
        // This checks that the user filled in the fields correctly.
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['required', 'date', 'after:start_at'],
            'venue'       => ['required', 'string', 'max:255'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'visibility'  => ['required', 'in:public,private'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tag,id_tag'],            
        ]);

        // 2. Extra rule: start_at at least 48 hours from now.
        $startAt = Carbon::parse($validated['start_at']);

        // If start time is earlier than "now + 48 hours"
        if ($startAt->lt(now()->addHours(48))) {
            // Go back to the form with an error message and keep the old input.
            return back()
                ->withErrors([
                    'start_at' => 'The event must start at least 48 hours from now.'
                ])
                ->withInput();
        }

        // 3. Create and save the new event in the database
        $event = Event::create([
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'start_at'     => $validated['start_at'],
            'end_at'       => $validated['end_at'],
            'venue'        => $validated['venue'],
            'capacity'     => $validated['capacity'],
            'visibility'   => $validated['visibility'],
            'status'       => 'published',                // new events start as "published".
            'id_organizer' => Auth::id(),                 // current user is the organizer.
        ]);

        // 4. If the user selected any tags, attach them to the event
        if (!empty($validated['tags'])) {
            $event->tags()->attach($validated['tags']);
        }        

        // 5. Redirect the user to the event page with a success message.
        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event created successfully!');
    }

    // Display a single event so that people can find it.
    public function show(Event $event)
    {
        // Eager-load invitations + invitee user to avoid N+ queries when listing invitations.
        $event->load(['invitations.invitee']);
        return view('events.show', compact('event'));
    }


    // List events created by the logged-in user ("My Events").
    public function mine()
    {
        // If not logged in, send to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get events organized by the user
        $organizedEvents = Event::where('id_organizer', Auth::id())
            ->orderBy('start_at', 'asc')
            ->get();

        // Get events where the user is a participant (has not left)
        $participatedEvents = Event::whereHas('participations', function ($query) {
                $query->where('id_user', Auth::id())
                      ->whereNull('left_at');
            })
            ->orderBy('start_at', 'asc')
            ->get();

        // Show a dedicated "my events" view with both types of events
        return view('events.mine', [
            'organizedEvents' => $organizedEvents,
            'participatedEvents' => $participatedEvents
        ]);
    }   
    
    // Show the form for editing an existing event
    public function edit(Event $event)
    {
        // Only the organizer can edit this event
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to edit this event.');
        }

        // Completed events cannot be edited
        if ($event->is_past) {
            abort(403, 'Completed events cannot be edited.');
        }

        // Canceled events cannot be edited
        if ($event->status === 'canceled') {
            abort(403, 'Canceled events cannot be edited.');
        }

        // All tags in the system
        $tags = Tag::all();

        // IDs of tags already attached to this event
        $selectedTags = $event->tags->pluck('id_tag')->toArray();

        return view('events.edit', compact('event', 'tags', 'selectedTags'));        
    } 
    

    // Update an existing event in the database
    public function update(Request $request, Event $event)
    {
        // Only the organizer may update
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to edit this event.');
        }


        // Completed events cannot be edited
        if ($event->is_past) {
            abort(403, 'Completed events cannot be edited.');
        }
        // Canceled events cannot be edited
        if ($event->status === 'canceled') {
            abort(403, 'Canceled events cannot be edited.');
        }
        
        // 1. Validate inputs (same as in store())
        $validated = $request->validate([
            // 'title'       => ['required', 'string', 'max:255'], --- don't edit
            'description' => ['nullable', 'string'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['required', 'date', 'after:start_at'],
            'venue'       => ['required', 'string', 'max:255'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'visibility'  => ['required', 'in:public,private'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tag,id_tag'],
        ]);

        // 2. Keep the "48 hours from now" rule
        $startAt = Carbon::parse($validated['start_at']);

        if ($startAt->lt(now()->addHours(48))) {
            return back()
                ->withErrors([
                    'start_at' => 'The event must start at least 48 hours from now.',
                ])
                ->withInput();
        }

        // 3. Update event fields
        $event->update([
            // 'title'       => $validated['title'], --- don't edit
            'description' => $validated['description'] ?? null,
            'start_at'    => $validated['start_at'],
            'end_at'      => $validated['end_at'],
            'venue'       => $validated['venue'],
            'capacity'    => $validated['capacity'],
            'visibility'  => $validated['visibility']
        ]);

        // 4. Update tags (sync replaces existing tags with the new list)
        $event->tags()->sync($validated['tags'] ?? []);

        // 5. Redirect back to event details
        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event updated successfully!');
    }

    // Cancel an event.
    // Only the organizer can cancel, and only published events can be canceled.
    public function cancel(Event $event)
    {
        // If user is not logged in OR is not the organizer, forbid access
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to cancel this event.');
        }

        // Only published events can be canceled
        if ($event->status !== 'published') {
            return back()->with('error', 'Only published events can be canceled.');
        }

        // Update the event status to canceled
        $event->update(['status' => 'canceled']);

        // Redirect back to the event details with success message
        return back()->with('success', 'Event has been canceled successfully.');
    }
    
    
    // Delete an event (OR08).
    // Only the organizer who created the event can delete it.
    public function destroy(Event $event)
    {
        // If user is not logged in OR is not the organizer, forbid access
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to delete this event.');
        }

        // Check if the event can be hard deleted (no activity)
        if (!$event->can_hard_delete) {
            return redirect()
                ->route('events.mine')
                ->with('error', 'This event already has activity. Please cancel it instead of deleting.');
        }

        // Delete the event from the database.
        // If the database is set up with foreign key cascading,
        // related rows will be removed automatically.
        $event->delete();

        // After deleting, send the user back to "My events" page with a success message.
        return redirect()
            ->route('events.mine')
            ->with('success', 'Event deleted successfully!');
    }

    public function apply(Event $event)
    {
        $user = Auth::user();

        // Cannot apply to non-published events (including canceled)
        if ($event->status !== 'published') {
            return back()->with('error', 'You can only apply to published events.');
        }

        // Cannot apply if event full
        if ($event->is_full) {
            return back()->with('error', 'This event is already full.');
        }

        // Cannot apply twice
        $alreadyApplied = $event->applications()
            ->where('id_user', $user->id_user)
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'You have already applied to this event.');
        }

        // Cannot apply if already participant
        $alreadyParticipant = $event->participants()
            ->where('id_user', $user->id_user)
            ->exists();

        if ($alreadyParticipant) {
            return back()->with('error', 'You are already participating in this event.');
        }

        // Create new application
        $event->applications()->create([
            'id_user' => $user->id_user,
            'status'   => 'pending',    
            'created_at' => now()
        ]);

        return back()->with('success', 'Your request to join this event was submitted!');
    }

    
}
