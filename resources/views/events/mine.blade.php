{{-- This view shows the list of events related to the logged-in user --}}
@extends('layouts.app')

@section('title', 'My Events')

{{-- Main content area --}}
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Events I Organize</h1>
    </div>        

    {{-- If the user has no events, show a simple message --}}
    @if($events->isEmpty())
        <p class="text-gray-600">
            You haven't organized any events yet.
        </p>
    @else
        {{-- Otherwise, show a table with all their events --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Table header row --}}
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                {{-- Table body with one row per event --}}
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($events as $event)
                        <tr>
                            <td class="px-4 py-2">
                                {{-- Link to event details page --}}
                                <a href="{{ route('events.show', $event->id_event) }}" class="text-indigo-600 hover:underline">
                                    {{ $event->title }}
                                </a>
                            </td>

                            {{-- Start date --}}
                            <td class="px-4 py-2 text-sm text-gray-600">
                                {{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d H:i') : 'N/A' }}
                            </td>

                            {{-- Actions: Edit + Delete --}}
                            <td class="px-4 py-2 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    {{-- Edit button --}}
                                    <a
                                        href="{{ route('events.edit', $event->id_event) }}"
                                        class="inline-flex items-center px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>

                                    {{-- Delete button --}}
                                    <form
                                        id="delete-form-{{ $event->id_event }}"
                                        action="{{ route('events.destroy', $event->id_event) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-1 text-sm text-red-600 hover:text-red-800"
                                            onclick="openDeleteModal('delete-form-{{ $event->id_event }}')"
                                        >
                                            <i class="fas fa-trash mr-1"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Delete confirmation modal (hidden by default) --}}
{{-- 
    fixed     = the modal stays fixed on the screen (even if page scrolls)
    inset-0   = stretch to cover the whole screen
    bg-black/40 = black background with 40% opacity (dark overlay)
    flex items-center justify-center = center the box on the screen
    z-50      = put it on top of other elements
    hidden    = DO NOT show it at first (we remove this with JS)
--}}
<div
    id="delete-modal"
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden"
>
    {{-- White box in the middle of the screen --}}
    <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
        {{-- Modal header (title area) --}}
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">
                Delete event
            </h2>
        </div>

        {{-- Modal body (message text) --}}
        <div class="px-4 py-3">
            <p class="text-sm text-gray-700">
                Are you sure you want to delete this event? This action cannot be undone.
            </p>
        </div>

        {{-- Modal footer (buttons area) --}}
        <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
            {{-- Cancel button: closes the modal, does NOT delete --}}
            <button
                type="button"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                onclick="closeDeleteModal()"
            >
                Cancel
            </button>

            {{-- Delete button: confirms and submits the stored form --}}
            <button
                type="button"
                class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-red-600 hover:bg-red-700"
                onclick="confirmDelete()"
            >
                Delete
            </button>
        </div>
    </div>
</div>

{{-- JS --}}
<script>
    // Will store the form we should submit when user confirms
    let formToDelete = null;

    function openDeleteModal(formId) {
        formToDelete = document.getElementById(formId);
        const modal = document.getElementById('delete-modal');
        if (modal) modal.classList.remove('hidden');
    }

    // Hide the modal without deleting anything.
    // Called when the user clicks the "Cancel" button
    function closeDeleteModal() {
        const modal = document.getElementById('delete-modal');

        // Add the "hidden" class again so the modal disappears
        if (modal) modal.classList.add('hidden');

        // Forget which form we were going to delete
        formToDelete = null;
    }

    // Actually delete the event.
    // Called when the user clicks the red "Delete" button in the modal.
    function confirmDelete() {
        if (formToDelete) {
            formToDelete.submit();
        }
    }
</script>
@endsection
