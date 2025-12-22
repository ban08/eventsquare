@extends('layouts.app')

@section('title', $event->title)

@section('content')
    {{-- Hero Section with Gradient --}}
    <div class="relative bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-900 py-16 sm:py-24">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-[url('/img/grid.svg')] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-2">
                        {{ $event->title }}
                    </h1>
                    <div class="flex items-center text-indigo-200 text-sm sm:text-base mb-4">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        {{ $event->venue }}
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur-sm border border-white/20">
                            <i class="fas fa-eye text-indigo-300"></i>
                            {{ ucfirst($event->visibility) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium backdrop-blur-sm border
                            @if($event->effective_status === 'published') bg-green-500/20 text-green-200 border-green-500/30
                            @elseif($event->effective_status === 'draft') bg-yellow-500/20 text-yellow-200 border-yellow-500/30
                            @elseif($event->effective_status === 'canceled') bg-red-500/20 text-red-200 border-red-500/30
                            @elseif($event->effective_status === 'completed') bg-blue-500/20 text-blue-200 border-blue-500/30
                            @else bg-gray-500/20 text-gray-200 border-gray-500/30 @endif">
                            <i class="fas fa-circle text-[0.6rem] mr-1"></i>
                            {{ ucfirst($event->effective_status) }}
                        </span>
                    </div>
                </div>
                
                {{-- Action Buttons (Desktop) --}}
                <div class="hidden md:flex gap-3">
                    @auth
                        @if(Auth::id() === $event->id_organizer)
                            @if($event->is_editable)
                                <a href="{{ route('events.edit', $event->id_event) }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 backdrop-blur-sm transition border border-white/20">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            @endif
                            
                            @if($event->is_cancelable)
                                <button onclick="openCancelModal('cancel-form-{{ $event->id_event }}')" class="inline-flex items-center gap-2 rounded-lg bg-yellow-500/20 px-4 py-2 text-sm font-medium text-yellow-200 hover:bg-yellow-500/30 backdrop-blur-sm transition border border-yellow-500/30">
                                    <i class="fas fa-ban"></i> Cancel
                                </button>
                            @endif

                            @if($event->can_hard_delete)
                                <button onclick="openDeleteModal('delete-form-{{ $event->id_event }}')" class="inline-flex items-center gap-2 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-200 hover:bg-red-500/30 backdrop-blur-sm transition border border-red-500/30">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            @endif
                        @endif
                    @endauth
                    
                    @can('admin')
                        <button onclick="openAdminDeleteModal('admin-delete-form-{{ $event->id_event }}')" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 shadow-lg shadow-red-900/20 transition">
                            <i class="fas fa-shield-alt"></i> Admin Delete
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Description Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-indigo-500"></i> About this Event
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600">
                        <p class="whitespace-pre-line">{{ $event->description ?: 'No description provided.' }}</p>
                    </div>

                    {{-- Tags --}}
                    @if($event->tags->count() > 0)
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h3 class="text-sm font-semibold text-slate-900 mb-3">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($event->tags as $tag)
                                    <a href="{{ route('events.index', ['tags[]' => $tag->name]) }}" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Attendees Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-users text-indigo-500"></i> Attendees
                        </h2>
                        <span class="text-sm font-medium text-slate-500">
                            {{ $event->current_participants_count }} / {{ $event->capacity }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-100 rounded-full h-2.5 mb-6 overflow-hidden">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(100, ($event->current_participants_count / $event->capacity) * 100) }}%"></div>
                    </div>

                    @auth
                        @if($event->participants->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                @foreach($event->participants->take(8) as $participant)
                                    <a href="{{ route('profile.show', $participant->id_user) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition group">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shrink-0 group-hover:bg-indigo-200 transition">
                                            {{ substr($participant->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate group-hover:text-indigo-700 transition">{{ $participant->name }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ $participant->id_user === $event->id_organizer ? 'Organizer' : 'Member' }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @if($event->participants->count() > 0)
                                <div class="mt-4 text-center">
                                    <button onclick="openAttendeesModal()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium focus:outline-none">
                                        @if($event->participants->count() > 8)
                                            +{{ $event->participants->count() - 8 }} more attendees
                                        @else
                                            View all attendees
                                        @endif
                                    </button>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-slate-500 text-sm">Be the first to join!</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <div class="mb-2">
                                <i class="fas fa-lock text-slate-300 text-2xl"></i>
                            </div>
                            <p class="text-slate-500 text-sm">Login to see who is attending.</p>
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-2 inline-block">Login now</a>
                        </div>
                    @endauth
                </div>

                {{-- Polls Card (OR06 + AT06) --}}
                @if(Auth::check() && ($isParticipant || Auth::id() === $event->id_organizer))
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <i class="fas fa-poll text-indigo-500"></i> Polls
                            </h2>
                            @if(Auth::id() === $event->id_organizer && $event->effective_status !== 'canceled')
                                <button onclick="openCreatePollModal()" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition">
                                    <i class="fas fa-plus"></i> Create Poll
                                </button>
                            @endif
                        </div>

                        @if($event->polls->count() > 0)
                            <div class="space-y-6" id="polls-container">
                                @foreach($event->polls as $poll)
                                    @include('events.partials.poll-card', ['poll' => $poll, 'event' => $event])
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-slate-500 text-sm">No polls created yet.</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Organizer Only: Applications --}}
                @auth
                    @if(Auth::id() === $event->id_organizer)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mb-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-clipboard-list text-indigo-500"></i> Manage Applications
                            </h2>

                            <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                @forelse($event->applications->where('status', 'pending') as $app)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 text-xs">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                @if($app->user)
                                                    <a href="{{ route('profile.show', $app->user->id_user) }}" class="text-sm font-medium text-slate-900 hover:text-indigo-600 hover:underline transition">
                                                        {{ $app->user->name }}
                                                    </a>
                                                @else
                                                    <p class="text-sm font-medium text-slate-900">{{ 'Unknown User' }}</p>
                                                @endif
                                                <p class="text-xs text-slate-500">Applied {{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->diffForHumans() : 'recently' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('applications.accept', $app->id_application) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800 hover:bg-green-200 transition">
                                                    <i class="fas fa-check mr-1"></i> Accept
                                                </button>
                                            </form>
                                            <form action="{{ route('applications.reject', $app->id_application) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-800 hover:bg-red-200 transition">
                                                    <i class="fas fa-times mr-1"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 text-center py-4">No pending applications.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endauth

                {{-- Organizer Only: Invitations --}}
                @auth
                    @if(Auth::id() === $event->id_organizer)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                            <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-envelope text-indigo-500"></i> Manage Invitations
                            </h2>
                            
                            @php $effectiveStatus = $event->effective_status; @endphp
                            @if(in_array($effectiveStatus, ['canceled', 'completed', 'deleted']))
                                <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 flex items-center gap-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Invitations are disabled for {{ $effectiveStatus }} events.</span>
                                </div>
                            @else
                                <form action="{{ route('invitations.invite', $event->id_event) }}" method="POST" class="flex gap-2 mb-6">
                                    @csrf
                                    <input type="email" name="invitee_email" required class="flex-1 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Enter email address to invite..." />
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition">
                                        <i class="fas fa-paper-plane"></i> Invite
                                    </button>
                                </form>
                            @endif

                            <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                                @forelse($event->invitations as $inv)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 text-xs">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                @if($inv->invitee)
                                                    <a href="{{ route('profile.show', $inv->invitee->id_user) }}" class="text-sm font-medium text-slate-900 hover:text-indigo-600 hover:underline transition">
                                                        {{ $inv->invitee->name }}
                                                    </a>
                                                @else
                                                    <p class="text-sm font-medium text-slate-900">{{ $inv->invitee_email ?? 'Unknown User' }}</p>
                                                @endif
                                                <p class="text-xs text-slate-500">Invited {{ $inv->sent_at ? $inv->sent_at->diffForHumans() : 'recently' }}</p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            @if($inv->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($inv->status === 'accepted') bg-green-100 text-green-800
                                            @elseif($inv->status === 'declined') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 text-center py-4">No invitations sent yet.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Event Details Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Event Details</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Date & Time</p>
                                <p class="text-sm text-slate-600">
                                    {{ $event->start_at->format('M d, Y') }}
                                    @if(!$event->start_at->isSameDay($event->end_at))
                                        - {{ $event->end_at->format('M d, Y') }}
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500">{{ $event->start_at->format('h:i A') }} - {{ $event->end_at->format('h:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Location</p>
                                <p class="text-sm text-slate-600">{{ $event->venue }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Organizer</p>
                                <p class="text-sm text-slate-600">{{ $event->organizer->name ?? 'Unknown' }}</p>
                                <a href="{{ route('profile.show', $event->organizer) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View Profile</a>
                            </div>
                        </div>
                    </div>

                    @auth
                        @if(Auth::id() !== $event->id_organizer)
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <button onclick="openReportModal()" class="flex items-center gap-2 text-sm text-slate-500 hover:text-red-600 transition">
                                    <i class="fas fa-flag"></i> Report Event
                                </button>
                            </div>
                        @endif
                    @endauth

                    {{-- Apply to an Event --}}
                    @auth
                        @php
                            $alreadyParticipant = $event->participants->contains(Auth::id());
                            $userApplication = $event->applications->firstWhere('id_user', Auth::id());
                            $alreadyApplied = $userApplication !== null;
                            $isOrganizer = $event->id_organizer == Auth::id();
                            $isAdmin = Gate::allows('admin');
                        @endphp

                        @if(!$isAdmin && !$isOrganizer)
                            <div class="mt-8 pt-6 border-t border-slate-100">
                                @if($alreadyParticipant)
                                    <div class="w-full rounded-xl bg-green-50 border border-green-200 p-4 text-center">
                                        <div class="mx-auto h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 mb-2">
                                            <i class="fas fa-check text-xl"></i>
                                        </div>
                                        <h4 class="text-green-900 font-semibold">You're going!</h4>
                                        <p class="text-green-700 text-xs mt-1">See you there.</p>
                                    </div>
                                @elseif($userApplication && $userApplication->status === 'pending')
                                    <div class="w-full rounded-xl bg-yellow-50 border border-yellow-200 p-4 text-center">
                                        <div class="mx-auto h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 mb-2">
                                            <i class="fas fa-clock text-xl"></i>
                                        </div>
                                        <h4 class="text-yellow-900 font-semibold">Application Pending</h4>
                                        <p class="text-yellow-700 text-xs mt-1">Waiting for organizer approval.</p>
                                    </div>
                                @elseif($userApplication && $userApplication->status === 'rejected')
                                    <div class="w-full rounded-xl bg-red-50 border border-red-200 p-4 text-center">
                                        <div class="mx-auto h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 mb-2">
                                            <i class="fas fa-times text-xl"></i>
                                        </div>
                                        <h4 class="text-red-900 font-semibold">Application Rejected</h4>
                                        <p class="text-red-700 text-xs mt-1">The organizer declined your request.</p>
                                        @if($event->is_full)
                                            <p class="text-red-700 text-xs mt-3">Event is full right now.</p>
                                        @elseif($event->is_past)
                                            <p class="text-red-700 text-xs mt-3">Event already ended.</p>
                                        @elseif($event->effective_status !== 'published')
                                            <p class="text-red-700 text-xs mt-3">Event {{ ucfirst($event->effective_status) }}.</p>
                                        @else
                                            <form action="{{ route('events.apply', $event) }}" method="POST" class="mt-3">
                                                @csrf
                                                <button class="w-full rounded-xl bg-red-600 p-3 text-white font-semibold shadow hover:bg-red-700 transition">
                                                    Apply Again
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @elseif($userApplication && $userApplication->status === 'approved')
                                    <div class="w-full rounded-xl bg-green-50 border border-green-200 p-4 text-center">
                                        <div class="mx-auto h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 mb-2">
                                            <i class="fas fa-check text-xl"></i>
                                        </div>
                                        <h4 class="text-green-900 font-semibold">Application Accepted</h4>
                                        <p class="text-green-700 text-xs mt-1">You have been accepted to this event.</p>
                                    </div>
                                @elseif($event->is_full)
                                    <button disabled class="w-full rounded-xl bg-slate-100 border border-slate-200 p-3 text-slate-400 font-medium cursor-not-allowed">
                                        Event Full
                                    </button>
                                @elseif($event->is_past)
                                    <button disabled class="w-full rounded-xl bg-slate-100 border border-slate-200 p-3 text-slate-400 font-medium cursor-not-allowed">
                                        Event already ended
                                    </button>
                                @elseif($event->effective_status !== 'published')
                                    <button disabled class="w-full rounded-xl bg-slate-100 border border-slate-200 p-3 text-slate-400 font-medium cursor-not-allowed">
                                        Event {{ ucfirst($event->effective_status) }}
                                    </button>
                                @else
                                    <form action="{{ route('events.apply', $event) }}" method="POST">
                                        @csrf
                                        <button class="w-full rounded-xl bg-indigo-600 p-4 text-white font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition transform hover:-translate-y-0.5">
                                            Apply to Join
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="mt-8 pt-6 border-t border-slate-100">
                            @if($event->is_past)
                                <button disabled class="w-full rounded-xl bg-slate-100 border border-slate-200 p-3 text-slate-400 font-medium cursor-not-allowed">
                                    Event already ended
                                </button>
                            @elseif($event->is_full)
                                <button disabled class="w-full rounded-xl bg-slate-100 border border-slate-200 p-3 text-slate-400 font-medium cursor-not-allowed">
                                    Event Full
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="block w-full text-center rounded-xl bg-indigo-600 p-4 text-white font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition">
                                    Login to Join
                                </a>
                            @endif
                        </div>
                    @endauth

                </div>


                {{-- Mobile Actions (Visible only on small screens) --}}
                @auth
                    @if(Auth::id() === $event->id_organizer)
                        <div class="md:hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3">
                            <h3 class="text-sm font-semibold text-slate-900 mb-2">Organizer Actions</h3>
                            
                            @if($event->is_editable)
                                <a href="{{ route('events.edit', $event->id_event) }}" class="block w-full text-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Edit Event
                                </a>
                            @endif
                            
                            @if($event->is_cancelable)
                                <button onclick="openCancelModal('cancel-form-{{ $event->id_event }}')" class="block w-full text-center rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-700 hover:bg-yellow-100">
                                    Cancel Event
                                </button>
                            @endif


                            @if($event->can_hard_delete)
                                <button onclick="openDeleteModal('delete-form-{{ $event->id_event }}')" class="block w-full text-center rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                                    Delete Event
                                </button>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- Hidden Forms --}}
    @auth
        @if(Auth::id() === $event->id_organizer)
            @if($event->is_cancelable)
                <form id="cancel-form-{{ $event->id_event }}" action="{{ route('events.cancel', $event->id_event) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @endif
            @if($event->can_hard_delete)
                <form id="delete-form-{{ $event->id_event }}" action="{{ route('events.destroy', $event->id_event) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endif
    @endauth

    @can('admin')
        <form id="admin-delete-form-{{ $event->id_event }}" action="{{ route('events.destroy', $event->id_event) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    {{-- Modals --}}
    <x-modal-confirm
        id="cancel-modal"
        title="Cancel Event"
        message="Are you sure you want to cancel this event? The event will remain visible but no one will be able to apply to join."
        confirm-text="Cancel Event"
        confirm-color="yellow"
        on-confirm="confirmCancel()"
    />

    <x-modal-confirm
        id="delete-modal"
        title="Delete Event Forever"
        message="This event has no registrations, so it can be deleted without leaving traces. This action cannot be undone."
        confirm-text="Delete Event"
        confirm-color="red"
        on-confirm="confirmDelete()"
    />

    <x-modal-confirm
        id="admin-delete-modal"
        title="Admin: Delete Event"
        message="Warning: You are about to permanently delete this event as an administrator. This action will be logged and cannot be undone."
        confirm-text="Delete Event"
        confirm-color="red"
        on-confirm="confirmAdminDelete()"
    />

    {{-- Attendees Modal --}}
    <div id="attendees-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeAttendeesModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-slate-900 mb-4 flex items-center gap-2" id="modal-title">
                                <i class="fas fa-users text-indigo-500"></i> All Attendees ({{ $event->participants->count() }})
                            </h3>
                            <div class="mt-2 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                                <div class="space-y-3">
                                    @foreach($event->participants as $participant)
                                        <a href="{{ route('profile.show', $participant->id_user) }}" class="block w-full flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition border border-transparent hover:border-slate-100 cursor-pointer group">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shrink-0 group-hover:bg-indigo-200 transition">
                                                {{ substr($participant->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex-1 text-left">
                                                <span class="text-sm font-medium text-slate-900 group-hover:text-indigo-600 truncate block">
                                                    {{ $participant->name }}
                                                </span>
                                                <p class="text-xs text-slate-500 truncate">{{ $participant->id_user === $event->id_organizer ? 'Organizer' : 'Member' }}</p>
                                            </div>
                                            @if(Auth::id() === $participant->id_user)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    You
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeAttendeesModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Poll Modal --}}
    <div id="create-poll-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCreatePollModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10">
                <form action="{{ route('polls.store', $event->id_event) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-slate-900 mb-4">Create New Poll</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="poll-question" class="block text-sm font-medium text-slate-700">Question</label>
                                <input type="text" name="question" id="poll-question" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., What should we eat?">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Options</label>
                                <div id="poll-options-container" class="space-y-2">
                                    <input type="text" name="options[]" required class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Option 1">
                                    <input type="text" name="options[]" required class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Option 2">
                                </div>
                                <button type="button" onclick="addPollOption()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                    + Add another option
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create Poll
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeCreatePollModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Report Modal --}}
    <div id="report-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReportModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10">
                <form action="{{ route('events.report', $event->id_event) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-flag text-red-500"></i> Report Event
                        </h3>
                        
                        <div class="space-y-4">
                            <p class="text-sm text-slate-600">Please describe why you are reporting this event. This will be reviewed by an administrator.</p>
                            <div>
                                <label for="report-reason" class="block text-sm font-medium text-slate-700">Reason</label>
                                <textarea name="reason" id="report-reason" rows="4" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Inappropriate content, spam, etc."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit Report
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeReportModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Logic
        let activeForm = null;

        function openCancelModal(formId) {
            activeForm = document.getElementById(formId);
            document.getElementById('cancel-modal').classList.remove('hidden');
        }

        function openDeleteModal(formId) {
            activeForm = document.getElementById(formId);
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function openAdminDeleteModal(formId) {
            activeForm = document.getElementById(formId);
            document.getElementById('admin-delete-modal').classList.remove('hidden');
        }

        function openAttendeesModal() {
            document.getElementById('attendees-modal').classList.remove('hidden');
        }

        function closeAttendeesModal() {
            document.getElementById('attendees-modal').classList.add('hidden');
        }

        function openCreatePollModal() {
            document.getElementById('create-poll-modal').classList.remove('hidden');
        }

        function closeCreatePollModal() {
            document.getElementById('create-poll-modal').classList.add('hidden');
        }

        function openReportModal() {
            document.getElementById('report-modal').classList.remove('hidden');
        }

        function closeReportModal() {
            document.getElementById('report-modal').classList.add('hidden');
        }

        function addPollOption() {
            const container = document.getElementById('poll-options-container');
            const count = container.children.length + 1;
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.required = true;
            input.className = 'block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
            input.placeholder = 'Option ' + count;
            container.appendChild(input);
        }

        function confirmCancel() { if(activeForm) activeForm.submit(); }
        function confirmDelete() { if(activeForm) activeForm.submit(); }
        function confirmAdminDelete() { if(activeForm) activeForm.submit(); }
    </script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pollsContainer = document.getElementById('polls-container');
        if (!pollsContainer) return;

        pollsContainer.addEventListener('submit', async function(e) {
            const form = e.target;
            const isVote = form.matches('.poll-vote-form');
            const isRemove = form.matches('.poll-remove-vote-form');
            if (!isVote && !isRemove) return;

            e.preventDefault();

            const formData = new FormData(form);
            
            if (isRemove) {
                formData.append('_method', 'DELETE');
            }

            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (!res.ok || !data || !data.html) {
                    throw new Error('Bad response');
                }

                const card = form.closest('[id^="poll-card-"]');
                if (card) card.outerHTML = data.html;
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                if (btn) btn.disabled = false;
            }
        });
    });
                
</script>
@endpush