{{-- Event card component for use in my-events page --}}
{{-- Props: $event (Event model), $showActions (boolean, default false), $showTimeStatus (boolean, default true), $showViewOnly (boolean, default false) --}}
@props(['event', 'showActions' => false, 'showTimeStatus' => true, 'showViewOnly' => false])

@php
    // Format the start date for display
    $formattedDate = $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('M j, Y g:i A') : 'N/A';

    // Determine status styling using effective status
    $effectiveStatus = $event->effective_status;
    $statusColor = match($effectiveStatus) {
        'published' => 'bg-green-100 text-green-700',
        'draft' => 'bg-yellow-100 text-yellow-700',
        'canceled' => 'bg-red-100 text-red-700',
        'completed' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700'
    };

    // Determine if event is upcoming/ongoing/past
    $now = now();
    $timeStatus = $event->start_at > $now ? 'Upcoming' :
                   ($event->end_at > $now ? 'Ongoing' : 'Past');
    $timeStatusColor = $timeStatus === 'Upcoming' ? 'bg-blue-100 text-blue-700' :
                       ($timeStatus === 'Ongoing' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700');
@endphp

<div class="group relative flex h-full flex-col rounded-3xl bg-white/95 p-4 shadow-[0_10px_30px_rgba(88,80,236,0.18)] transition hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(88,80,236,0.28)]">
    {{-- Image placeholder area --}}
    <div class="mb-3 flex-1 rounded-2xl border border-dashed border-slate-200 bg-slate-50"></div>

    {{-- Event title + status badge on same line --}}
    <div class="mt-1 flex items-center justify-between gap-2">
        <span class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 truncate">
            {{ $event->title }}
        </span>
        <span class="shrink-0 inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
            <i class="fas fa-clock"></i>
            {{ ucfirst($effectiveStatus) }}
        </span>
    </div>

    {{-- Event meta information --}}
    <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
        <span class="truncate">{{ $formattedDate }}</span>
        <span class="truncate text-right ml-2">{{ $event->venue }}</span>
    </div>

    {{-- Capacity information --}}
    <div class="mt-2 flex items-center text-xs text-slate-500">
        <i class="fas fa-users mr-1"></i>
        <span>{{ $event->current_participants_count ?? 0 }}/{{ $event->capacity }}</span>
    </div>

    {{-- Action buttons (only shown when requested) --}}
    @if($showActions || $showViewOnly)
        <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
            <a href="{{ route('events.show', $event->id_event) }}"
               class="inline-flex items-center px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-eye mr-1"></i>
                View
            </a>

            {{-- Edit and Delete buttons for organizers --}}
            @if($showActions)
                @auth
                @if(Auth::id() === $event->id_organizer && $event->is_editable && $event->status !== 'canceled')
                    <div class="flex gap-2">
                        <a href="{{ route('events.edit', $event->id_event) }}"
                           class="inline-flex items-center px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800 cursor-pointer">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>

                        @if($event->can_hard_delete)
                            <form id="delete-form-{{ $event->id_event }}"
                                  action="{{ route('events.destroy', $event->id_event) }}"
                                  method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button"
                                    class="inline-flex items-center px-3 py-1 text-sm text-rose-700 hover:text-rose-900 cursor-pointer"
                                    onclick="openDeleteModal('delete-form-{{ $event->id_event }}')">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        @endif
                    </div>
                @endif
                @endauth
            @endif
        </div>
    @endif
</div>