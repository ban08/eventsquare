@extends('layouts.app')

@section('title', 'Events')

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Events</h2>
                    <p class="mt-1 text-sm text-slate-500">Browse and search public events on EventSquare.</p>
                </div>

                <form method="GET" action="{{ route('events.index') }}" id="event-search-form"
                      class="flex w-full max-w-md items-center gap-2 rounded-full bg-white px-4 py-2 shadow-sm ring-1 ring-slate-200">
                    <i class="fas fa-magnifying-glass text-slate-400"></i>
                    <input
                        type="text"
                        name="q"
                        id="event-search-input"
                        value="{{ $search }}"
                        placeholder="Search events..."
                        class="w-full border-0 bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:ring-0"
                    >
                    <button type="submit"
                            class="hidden sm:inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        Search
                    </button>
                </form>
            </div>

            <div class="mt-8" id="event-list-wrapper">
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" id="event-list">
                    @forelse ($events as $event)
                        <li>
                            <a href="{{ route('events.show', $event->id_event) }}"
                               class="group flex h-full flex-col rounded-3xl bg-white/95 p-4 shadow-[0_10px_30px_rgba(88,80,236,0.18)] transition hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(88,80,236,0.28)]">
                                <div class="mb-3 flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50"></div>

                                <div class="mt-1 text-sm font-semibold text-slate-900 group-hover:text-indigo-700 truncate">
                                    {{ $event->title }}
                                </div>

                                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ $event->start_at }}</span>
                                    <span class="truncate text-right ml-2">{{ $event->venue }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600">Public</span>
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-600">Upcoming</span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="col-span-full py-6 text-center text-sm text-slate-500">No events found.</li>
                    @endforelse
                </ul>

                <div class="pt-4">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
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
                    li.className = 'col-span-full py-6 text-center text-sm text-slate-500';
                    li.textContent = 'No events found.';
                    list.appendChild(li);
                    return;
                }

                for (const event of events) {
                    const li = document.createElement('li');

                    const link = document.createElement('a');
                    link.href = `/events/${event.id}`;
                    link.className = 'group flex h-full flex-col rounded-3xl bg-white/95 p-4 shadow-[0_10px_30px_rgba(88,80,236,0.18)] transition hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(88,80,236,0.28)]';

                    const imgPlaceholder = document.createElement('div');
                    imgPlaceholder.className = 'mb-3 flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50';

                    const title = document.createElement('div');
                    title.className = 'mt-1 text-sm font-semibold text-slate-900 group-hover:text-indigo-700 truncate';
                    title.textContent = event.title;

                    const meta = document.createElement('div');
                    meta.className = 'mt-1 flex items-center justify-between text-xs text-slate-500';
                    meta.innerHTML = `
                        <span>${event.startAt ?? ''}</span>
                        <span class="truncate text-right ml-2">${event.venue ?? ''}</span>
                    `;

                    const tags = document.createElement('div');
                    tags.className = 'mt-3 flex flex-wrap gap-2 text-[11px]';
                    tags.innerHTML = `
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600">Public</span>
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-600">Upcoming</span>
                    `;

                    link.appendChild(imgPlaceholder);
                    link.appendChild(title);
                    link.appendChild(meta);
                    link.appendChild(tags);

                    li.appendChild(link);
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
