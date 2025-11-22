@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Upcoming Events</h2>
                    <p class="mt-1 text-sm text-slate-500">Discover what is happening soon on EventSquare.</p>
                </div>

                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    <span>Browse all events</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>

            @if ($events->isEmpty())
                <div class="rounded-3xl bg-white/90 p-10 text-center shadow-xl">
                    <p class="text-sm text-slate-500">No upcoming public events.</p>
                </div>
            @else
                <div class="mt-6">
                    <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($events as $event)
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
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
