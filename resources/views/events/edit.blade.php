{{-- This view lets the organizer edit an existing event --}}
@extends('layouts.app')

{{-- Set the page title in the browser tab --}}
@section('title', 'Edit Event - EventSquare')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    {{-- Center the form and limit width --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            {{-- Page title --}}
            <h1 class="text-3xl font-bold text-gray-900">Edit Event</h1>
            {{-- Small subtitle showing current event title --}}
            <p class="mt-2 text-gray-600">Update the details for "{{ $event->title }}"</p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            {{-- IMPORTANT: use events.update route + PUT method --}}
            <form action="{{ route('events.update', $event->id_event) }}" method="POST">
                @csrf
                {{-- Because HTML forms don't support PUT directly, we spoof it with @method --}}
                @method('PUT')

                <!-- Form Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-edit mr-2"></i>
                        Event Details
                    </h2>
                </div>

                <!-- Form Body (same structure as create.blade.php, but with default values) -->
                <div class="p-6 space-y-8">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                            Basic Information
                        </h3>
                        {{-- Grid layout for basic info fields --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Event title (NOT editable on this page) --}}
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Event Title
                                </label>
                                {{-- Show the title as plain, non-editable text --}}
                                <p class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                    {{ $event->title }}
                                </p>
                            </div>

                            {{-- Venue field --}}
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">
                                    Venue *
                                </label>
                                <div class="relative">
                                    {{-- Location icon inside the input --}}
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="venue"
                                        name="venue"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('venue') border-red-500 @enderror"
                                        value="{{ old('venue', $event->venue) }}"
                                        placeholder="Where will the event take place?"
                                        required
                                    >
                                </div>
                                @error('venue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Capacity field --}}
                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                                    Capacity *
                                </label>
                                <div class="relative">
                                    {{-- People icon --}}
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    <input
                                        type="number"
                                        id="capacity"
                                        name="capacity"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('capacity') border-red-500 @enderror"
                                        value="{{ old('capacity', $event->capacity) }}"
                                        placeholder="Maximum number of attendees"
                                        min="1"
                                        required
                                    >
                                </div>
                                @error('capacity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Date and Time -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clock mr-2 text-indigo-500"></i>
                            Date and Time
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Start date/time --}}
                            <div>
                                <label for="start_at" class="block text-sm font-medium text-gray-700 mb-1">
                                    Start Date &amp; Time *
                                </label>
                                <input
                                    type="datetime-local"
                                    id="start_at"
                                    name="start_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('start_at') border-red-500 @enderror"
                                    value="{{ old('start_at', $event->start_at ? $event->start_at->format('Y-m-d\TH:i') : '') }}"
                                    required
                                >
                                @error('start_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- End date/time --}}
                            <div>
                                <label for="end_at" class="block text-sm font-medium text-gray-700 mb-1">
                                    End Date &amp; Time *
                                </label>
                                <input
                                    type="datetime-local"
                                    id="end_at"
                                    name="end_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('end_at') border-red-500 @enderror"
                                    value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}"
                                    required
                                >
                                @error('end_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Event Settings -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog mr-2 text-indigo-500"></i>
                            Event Settings
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Visibility dropdown --}}
                            <div>
                                <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">
                                    Visibility *
                                </label>
                                {{-- Eye icon --}}
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-eye text-gray-400"></i>
                                    </div>
                                    <select
                                        id="visibility"
                                        name="visibility"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('visibility') border-red-500 @enderror"
                                        required
                                    >
                                        {{-- Public option --}}
                                        <option value="public" {{ old('visibility', $event->visibility) == 'public' ? 'selected' : '' }}>
                                            Public - Anyone can see and join
                                        </option>
                                        {{-- Private option --}}
                                        <option value="private" {{ old('visibility', $event->visibility) == 'private' ? 'selected' : '' }}>
                                            Private - Only invited users can join
                                        </option>
                                    </select>
                                </div>
                                @error('visibility')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tags checkboxes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tags (Optional)
                                </label>
                                {{-- Scrollable box of tags --}}
                                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 rounded-lg bg-white">
                                    @foreach($tags as $tag)
                                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="tags[]"
                                                value="{{ $tag->id_tag }}"
                                                {{-- check if this tag is in the selectedTags array, or in old('tags') after validation error --}}
                                                {{ in_array($tag->id_tag, old('tags', $selectedTags)) ? 'checked' : '' }}
                                                class="mr-2 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('tags')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-align-left mr-2 text-indigo-500"></i>
                            Description
                        </h3>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Event Description
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                                rows="6"
                                placeholder="Describe your event, what attendees can expect, and any important information..."
                            >{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                    <div class="flex space-x-2">
                        {{-- Cancel Event button - only for published events --}}
                        @if($event->status === 'published')
                            <button type="button"
                                    onclick="openCancelModal('cancel-form-{{ $event->id_event }}')"
                                    class="px-4 py-2 border border-transparent rounded-lg text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors flex items-center">
                                <i class="fas fa-ban mr-2"></i>
                                Cancel Event
                            </button>
                        @endif

                        {{-- Delete Event button - only if no activity --}}
                        @if($event->can_hard_delete)
                            <button type="button"
                                    onclick="openDeleteModal('delete-form-{{ $event->id_event }}')"
                                    class="px-4 py-2 border border-transparent rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors flex items-center">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Event
                            </button>
                        @endif
                    </div>

                    <div class="flex space-x-4">
                        {{-- Cancel: go back to event details page without saving --}}
                        <a
                            href="{{ route('events.show', $event->id_event) }}"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        >
                            Back to Event
                        </a>

                        {{-- Submit: save changes --}}
                        <button
                            type="submit"
                            class="px-6 py-2 border border-transparent rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors flex items-center"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Update Event
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Event Form (hidden) --}}
@if($event->status === 'published')
    <form id="cancel-form-{{ $event->id_event }}"
          action="{{ route('events.cancel', $event->id_event) }}"
          method="POST" class="hidden">
        @csrf
    </form>
@endif

{{-- Delete Event Form (hidden) --}}
@if($event->can_hard_delete)
    <form id="delete-form-{{ $event->id_event }}"
          action="{{ route('events.destroy', $event->id_event) }}"
          method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif

{{-- Cancel Confirmation Modal --}}
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

{{-- Delete Confirmation Modal --}}
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
                    class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-red-600 hover:bg-red-700"
                    onclick="confirmDelete()">
                Delete Event
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
</script>
@endsection
