<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Admin;
use App\Models\AdminAction;
use App\Models\AdminEventAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use App\Models\Invitation;
use App\Models\Notification;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()
            ->with('tags'); // Eager load tags for display (US03)

        // AD01: Admins can browse ALL events, regular users only see public/published
        if (!Gate::allows('admin')) {
            // Global filter: Only published and future events (hides completed/canceled/draft/past)
            $query->where('status', 'published')
                  ->where('start_at', '>', now());

            $query->where(function ($q) {
                // Public events
                $q->where('visibility', 'public');

                // OR events where the user is involved (participant, invited, organizer)
                if (Auth::check()) {
                    $userId = Auth::id();
                    $q->orWhereHas('participations', function ($p) use ($userId) {
                        $p->where('id_user', $userId);
                    })->orWhereHas('invitations', function ($i) use ($userId) {
                        $i->where('id_invitee', $userId);
                    })->orWhere('id_organizer', $userId);
                }
            });
        }

        $search = trim((string) $request->input('q', ''));
        $tagFilters = $request->input('tags', []); // US03: Multiple tag-based exploration
        
        // Ensure tagFilters is always an array
        if (!is_array($tagFilters)) {
            $tagFilters = $tagFilters ? [$tagFilters] : [];
        }

        // US03: Filter by tags if provided (events must have ALL selected tags)
        if (!empty($tagFilters)) {
            foreach ($tagFilters as $tagName) {
                $query->whereHas('tags', function ($q) use ($tagName) {
                    $q->where('name', $tagName);
                });
            }
        }

        // US05: Sort Events by Date
        $sort = $request->input('sort', 'date_asc');

        // 3.14: Full-text search with weighted ranking (IDX04 in EBD A6)
        // Uses search_fts tsvector column with weights: title='A', description/venue='B'
        if ($search !== '') {
            // Convert search terms to tsquery format with prefix matching
            $tsquery = $this->buildTsQuery($search);
            
            if ($tsquery) {
                $query->whereRaw('search_fts @@ to_tsquery(\'simple\', ?)', [$tsquery]);

                if ($sort === 'relevance') {
                    $query->orderByRaw('ts_rank_cd(search_fts, to_tsquery(\'simple\', ?)) DESC', [$tsquery]);
                }
            }
        }

        // Apply date sorting
        if ($sort === 'date_asc') {
            $query->orderBy('start_at', 'asc');
        } elseif ($sort === 'date_desc') {
            $query->orderBy('start_at', 'desc');
        }

        $events = $query->paginate(10)->withQueryString();

        // US03: Get all available tags for the filter UI
        $allTags = Tag::orderBy('name')->get();

        return view('events.index', [
            'events' => $events,
            'search' => $search,
            'tagFilters' => $tagFilters,
            'allTags' => $allTags,
            'sort' => $sort,
        ]);
    }

    public function searchApi(Request $request)
    {
        $query = Event::query()
            ->with('tags') // US03: Include tags in API response
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->where('start_at', '>', now()); // BR14: Only future events

        $search = trim((string) $request->input('q', ''));
        $tagFilters = $request->input('tags', []); // US03: Multiple tag-based exploration
        
        // Ensure tagFilters is always an array
        if (!is_array($tagFilters)) {
            $tagFilters = $tagFilters ? [$tagFilters] : [];
        }

        // US03: Filter by tags if provided (events must have ALL selected tags)
        if (!empty($tagFilters)) {
            foreach ($tagFilters as $tagName) {
                $query->whereHas('tags', function ($q) use ($tagName) {
                    $q->where('name', $tagName);
                });
            }
        }

        // 3.14: Full-text search with weighted ranking (IDX04 in EBD A6)
        if ($search !== '') {
            $tsquery = $this->buildTsQuery($search);
            
            $query->whereRaw('search_fts @@ to_tsquery(\'simple\', ?)', [$tsquery])
                  ->orderByRaw('ts_rank_cd(search_fts, to_tsquery(\'simple\', ?)) DESC', [$tsquery])
                  ->orderBy('start_at', 'asc');
        } else {
            $query->orderBy('start_at', 'asc');
        }

        $events = $query->paginate(10);

        // US03: Transform response to include tag names
        $result = $events->map(function ($event) {
            return [
                'id' => $event->id_event,
                'title' => $event->title,
                'startAt' => $event->start_at,
                'endAt' => $event->end_at,
                'venue' => $event->venue,
                'tags' => $event->tags->pluck('name')->toArray(),
            ];
        });

        return response()->json($result);
    }

    // Show the form to create a new event.
    // Only for users who are logged in.
    public function create()
    {
        // If the user is NOT logged in, send them to the login page
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // BR13: Admins cannot create events
        if (Gate::allows('admin')) {
            return redirect()->route('events.index')
                ->with('error', 'Administrators cannot create events.');
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

        // BR13: Admins cannot create events
        if (Gate::allows('admin')) {
            return redirect()->route('events.index')
                ->with('error', 'Administrators cannot create events.');
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
        // BR02: Private events are hidden from search and public view.
        // Only accessible by: Organizer, Participants, Invitees, Admins.
        if ($event->visibility === 'private') {
            $canView = false;
            if (Auth::check()) {
                $user = Auth::user();
                // 1. Organizer
                if ($user->id_user === $event->id_organizer) $canView = true;
                // 2. Admin
                elseif (Gate::allows('admin')) $canView = true;
                // 3. Participant
                elseif ($event->participations()->where('id_user', $user->id_user)->whereNull('left_at')->exists()) $canView = true;
                // 4. Invitee (Pending or Accepted)
                elseif ($event->invitations()->where('id_invitee', $user->id_user)->whereIn('status', ['pending', 'accepted'])->exists()) $canView = true;
            }
            
            if (!$canView) {
                abort(403, 'This event is private.');
            }
        }

        // Eager-load invitations + invitee user to avoid N+ queries when listing invitations.
        $event->load([
            'invitations.invitee', 
            'applications.user', 
            'polls.options' => function($query) {
                $query->withCount('votes');
            },
            'polls.votes' // Load all votes to check user participation in view (acceptable for scale)
        ]);

        $isParticipant = Auth::check() ? $event->participations()->where('id_user', Auth::id())->whereNull('left_at')->exists() : false;

        return view('events.show', compact('event', 'isParticipant'));
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

        // BR06: Event edits locked 24h before start
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            abort(403, 'Events cannot be edited less than 24 hours before they start.');
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

        $currentParticipants = $event->participations()
            ->whereNull('left_at')
            ->count();

        if ($validated['capacity'] < $currentParticipants) {
            return back()
                ->withErrors([
                    'capacity' => 'Capacity cannot be lower than current attendees (' . $currentParticipants . ').',
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

        // AT09: Notify all current participants about the update
        $participants = $event->participants()->wherePivot('left_at', null)->get();
        
        foreach ($participants as $participant) {
            // Avoid notifying the organizer if they are also a participant
            if ($participant->id_user !== Auth::id()) {
                 Notification::create([
                    'id_user' => $participant->id_user,
                    'message' => 'event updated',
                    'id_event' => $event->id_event,
                    'created_at' => now(),
                ]);
            }
        }

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

        // BR06: Event cancellation locked 24h before start
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            return back()->with('error', 'Events cannot be canceled less than 24 hours before they start.');
        }

        // Update the event status to canceled
        $event->update(['status' => 'canceled']);

        // Cancel all pending invitations for this event
        Invitation::where('id_event', $event->id_event)
            ->where('status', 'pending')
            ->update(['status' => 'canceled', 'responded_at' => now()]);

        // AT09: Notify all current participants about the cancellation (as an update)
        $participants = $event->participants()->wherePivot('left_at', null)->get();
        
        foreach ($participants as $participant) {
            if ($participant->id_user !== Auth::id()) {
                 Notification::create([
                    'id_user' => $participant->id_user,
                    'message' => 'event canceled',
                    'id_event' => $event->id_event,
                    'created_at' => now(),
                ]);
            }
        }

        // Redirect to event details
        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event has been canceled successfully.');
    }
    
    
    // Delete an event (OR08 for organizers, AD03 for admins).
    // Organizers can only delete their own events with no activity.
    // Admins can delete any event to remove harmful content.
    public function destroy(Event $event)
    {
        $isAdmin = Gate::allows('admin');
        $isOrganizer = Auth::check() && Auth::id() === $event->id_organizer;

        // Must be either the organizer or an admin
        if (!$isAdmin && !$isOrganizer) {
            abort(403, 'You are not allowed to delete this event.');
        }

        // For organizers: check if the event can be hard deleted (no activity)
        // Admins can delete any event regardless of activity (AD03)
        if (!$isAdmin && !$event->can_hard_delete) {
            return redirect()
                ->route('events.mine')
                ->with('error', 'This event already has activity. Please cancel it instead of deleting.');
        }

        // Store event info for logging before deletion
        $eventTitle = $event->title;
        $eventId = $event->id_event;

        // Use transaction for admin actions to ensure audit log is created
        DB::transaction(function () use ($event, $isAdmin, $eventTitle, $eventId) {
            // AD03: Log admin action if admin is deleting
            if ($isAdmin) {
                $admin = Admin::where('email', Auth::user()->email)->first();
                if ($admin) {
                    $action = AdminAction::create([
                        'id_admin'   => $admin->id_admin,
                        'details'    => 'Deleted event: ' . $eventTitle . ' (ID: ' . $eventId . ')',
                        'created_at' => now(),
                    ]);

                    AdminEventAction::create([
                        'id_action'    => $action->id_action,
                        'action'       => 'delete event',
                        'target_event' => $eventId,
                    ]);
                }
            }

            // Delete the event from the database.
            // Foreign key cascading handles related rows.
            $event->delete();
        });

        // Redirect based on who deleted
        if ($isAdmin) {
            return redirect()
                ->route('events.index')
                ->with('success', 'Event "' . $eventTitle . '" deleted successfully by administrator.');
        }

        // After deleting, send the organizer back to "My events" page with a success message.
        return redirect()
            ->route('events.mine')
            ->with('success', 'Event deleted successfully!');
    }


//Apply to an event (RU09)
    public function apply(Event $event)
    {
        $user = Auth::user(); //user autenticado

        // BR13: Admins cannot participate in events
        if (Gate::allows('admin')) {
            return back()->with('error', 'Administrators cannot participate in events.');
        }

        // Cannot apply to non-published events (including canceled)
        if ($event->status !== 'published') {
            return back()->with('error', 'You can only apply to published events.');
        }

        // Cannot apply if event has ended
        if ($event->is_past) {
        return back()->with('error', 'You can no longer apply to a past event.');
        }

        // Cannot apply if event full
        if ($event->is_full) {
            return back()->with('error', 'This event is already full.');
        }

        // Cannot apply twice, but allow re-apply if previously rejected
        $existingApplication = $event->applications()
            ->where('id_user', $user->id_user)
            ->first();

        if ($existingApplication) {
            if (in_array($existingApplication->status, ['rejected', 'canceled'])) {
                $existingApplication->update([
                    'status' => 'pending',
                    'created_at' => now(),
                    'decided_at' => null,
                ]);

                return back()->with('success', 'Your request to join this event was submitted!');
            }

            return back()->with('error', 'You have already applied to this event.');
        }

        // Cannot apply if already participant
        $alreadyParticipant = $event->participants()
            ->where('participation.id_user', $user->id_user)
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


    // Leave an event (AT01)
    public function leave(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user is actually participating
        $participation = $event->participations()
            ->where('id_user', Auth::id())
            ->whereNull('left_at')
            ->first();

        if (!$participation) {
            return back()->with('error', 'You are not participating in this event.');
        }

        // BR05: Participant activities locked 24h before event
        if ($event->start_at->copy()->subHours(24)->isPast()) {
             return back()->with('error', 'You cannot leave the event less than 24 hours before it starts.');
        }

        // Update left_at
        $participation->left_at = now();
        $participation->save();

        // Notify organizer
        \App\Models\Notification::create([
            'id_user' => $event->id_organizer,
            'message' => 'event updated',
            'id_event' => $event->id_event,
            'created_at' => now(),
        ]);

        // Update application status if exists
        $application = $event->applications()
            ->where('id_user', Auth::id())
            ->first();
            
        if ($application) {
            $application->status = 'canceled';
            $application->save();
        }

        return back()->with('success', 'You have left the event.');
    }

    /**
     * 3.14: Build a PostgreSQL tsquery string from user search input.
     * 
     * Converts user input like "python workshop" into "python:* & workshop:*"
     * for prefix matching with the 'simple' text search configuration.
     * Sanitizes input to prevent SQL injection via tsquery syntax.
     *
     * @param string $search User's search input
     * @return string PostgreSQL tsquery-compatible string
     */
    private function buildTsQuery(string $search): string
    {
        // Split by whitespace, remove empty entries
        $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
        
        // Sanitize each word: keep only alphanumeric chars, add prefix matching
        $terms = array_map(function ($word) {
            $clean = preg_replace('/[^a-zA-Z0-9]/', '', $word);
            return $clean !== '' ? $clean . ':*' : null;
        }, $words);
        
        // Filter out empty terms and join with AND operator
        $terms = array_filter($terms);
        
        return implode(' & ', $terms) ?: '';
    }
}