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
                <div class="h-8 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-t-3xl shadow-md"></div>

                <div class="p-8 sm:p-10 bg-white rounded-b-3xl shadow-lg">
                    {{-- Title + status chips --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-3xl font-bold text-indigo-700 tracking-tight">{{ $event->title }}</h2>

                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-indigo-700 shadow">
                                <i class="fas fa-calendar-check"></i>
                                <span>Public event</span>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-3 py-1 text-slate-700 shadow">
                                <i class="fas fa-clock"></i>
                                <span>Upcoming</span>
                            </span>
                        </div>
                    </div>

                    {{-- Meta info --}}
                    <dl class="mt-8 grid gap-6 text-base text-slate-700 sm:grid-cols-2">
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-calendar-day text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Date &amp; time</p>
                                <p class="mt-1">{{ $event->start_at }}</p>
                            </dd>
                        </div>
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-pink-50 to-indigo-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-location-dot text-pink-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Venue</p>
                                <p class="mt-1">{{ $event->venue }}</p>
                            </dd>
                        </div>
                    </dl>

                    {{-- Description --}}
                    <div class="mt-10 border-t border-slate-200 pt-8">
                        <h3 class="text-base font-semibold text-indigo-700 mb-2">Description</h3>
                        <p class="mt-2 text-base leading-relaxed text-slate-700 max-w-3xl">{{ $event->description }}</p>
                    </div>

                    {{-- Invitations prototype (OR03 / RU10) --}}
                    <div class="mt-10 border-t border-slate-200 pt-8">
                        <h3 class="text-base font-semibold text-indigo-700 mb-4">Invitations</h3>

                        @auth
                            {{-- Organizer invite form --}}
                            @if(Auth::id() === $event->id_organizer)
                                <form action="{{ route('invitations.invite', $event->id_event) }}" method="POST" class="mb-6 flex items-end gap-3">
                                    @csrf
                                    <div>
                                        <label for="invitee_email" class="block text-xs font-medium text-slate-600">Invite by email</label>
                                        <input type="email" name="invitee_email" id="invitee_email" required class="mt-1 w-64 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="user@example.com" />
                                        @error('invitee_email')
                                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Invite</span>
                                    </button>
                                </form>
                            @endif

                            {{-- List invitations (pending + responded) --}}
                            <div class="space-y-3">
                                @forelse($event->invitations as $inv)
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-2 text-sm">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-envelope text-indigo-500"></i>
                                            <span class="font-medium">Invitation #{{ $inv->id_invitation }}</span>
                                            <span>to user <strong>{{ $inv->invitee->name ?? ('ID ' . $inv->id_invitee) }}</strong></span>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
                                                @class([
                                                    'bg-yellow-100 text-yellow-700' => $inv->status === 'pending',
                                                    'bg-green-100 text-green-700' => $inv->status === 'accepted',
                                                    'bg-red-100 text-red-700' => $inv->status === 'declined',
                                                ])">
                                                {{ ucfirst($inv->status) }}
                                            </span>
                                        </div>
                                        {{-- Invitee action buttons if pending --}}
                                        @if(Auth::id() === $inv->id_invitee && $inv->status === 'pending')
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('invitations.accept', $inv->id_invitation) }}">
                                                    @csrf
                                                    <button class="inline-flex items-center rounded-full bg-green-600 px-3 py-1 text-white text-xs font-semibold shadow hover:bg-green-700">
                                                        <i class="fas fa-check mr-1"></i> Accept
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('invitations.decline', $inv->id_invitation) }}">
                                                    @csrf
                                                    <button class="inline-flex items-center rounded-full bg-red-600 px-3 py-1 text-white text-xs font-semibold shadow hover:bg-red-700">
                                                        <i class="fas fa-times mr-1"></i> Decline
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No invitations yet.</p>
                                @endforelse
                            </div>
                        @endauth
                    </div>

                    {{-- Call to action --}}
                    <div class="mt-10 flex justify-end">
                        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-2 text-base font-semibold text-white shadow-lg hover:from-indigo-600 hover:to-purple-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 transition">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to all events</span>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </div>
@endsection
