@extends('layouts.app')

@section('title', 'My Invitations')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-indigo-50 via-slate-50 to-purple-50 py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Back home
            </a>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                <span class="inline-flex items-center gap-1 rounded-full bg-white border border-indigo-100 px-3 py-1 shadow-sm">
                    <i class="fas fa-envelope-open-text text-indigo-600"></i>
                    <span>Pending: {{ $pending->count() }}</span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-white border border-purple-100 px-3 py-1 shadow-sm">
                    <i class="fas fa-history text-purple-600"></i>
                    <span>History: {{ $responded->count() }}</span>
                </span>
            </div>
        </div>

        <h1 class="text-3xl font-bold tracking-tight text-indigo-700 mb-8 flex items-center gap-3">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md"><i class="fas fa-envelope"></i></span>
            <span>My Invitations</span>
        </h1>

        {{-- Pending invitations --}}
        <div class="mb-12">
            <h2 class="text-sm font-semibold tracking-wide text-indigo-600 uppercase mb-4">Pending</h2>
            @if($pending->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-center">
                    <p class="text-sm text-slate-500">You have no pending invitations.</p>
                </div>
            @else
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($pending as $inv)
                        <div class="group rounded-2xl bg-white shadow-[0_6px_18px_rgba(79,70,229,0.12)] border border-indigo-100 hover:shadow-[0_10px_28px_rgba(79,70,229,0.18)] transition overflow-hidden flex flex-col">
                            <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                            <div class="px-6 pt-6 pb-5 flex-1 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <h3 class="text-base font-semibold text-slate-800 line-clamp-1">
                                        <a href="{{ route('events.show', $inv->event->id_event) }}" class="hover:text-indigo-600">{{ $inv->event->title }}</a>
                                    </h3>
                                    <div class="flex flex-wrap gap-2 text-[11px] font-medium">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-indigo-600">
                                            <i class="fas fa-clock"></i>
                                            <span>Sent {{ $inv->sent_at?->diffForHumans() }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2 py-1 text-yellow-700">
                                            <i class="fas fa-hourglass-half"></i>
                                            <span>Pending</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-5 flex gap-3 justify-end">
                                    <form method="POST" action="{{ route('invitations.accept', $inv->id_invitation) }}">
                                        @csrf
                                        <button class="inline-flex items-center gap-1 rounded-full bg-green-600 px-4 py-1.5 text-white text-xs font-semibold shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition">
                                            <i class="fas fa-check"></i><span>Accept</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('invitations.decline', $inv->id_invitation) }}">
                                        @csrf
                                        <button class="inline-flex items-center gap-1 rounded-full bg-red-600 px-4 py-1.5 text-white text-xs font-semibold shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition">
                                            <i class="fas fa-times"></i><span>Decline</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Responded invitations --}}
        <div>
            <h2 class="text-sm font-semibold tracking-wide text-purple-600 uppercase mb-4">History</h2>
            @if($responded->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-6 text-center">
                    <p class="text-sm text-slate-500">No responded invitations yet.</p>
                </div>
            @else
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach($responded as $inv)
                        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 hover:shadow-md transition overflow-hidden flex flex-col">
                            <div class="h-1 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
                            <div class="px-6 pt-6 pb-5 flex-1 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <h3 class="text-base font-semibold text-slate-800 line-clamp-1">
                                        <a href="{{ route('events.show', $inv->event->id_event) }}" class="hover:text-indigo-600">{{ $inv->event->title }}</a>
                                    </h3>
                                    <div class="flex flex-wrap gap-2 text-[11px] font-medium">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-600">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ $inv->responded_at?->diffForHumans() }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-white @class([
                                            'bg-green-600' => $inv->status === 'accepted',
                                            'bg-red-600' => $inv->status === 'declined',
                                            'bg-slate-600' => !in_array($inv->status,['accepted','declined'])
                                        ])">
                                            <i class="fas fa-info-circle"></i>
                                            <span>{{ ucfirst($inv->status) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
