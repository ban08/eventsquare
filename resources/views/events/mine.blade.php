{{-- This view shows the list of events related to the logged-in user --}}
@extends('layouts.app')

@section('title', 'My Events')

{{-- Main content area --}}
@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page header and filter tabs --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-slate-900">My Events</h1>
        </div>

        {{-- Filter tabs --}}
        <div class="border-b border-slate-200 mb-8">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('organized')" id="organized-tab"
                        class="whitespace-nowrap py-2 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600 tab-button">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Events I Organize ({{ $organizedEvents->count() }})
                </button>
                <button onclick="showTab('participating')" id="participating-tab"
                        class="whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm text-slate-500 hover:text-slate-700 hover:border-slate-300 tab-button">
                    <i class="fas fa-users mr-2"></i>
                    Events I Participate ({{ $participatedEvents->count() }})
                </button>
            </nav>
        </div>

        {{-- Events I Organize Section --}}
        <div id="organized-content" class="tab-content">
            @if($organizedEvents->isEmpty())
                <div class="rounded-3xl bg-white/90 p-10 text-center shadow-xl">
                    <i class="fas fa-calendar-plus text-4xl text-slate-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">No organized events</h3>
                    <p class="text-sm text-slate-500 mb-4">You haven't organized any events yet.</p>
                    {{-- BR13: Admins cannot create events --}}
                    @cannot('admin')
                    <a href="{{ route('events.create') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-plus"></i>
                        Create Your First Event
                    </a>
                    @endcannot
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($organizedEvents as $event)
                        <div>
                            <x-event-card :event="$event" :show-actions="true" :show-time-status="false" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Events I Participate Section --}}
        <div id="participating-content" class="tab-content hidden">
            @if($participatedEvents->isEmpty())
                <div class="rounded-3xl bg-white/90 p-10 text-center shadow-xl">
                    <i class="fas fa-users text-4xl text-slate-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">No participated events</h3>
                    <p class="text-sm text-slate-500 mb-4">You haven't joined any events yet.</p>
                    <a href="{{ route('events.index') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-search"></i>
                        Browse Events
                    </a>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($participatedEvents as $event)
                        <div>
                            <x-event-card :event="$event" :show-time-status="false" :show-view-only="true" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete confirmation modal (hidden by default) --}}
<div
    id="delete-modal"
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden"
>
    <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">
                Delete event forever
            </h2>
        </div>
        <div class="px-4 py-3">
            <p class="text-sm text-gray-700">
                This event has no registrations or invitations, so it can be deleted completely. Continue?
            </p>
        </div>
        <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
            <button
                type="button"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                onclick="closeDeleteModal()"
            >
                Keep event
            </button>
            <button
                type="button"
                class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-rose-700 hover:bg-rose-800"
                onclick="confirmDelete()"
            >
                Delete event
            </button>
        </div>
    </div>
</div>

{{-- JavaScript for tab switching and delete modal --}}
<script>
    // Will store the form we should submit when user confirms
    let deleteFormToSubmit = null;

    // Function to switch between tabs
    function showTab(tabName) {
        // Hide all content sections
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active state from all tabs
        document.querySelectorAll('.tab-button').forEach(tab => {
            tab.classList.remove('border-indigo-500', 'text-indigo-600');
            tab.classList.add('border-transparent', 'text-slate-500');
        });

        // Show selected content section
        document.getElementById(tabName + '-content').classList.remove('hidden');

        // Add active state to selected tab
        const activeTab = document.getElementById(tabName + '-tab');
        activeTab.classList.remove('border-transparent', 'text-slate-500');
        activeTab.classList.add('border-indigo-500', 'text-indigo-600');
    }

    // Function to open delete modal
    function openDeleteModal(formId) {
        deleteFormToSubmit = document.getElementById(formId);
        const modal = document.getElementById('delete-modal');
        if (modal) modal.classList.remove('hidden');
    }

    // Hide the delete modal without deleting anything
    function closeDeleteModal() {
        const modal = document.getElementById('delete-modal');
        if (modal) modal.classList.add('hidden');
        deleteFormToSubmit = null;
    }

    // Actually delete the event
    function confirmDelete() {
        if (deleteFormToSubmit) {
            deleteFormToSubmit.submit();
        }
    }

    // Initialize page with first tab active
    document.addEventListener('DOMContentLoaded', function() {
        showTab('organized');
    });
</script>
@endsection