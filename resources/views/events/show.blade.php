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
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <h2 class="text-3xl font-bold text-indigo-700 tracking-tight mb-3">{{ $event->title }}</h2>

                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-indigo-700 shadow">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>{{ ucfirst($event->visibility) }} event</span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full
                                    @class([
                                        'bg-green-100 text-green-700' => $event->status === 'published',
                                        'bg-yellow-100 text-yellow-700' => $event->status === 'draft',
                                        'bg-red-100 text-red-700' => $event->status === 'canceled',
                                        'bg-blue-100 text-blue-700' => $event->status === 'completed',
                                        'bg-gray-100 text-gray-700' => true,
                                    ]) px-3 py-1 shadow">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ ucfirst($event->effective_status) }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Organizer actions (Edit, Cancel, Delete buttons) --}}
                        @auth
                            @if(Auth::id() === $event->id_organizer)
                                <div class="flex gap-2">
                                    {{-- Edit button only for non-canceled events that are editable --}}
                                    @if($event->status !== 'canceled' && $event->is_editable)
                                        <a href="{{ route('events.edit', $event->id_event) }}"
                                           class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 cursor-pointer">
                                            <i class="fas fa-edit"></i>
                                            <span>Edit Event</span>
                                        </a>
                                    @endif

                                    @if($event->status === 'published')
                                        <button type="button"
                                                onclick="openCancelModal('cancel-form-{{ $event->id_event }}')"
                                                class="inline-flex items-center gap-2 rounded-full bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-yellow-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2 cursor-pointer">
                                            <i class="fas fa-ban"></i>
                                            <span>Cancel Event</span>
                                        </button>
                                    @endif

                                    @if($event->can_hard_delete)
                                        <button type="button"
                                                onclick="openDeleteModal('delete-form-{{ $event->id_event }}')"
                                                class="inline-flex items-center gap-2 rounded-full bg-rose-700 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 cursor-pointer">
                                            <i class="fas fa-trash"></i>
                                            <span>Delete Event</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @endauth

                        {{-- AD03: Admin delete button (separate from organizer actions) --}}
                        @can('admin')
                            <div class="flex gap-2">
                                <button type="button"
                                        onclick="openAdminDeleteModal('admin-delete-form-{{ $event->id_event }}')"
                                        class="inline-flex items-center gap-2 rounded-full bg-rose-700 px-4 py-2 text-sm font-medium text-white shadow hover:bg-rose-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 cursor-pointer">
                                    <i class="fas fa-trash"></i>
                                    <span>Admin Delete</span>
                                </button>
                            </div>
                        @endcan
                    </div>

                    {{-- Meta info --}}
                    <dl class="mt-8 grid gap-6 text-base text-slate-700 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Organizer --}}
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-user text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Organizer</p>
                                <p class="mt-1">{{ $event->organizer->name ?? 'Unknown' }}</p>
                            </dd>
                        </div>
                        {{-- Start date/time --}}
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-calendar-day text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Starts</p>
                                <p class="mt-1">{{ $event->start_at }}</p>
                            </dd>
                        </div>
                        {{-- Venue --}}
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-location-dot text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Venue</p>
                                <p class="mt-1">{{ $event->venue }}</p>
                            </dd>
                        </div>
                        {{-- End date/time --}}
                        <div class="flex items-start gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 shadow">
                            <dt class="mt-1"><i class="fas fa-calendar-check text-indigo-500"></i></dt>
                            <dd>
                                <p class="font-medium text-slate-900">Ends</p>
                                <p class="mt-1">{{ $event->end_at ?? 'N/A' }}</p>
                            </dd>
                        </div>
                    </dl>

                    {{-- Capacity indicator --}}
                    <div class="mt-6 flex items-center gap-2 text-base text-slate-700">
                        <i class="fas fa-users text-indigo-500"></i>
                        <span class="font-medium">Capacity:</span>
                        <span>{{ $event->current_participants_count ?? 0 }}/{{ $event->capacity }}</span>
                        @if($event->is_full)
                            <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Full</span>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="mt-10 border-t border-slate-200 pt-8">
                        <h3 class="text-base font-semibold text-indigo-700 mb-2">Description</h3>
                        <p class="mt-2 text-base leading-relaxed text-slate-700 max-w-3xl">{{ $event->description }}</p>
                    </div>

                    {{-- Invitations prototype (OR03 / RU10) - Only visible to the event organizer --}}
                    @auth
                        @if(Auth::id() === $event->id_organizer)
                    <div class="mt-10 border-t border-slate-200 pt-8">
                        <h3 class="text-base font-semibold text-indigo-700 mb-4">Invitations</h3>

                            {{-- OR03: Organizer invite form - only for active events --}}
                            @php $effectiveStatus = $event->effective_status; @endphp
                            @if(in_array($effectiveStatus, ['canceled', 'completed', 'deleted']))
                                <div class="mb-6 rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-sm text-gray-600">
                                    <i class="fas fa-ban mr-2"></i>
                                    Cannot send invitations to {{ $effectiveStatus }} events.
                                </div>
                            @else
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
                    </div>
                        @endif
                    @endauth

                    {{-- Apply to event (RU09) - BR13: Admins cannot participate --}}
                    @auth
                        @php
                            $alreadyParticipant = $event->participants->contains(Auth::id());
                            $alreadyApplied = $event->applications->contains(fn($a) => $a->id_user == Auth::id());
                            $isOrganizer = $event->id_organizer == Auth::id();
                            $isAdmin = Gate::allows('admin');
                        @endphp

                        @if(
                            !$isAdmin &&
                            !$isOrganizer &&
                            !$alreadyParticipant &&
                            !$alreadyApplied &&
                            !$event->is_full &&
                            $event->status === 'published'
                        )
                            <form action="{{ route('events.apply', $event) }}" method="POST" class="mt-8 flex justify-end">
                                @csrf
                                <button class="inline-flex items-center gap-2 rounded-full bg-green-600 px-6 py-2 text-base font-semibold text-white shadow-lg hover:bg-green-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 transition">
                                    <i class="fas fa-user-plus"></i>
                                    Apply to Join Event
                                </button>
                            </form>
                        @endif
                    @endauth


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

    {{-- Cancel form for organizers (hidden by default) --}}
    @auth
        @if(Auth::id() === $event->id_organizer)
            @if($event->status === 'published')
                <form id="cancel-form-{{ $event->id_event }}"
                      action="{{ route('events.cancel', $event->id_event) }}"
                      method="POST" class="hidden">
                    @csrf
                </form>
            @endif
        @endif
    @endauth

    {{-- Delete form for organizers (hidden by default) --}}
    @auth
        @if(Auth::id() === $event->id_organizer)
            @if($event->can_hard_delete)
                <form id="delete-form-{{ $event->id_event }}"
                      action="{{ route('events.destroy', $event->id_event) }}"
                      method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @endif
    @endauth

    {{-- AD03: Delete form for admins (hidden by default) --}}
    @can('admin')
        <form id="admin-delete-form-{{ $event->id_event }}"
              action="{{ route('events.destroy', $event->id_event) }}"
              method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    {{-- Cancel confirmation modal (hidden by default) --}}
    <div id="cancel-modal"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">
                    Cancel Event
                </h2>
            </div>
            <div class="px-4 py-3">
                <p class="text-sm text-gray-700">
                    Are you sure you want to cancel this event? The event will remain visible but no one will be able to apply to join.
                </p>
            </div>
            <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                        onclick="closeCancelModal()">
                    Keep Event
                </button>
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-yellow-600 hover:bg-yellow-700"
                        onclick="confirmCancel()">
                    Cancel Event
                </button>
            </div>
        </div>
    </div>

    {{-- Delete confirmation modal (hidden by default) --}}
    <div id="delete-modal"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">
                    Delete Event Forever
                </h2>
            </div>
            <div class="px-4 py-3">
                <p class="text-sm text-gray-700">
                    This event has no registrations, so it can be deleted without leaving traces. This action cannot be undone. Continue?
                </p>
            </div>
            <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                        onclick="closeDeleteModal()">
                    Keep Event
                </button>
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-rose-700 hover:bg-rose-800"
                        onclick="confirmDelete()">
                    Delete Event
                </button>
            </div>
        </div>
    </div>

    {{-- AD03: Admin delete confirmation modal --}}
    <div id="admin-delete-modal"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="px-4 py-3 border-b border-rose-200 bg-rose-50">
                <h2 class="text-base font-semibold text-rose-900">
                    <i class="fas fa-shield-alt mr-2"></i>Admin: Delete Event
                </h2>
            </div>
            <div class="px-4 py-3">
                <p class="text-sm text-gray-700">
                    <strong>Warning:</strong> You are about to permanently delete this event as an administrator. This action will be logged and cannot be undone.
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Event: <strong>{{ $event->title }}</strong>
                </p>
            </div>
            <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                        onclick="closeAdminDeleteModal()">
                    Cancel
                </button>
                <button type="button"
                        class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-rose-700 hover:bg-rose-800"
                        onclick="confirmAdminDelete()">
                    <i class="fas fa-trash mr-1"></i>Delete Event
                </button>
            </div>
        </div>
    </div>

    {{-- JavaScript for modals --}}
    <script>
        let cancelFormToSubmit = null;
        let deleteFormToSubmit = null;

        function openCancelModal(formId) {
            cancelFormToSubmit = document.getElementById(formId);
            const modal = document.getElementById('cancel-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeCancelModal() {
            const modal = document.getElementById('cancel-modal');
            if (modal) modal.classList.add('hidden');
            cancelFormToSubmit = null;
        }

        function confirmCancel() {
            if (cancelFormToSubmit) {
                cancelFormToSubmit.submit();
            }
        }

        function openDeleteModal(formId) {
            deleteFormToSubmit = document.getElementById(formId);
            const modal = document.getElementById('delete-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-modal');
            if (modal) modal.classList.add('hidden');
            deleteFormToSubmit = null;
        }

        function confirmDelete() {
            if (deleteFormToSubmit) {
                deleteFormToSubmit.submit();
            }
        }

        // AD03: Admin delete modal functions
        let adminDeleteFormToSubmit = null;

        function openAdminDeleteModal(formId) {
            adminDeleteFormToSubmit = document.getElementById(formId);
            const modal = document.getElementById('admin-delete-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeAdminDeleteModal() {
            const modal = document.getElementById('admin-delete-modal');
            if (modal) modal.classList.add('hidden');
            adminDeleteFormToSubmit = null;
        }

        function confirmAdminDelete() {
            if (adminDeleteFormToSubmit) {
                adminDeleteFormToSubmit.submit();
            }
        }
    </script>
@endsection