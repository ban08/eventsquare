@extends('layouts.app')

@section('title', 'Events')

@section('content')
    <h2>Events</h2>

    <form method="GET" action="{{ route('events.index') }}" id="event-search-form">
        <input type="text" name="q" id="event-search-input" value="{{ $search }}" placeholder="Search events...">
        <button type="submit">Search</button>
    </form>

    <ul class="event-list" id="event-list">
        @forelse ($events as $event)
            <li>
                <a href="{{ route('events.show', $event->id_event) }}">{{ $event->title }}</a>
                <span>{{ $event->start_at }}</span>
                <span>{{ $event->venue }}</span>
            </li>
        @empty
            <li>No events found.</li>
        @endforelse
    </ul>

    {{ $events->links() }}
@endsection

@push('scripts')
    <script type="module">
        const input = document.getElementById('event-search-input');
        const list = document.getElementById('event-list');

        if (input && list) {
            let controller = null;

            const renderEvents = (events) => {
                list.innerHTML = '';

                if (!events.length) {
                    const li = document.createElement('li');
                    li.textContent = 'No events found.';
                    list.appendChild(li);
                    return;
                }

                for (const event of events) {
                    const li = document.createElement('li');

                    const link = document.createElement('a');
                    link.href = `/events/${event.id}`;
                    link.textContent = event.title;

                    const spanDate = document.createElement('span');
                    spanDate.textContent = event.startAt ?? '';

                    const spanVenue = document.createElement('span');
                    spanVenue.textContent = event.venue ?? '';

                    li.appendChild(link);
                    li.appendChild(spanDate);
                    li.appendChild(spanVenue);

                    list.appendChild(li);
                }
            };

            const fetchResults = async (query) => {
                if (controller) controller.abort();
                controller = new AbortController();

                const params = new URLSearchParams();
                if (query) params.set('q', query);

                try {
                    const response = await fetch(`/api/events?${params.toString()}`, {
                        signal: controller.signal,
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    renderEvents(data);
                } catch (_) {
                    // Ignore aborted/failed requests; keep current list
                }
            };

            let timeoutId = null;
            input.addEventListener('input', () => {
                const value = input.value.trim();
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => fetchResults(value), 250);
            });
        }
    </script>
@endpush
