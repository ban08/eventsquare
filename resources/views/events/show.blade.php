@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('events.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to events
            </a>

            <article class="overflow-hidden rounded-3xl bg-white/95 shadow-[0_10px_30px_rgba(88,80,236,0.18)]">
                {{-- Hero / image placeholder --}}
                <div class="h-28 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-t-3xl"></div>

                <div class="p-6 sm:p-8">
                    {{-- Title + status chips --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-3xl font-semibold text-slate-900">{{ $event->title }}</h2>

                        <div class="flex flex-wrap gap-2 text-xs font-medium">
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">
                                <i class="fas fa-calendar-check"></i>
                                <span>Public event</span>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                <i class="fas fa-clock"></i>
                                <span>Upcoming</span>
                            </span>
                        </div>
                    </div>

                    {{-- Meta info --}}
                    <dl class="mt-6 grid gap-4 text-sm text-slate-600 sm:grid-cols-2">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="mt-1"><i class="fas fa-calendar-day text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Date &amp; time</p>
                                <p class="mt-1">{{ $event->start_at }}</p>
                            </dd>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="mt-1"><i class="fas fa-location-dot text-pink-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Venue</p>
                                <p class="mt-1">{{ $event->venue }}</p>
                            </dd>
                        </div>
                    </dl>

                    {{-- Description --}}
                    <div class="mt-8 border-t border-slate-100 pt-6">
                        <h3 class="text-sm font-medium text-slate-900">Description</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-700 max-w-3xl">{{ $event->description }}</p>
                    </div>

                    {{-- Call to action --}}
                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#4f46e5] px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#4338ca] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4f46e5] focus-visible:ring-offset-2 transition">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to all events</span>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </div>
@endsection
