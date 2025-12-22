<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
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
use App\Models\PollVote;

/**
 * EventController
 * 
 * Manages the lifecycle of events including creation, updates, deletion,
 * and participation. Implements complex business rules for visibility,
 * capacity management, and time-based restrictions.
 */
class EventController extends Controller
{
    /**
     * Display a listing of events.
     * Implements search, filtering, and visibility rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Event::query()
            ->with('tags');

        // AD01: Admins can browse ALL events, regular users only see public/published
        if (!Gate::allows('admin')) {
            // Global filter: Only published and future events
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
                        $i->where('id_invitee', $userId)
                          ->whereIn('status', ['pending', 'accepted']);
                    })->orWhere('id_organizer', $userId);
                }
            });
        }

        $search = trim((string) $request->input('q', ''));
        $tagFilters = $request->input('tags', []);
        
        if (!is_array($tagFilters)) {
            $tagFilters = $tagFilters ? [$tagFilters] : [];
        }

        // US03: Filter by tags if provided (events must have ALL selected tags)
        if (!empty($tagFilters)) {
            $allTagNames = Tag::pluck('name')->toArray();
            $validTags = array_intersect($tagFilters, $allTagNames);

            foreach ($validTags as $tagName) {
                $query->whereHas('tags', function ($q) use ($tagName) {
                    $q->where('name', $tagName);
                });
            }
        }

        // US05: Sort Events by Date
        $sort = $request->input('sort', 'date_asc');

        // 3.14: Full-text search with weighted ranking
        if ($search !== '') {
            $tsquery = $this->buildTsQuery($search);
            
            if ($tsquery) {
                $query->whereRaw('search_fts @@ to_tsquery(\'english\', ?)', [$tsquery]);

                if ($sort === 'relevance') {
                    $query->orderByRaw('ts_rank_cd(search_fts, to_tsquery(\'english\', ?)) DESC', [$tsquery]);
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

    /**
     * API endpoint for event search.
     * Returns JSON response for AJAX search functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchApi(Request $request)
    {
        $query = Event::query()
            ->with('tags')
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->where('start_at', '>', now()); // BR14: Only future events

        $search = trim((string) $request->input('q', ''));
        $tagFilters = $request->input('tags', []);
        
        if (!is_array($tagFilters)) {
            $tagFilters = $tagFilters ? [$tagFilters] : [];
        }

        // US03: Filter by tags if provided
        if (!empty($tagFilters)) {
            $allTagNames = Tag::pluck('name')->toArray();
            $validTags = array_intersect($tagFilters, $allTagNames);

            foreach ($validTags as $tagName) {
                $query->whereHas('tags', function ($q) use ($tagName) {
                    $q->where('name', $tagName);
                });
            }
        }

        // 3.14: Full-text search with weighted ranking
        if ($search !== '') {
            $tsquery = $this->buildTsQuery($search);
            
            $query->whereRaw('search_fts @@ to_tsquery(\'english\', ?)', [$tsquery])
                  ->orderByRaw('ts_rank_cd(search_fts, to_tsquery(\'english\', ?)) DESC', [$tsquery])
                  ->orderBy('start_at', 'asc');
        } else {
            $query->orderBy('start_at', 'asc');
        }

        $events = $query->paginate(10);

        // Transform response to include tag names
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

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // BR13: Admins cannot create events
        if (Gate::allows('admin')) {
            return redirect()->route('events.index')
                ->with('error', 'Administrators cannot create events.');
        }

        $tags = Tag::all();

        return view('events.create', compact('tags'));
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // BR13: Admins cannot create events
        if (Gate::allows('admin')) {
            return redirect()->route('events.index')
                ->with('error', 'Administrators cannot create events.');
        }

        // 1. Validate input parameters
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

        // 2. Enforce business rule: Event must start at least 48 hours from creation
        $startAt = Carbon::parse($validated['start_at']);

        if ($startAt->lt(now()->addHours(48))) {
            return back()
                ->withErrors([
                    'start_at' => 'The event must start at least 48 hours from now.'
                ])
                ->withInput();
        }

        // 3. Persist event to database
        $event = Event::create([
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'start_at'     => $validated['start_at'],
            'end_at'       => $validated['end_at'],
            'venue'        => $validated['venue'],
            'capacity'     => $validated['capacity'],
            'visibility'   => $validated['visibility'],
            'status'       => 'published',
            'id_organizer' => Auth::id(),
        ]);

        // 4. Attach tags if provided
        if (!empty($validated['tags'])) {
            $event->tags()->attach($validated['tags']);
        }        

        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\View\View
     */
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

        // Eager-load relationships
        $event->load([
            'invitations.invitee', 
            'applications.user', 
            'polls.options' => function($query) {
                $query->withCount('votes');
            },
            'polls.votes'
        ]);

        $isParticipant = Auth::check() ? $event->participations()->where('id_user', Auth::id())->whereNull('left_at')->exists() : false;

        return view('events.show', compact('event', 'isParticipant'));
    }

    /**
     * List events created by or participated in by the logged-in user.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function mine()
    {
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
    
    /**
     * Show the form for editing the specified event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\View\View
     */
    public function edit(Event $event)
    {
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to edit this event.');
        }

        if ($event->is_past) {
            abort(403, 'Completed events cannot be edited.');
        }

        if ($event->status === 'canceled') {
            abort(403, 'Canceled events cannot be edited.');
        }

        $tags = Tag::all();
        $selectedTags = $event->tags->pluck('id_tag')->toArray();

        return view('events.edit', compact('event', 'tags', 'selectedTags'));        
    } 
    
    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Event $event)
    {
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to edit this event.');
        }

        if ($event->is_past) {
            abort(403, 'Completed events cannot be edited.');
        }

        if ($event->status === 'canceled') {
            abort(403, 'Canceled events cannot be edited.');
        }

        // BR06: Event edits locked 24h before start
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            abort(403, 'Events cannot be edited less than 24 hours before they start.');
        }
        
        // 1. Validate inputs
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['required', 'date', 'after:start_at'],
            'venue'       => ['required', 'string', 'max:255'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'visibility'  => ['required', 'in:public,private'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tag,id_tag'],
        ]);

        // Check if capacity is lower than current attendees
        if ($validated['capacity'] < $event->current_participants_count) {
            return back()
                ->withErrors([
                    'capacity' => 'Capacity cannot be lower than the current number of attendees (' . $event->current_participants_count . ').',
                ])
                ->withInput();
        }

        // 3. Update event fields
        $event->update([
            'description' => $validated['description'] ?? null,
            'start_at'    => $validated['start_at'],
            'end_at'      => $validated['end_at'],
            'venue'       => $validated['venue'],
            'capacity'    => $validated['capacity'],
            'visibility'  => $validated['visibility']
        ]);

        // 4. Update tags
        $event->tags()->sync($validated['tags'] ?? []);

        // AT09: Notify all current participants about the update
        $participants = $event->participants()->wherePivot('left_at', null)->get();
        
        foreach ($participants as $participant) {
            if ($participant->id_user !== Auth::id()) {
                 Notification::create([
                    'id_user' => $participant->id_user,
                    'message' => 'event updated',
                    'id_event' => $event->id_event,
                    'created_at' => now(),
                ]);
            }
        }

        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Cancel the specified event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Event $event)
    {
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'You are not allowed to cancel this event.');
        }

        if ($event->status !== 'published') {
            return back()->with('error', 'Only published events can be canceled.');
        }

        // BR06: Event cancellation locked 24h before start
        if ($event->start_at->copy()->subHours(24)->isPast()) {
            return back()->with('error', 'Events cannot be canceled less than 24 hours before they start.');
        }

        $event->update(['status' => 'canceled']);

        // Cancel all pending invitations
        Invitation::where('id_event', $event->id_event)
            ->where('status', 'pending')
            ->update(['status' => 'canceled', 'responded_at' => now()]);

        // AT09: Notify all current participants
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

        return redirect()
            ->route('events.show', $event->id_event)
            ->with('success', 'Event has been canceled successfully.');
    }
    
    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Event $event)
    {
        $isAdmin = Gate::allows('admin');
        $isOrganizer = Auth::check() && Auth::id() === $event->id_organizer;

        if (!$isAdmin && !$isOrganizer) {
            abort(403, 'You are not allowed to delete this event.');
        }

        // For organizers: check if the event can be hard deleted (no attendees)
        if (!$isAdmin && !$event->can_hard_delete) {
            if ($event->status === 'canceled') {
                 return redirect()
                    ->route('events.mine')
                    ->with('error', 'This event is already canceled and cannot be deleted because it has history.');
            }
            
            return redirect()
                ->route('events.mine')
                ->with('error', 'This event has attendees. Please cancel it instead of deleting.');
        }

        $eventTitle = $event->title;
        $eventId = $event->id_event;

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

            $event->delete();
        });

        if ($isAdmin) {
            return redirect()
                ->route('events.index')
                ->with('success', 'Event "' . $eventTitle . '" deleted successfully by administrator.');
        }

        return redirect()
            ->route('events.mine')
            ->with('success', 'Event deleted successfully!');
    }


    /**
     * Handle a user applying to join an event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function apply(Event $event)
    {
        $user = Auth::user();

        // BR13: Admins cannot participate in events
        if (Gate::allows('admin')) {
            return back()->with('error', 'Administrators cannot participate in events.');
        }

        if ($event->status !== 'published') {
            return back()->with('error', 'You can only apply to published events.');
        }

        if ($event->is_past) {
        return back()->with('error', 'You can no longer apply to a past event.');
        }

        if ($event->is_full) {
            return back()->with('error', 'This event is already full.');
        }

        if ($event->id_organizer === $user->id_user) {
            return back()->with('error', 'You cannot join your own event.');
        }

        // Check for existing application
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

        // Check for pending invitation (Auto-accept if exists)
        $pendingInvitation = \App\Models\Invitation::where('id_event', $event->id_event)
            ->where('id_invitee', $user->id_user)
            ->where('status', 'pending')
            ->first();

        if ($pendingInvitation) {
            DB::transaction(function () use ($event, $user, $pendingInvitation) {
                // Accept invitation
                $pendingInvitation->update([
                    'status' => 'accepted',
                    'responded_at' => now()
                ]);

                // Create participation
                $event->participants()->attach($user->id_user, ['joined_at' => now()]);

                // Notify organizer
                \App\Models\Notification::create([
                    'id_user' => $event->id_organizer,
                    'message' => 'user joined',
                    'id_event' => $event->id_event,
                    'id_invitation' => $pendingInvitation->id_invitation,
                    'created_at' => now(),
                ]);
            });

            return back()->with('success', 'You had a pending invitation. You have successfully joined the event!');
        }

        // Create new application
        $event->applications()->create([
            'id_user' => $user->id_user,
            'status'   => 'pending',    
            'created_at' => now()
        ]);

        return back()->with('success', 'Your request to join this event was submitted!');
    }


    /**
     * Handle a user leaving an event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leave(Event $event)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

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

        //Erase user poll votes when he leaves the event
        PollVote::where('id_participation', $participation->id_participation)->delete();

        // Update left_at
        $participation->left_at = now();
        $participation->save();

        // Remove poll votes associated with this participation
        DB::table('poll_vote')->where('id_participation', $participation->id_participation)->delete();

        // Clear all notifications related to this event for the user
        Notification::where('id_user', Auth::id())
            ->where('id_event', $event->id_event)
            ->delete();

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
     * Remove a participant from an event (Organizer action).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeParticipant(Request $request, Event $event, User $user)
    {
        if (!Auth::check() || Auth::id() !== $event->id_organizer) {
            abort(403, 'Only the organizer can remove participants.');
        }

        if ($user->id_user === $event->id_organizer) {
            return back()->with('error', 'You cannot remove yourself from the event.');
        }

        $participation = $event->participations()
            ->where('id_user', $user->id_user)
            ->whereNull('left_at')
            ->first();

        if (!$participation) {
            return back()->with('error', 'User is not a participant.');
        }

        // Mark as left
        $participation->update(['left_at' => now()]);

        // Remove votes
        DB::table('poll_vote')->where('id_participation', $participation->id_participation)->delete();

        // Update application status if exists
        $application = $event->applications()
            ->where('id_user', $user->id_user)
            ->first();
            
        if ($application) {
            $application->status = 'rejected';
            $application->save();
        }

        return back()->with('success', 'Participant removed successfully.');
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