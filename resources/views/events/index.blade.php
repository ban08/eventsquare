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
                    {{-- US03: Preserve tag filters in search form --}}
                    @foreach($tagFilters as $tagName)
                        <input type="hidden" name="tags[]" value="{{ $tagName }}">
                    @endforeach
                    {{-- US05: Preserve sort order --}}
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <button type="submit"
                            class="hidden sm:inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        Search
                    </button>
                </form>
            </div>

            {{-- US03: Tag-based exploration filter (multi-select) --}}
            <div class="mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-slate-600">Filter by tags:</span>
                    <a href="{{ route('events.index', request()->only(['q', 'sort'])) }}"
                       class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium transition
                              {{ empty($tagFilters) ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                        All
                    </a>
                    @foreach($allTags as $tag)
                        @php
                            $isSelected = in_array($tag->name, $tagFilters);
                            // Build new tags array: toggle current tag
                            if ($isSelected) {
                                $newTags = array_values(array_diff($tagFilters, [$tag->name]));
                            } else {
                                $newTags = array_merge($tagFilters, [$tag->name]);
                            }
                            $params = request()->only(['q', 'sort']);
                            if (!empty($newTags)) {
                                $params['tags'] = $newTags;
                            }
                        @endphp
                        <a href="{{ route('events.index', $params) }}"
                           class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-medium transition capitalize
                                  {{ $isSelected ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                            @if($isSelected)
                                <i class="fas fa-check text-[10px]"></i>
                            @endif
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
                @if(!empty($tagFilters))
                    <p class="mt-2 text-sm text-slate-500">
                        Showing events with tags: 
                        @foreach($tagFilters as $tagName)
                            <strong class="capitalize">{{ $tagName }}</strong>@if(!$loop->last), @endif
                        @endforeach
                        <a href="{{ route('events.index', request()->only(['q', 'sort'])) }}" class="text-indigo-600 hover:underline ml-1">Clear all</a>
                    </p>
                @endif
            </div>

            {{-- US05: Sort Controls --}}
            <div class="flex justify-end mb-4">
                <form method="GET" action="{{ route('events.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $search }}">
                    @foreach($tagFilters as $tag)
                        <input type="hidden" name="tags[]" value="{{ $tag }}">
                    @endforeach
                    
                    <label for="sort" class="text-sm font-medium text-slate-700">Sort by:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()" 
                            class="rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-3 pr-8 shadow-sm cursor-pointer">
                        <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Date (Soonest)</option>
                        <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Date (Latest)</option>
                        @if($search)
                            <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Relevance</option>
                        @endif
                    </select>
                </form>
            </div>

            <div class="mt-8" id="event-list-wrapper">
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" id="event-list">
                    @forelse ($events as $event)
                        <li>
                            <a href="{{ route('events.show', $event->id_event) }}"
                               class="group flex h-full flex-col rounded-3xl bg-white/95 p-4 shadow-[0_10px_30px_rgba(88,80,236,0.18)] transition hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(88,80,236,0.28)]">
                                <div class="mb-3 flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50"></div>

                                {{-- Title + status badge on same line  --}}
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 truncate">
                                        {{ $event->title }}
                                    </span>
                                    <span class="shrink-0 inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold
                                        @if($event->effective_status === 'published') bg-green-100 text-green-700
                                        @elseif($event->effective_status === 'completed') bg-blue-100 text-blue-700
                                        @elseif($event->effective_status === 'canceled') bg-red-100 text-red-700
                                        @elseif($event->effective_status === 'draft') bg-yellow-100 text-yellow-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        <i class="fas fa-clock"></i>
                                        {{ ucfirst($event->effective_status) }}
                                    </span>
                                </div>

                                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ $event->start_at }}</span>
                                    <span class="truncate text-right ml-2">{{ $event->venue }}</span>
                                </div>

                                {{-- US03: Display event tags --}}
                                <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
                                    <span class="rounded-full px-2 py-0.5 text-slate-600 {{ $event->visibility === 'public' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($event->visibility) }}
                                    </span>
                                    @forelse($event->tags as $tag)
                                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-600 capitalize">{{ $tag->name }}</span>
                                    @empty
                                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-600">Upcoming</span>
                                    @endforelse
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


