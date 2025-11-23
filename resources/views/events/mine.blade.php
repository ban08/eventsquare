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
                            {{-- Actions: an "Edit" link with an icon --}}
                            <td class="px-4 py-2 text-sm text-gray-600">
                                {{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d H:i') : 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{-- Edit icon/link --}}
                                <a
                                    href="{{ route('events.edit', $event->id_event) }}"
                                    class="inline-flex items-center px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800"
                                >
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
