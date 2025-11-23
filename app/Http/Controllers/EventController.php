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
        // Show the "show event" view and pass the event to it.
        return view('events.show', compact('event'));
    }
}
