<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

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

    public function show(Event $event)
    {
        abort_unless($event->visibility === 'public' && $event->status === 'published', 404);

        return view('events.show', [
            'event' => $event,
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
}
