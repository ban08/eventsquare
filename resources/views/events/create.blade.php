@extends('layouts.app')

{{-- Set the page title that will appear in the browser tab --}}
@section('title', 'Create Event - EventSquare')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    {{--Center the content and limit its width--}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            {{--Main page title--}}
            <h1 class="text-3xl font-bold text-gray-900">Create New Event</h1>
            <p class="mt-2 text-gray-600">Fill in the details below to organize your amazing event</p>
        </div>

        <!-- Form -->
         {{--White card that holds the form--}}
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <form action="{{ route('events.store') }}" method="POST">
                @csrf

                <!-- Form Header -->
                 {{--Colored bar at the top of the form with a title--}}
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Event Details
                    </h2>
                </div>

                <!-- Form Body -->
                <div class="p-6 space-y-8">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-indigo-500"></i>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="lg:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                    Event Title *
                                </label>
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror"
                                    value="{{ old('title') }}"
                                    placeholder="Enter a catchy title for your event"
                                    required
                                >
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Venue field --}}
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">
                                    Venue *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="venue"
                                        name="venue"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('venue') border-red-500 @enderror"
                                        value="{{ old('venue') }}"
                                        placeholder="Where will the event take place?"
                                        required
                                    >
                                </div>
                                @error('venue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{--Capacity field (how many people can join)--}}
                            <div>
                                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                                    Capacity *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    <input
                                        type="number"
                                        id="capacity"
                                        name="capacity"
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('capacity') border-red-500 @enderror"
                                        value="{{ old('capacity') ?? '50' }}"
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
                            {{-- Start date and time --}}
                            <div>
                                <label for="start_at" class="block text-sm font-medium text-gray-700 mb-1">
                                    Start Date &amp; Time *
                                </label>
                                <input
                                    type="datetime-local"
                                    id="start_at"
                                    name="start_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('start_at') border-red-500 @enderror"
                                    value="{{ old('start_at') }}"
                                    required
                                >
                                @error('start_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- End date and time --}}
                            <div>
                                <label for="end_at" class="block text-sm font-medium text-gray-700 mb-1">
                                    End Date &amp; Time *
                                </label>
                                <input
                                    type="datetime-local"
                                    id="end_at"
                                    name="end_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('end_at') border-red-500 @enderror"
                                    value="{{ old('end_at') }}"
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
                            {{-- Visibility dropdown (public or private) --}}
                            <div>
                                <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">
                                    Visibility *
                                </label>
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
                                        {{-- Public option. Selected by default or if old value is "public". --}}
                                        <option value="public" {{ old('visibility') == 'public' || !old('visibility') ? 'selected' : '' }}>
                                            Public - Anyone can see and join
                                        </option>
                                        {{-- Private option. Selected if old value is "private". --}}
                                        <option value="private" {{ old('visibility') == 'private' ? 'selected' : '' }}>
                                            Private - Only invited users can join
                                        </option>
                                    </select>
                                </div>
                                @error('visibility')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                             {{-- Tags selection (optional checkboxes) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tags (Optional)
                                </label>
                                {{-- Scrollable box with a list of tags --}}
                                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 rounded-lg bg-white">
                                    @foreach($tags as $tag)
                                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="tags[]"
                                                value="{{ $tag->id_tag }}"
                                                {{ in_array($tag->id_tag, old('tags', [])) ? 'checked' : '' }}
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
                        {{-- Textarea for the event description --}}
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
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                    {{-- Cancel button: go back to the events page --}}
                    <a
                        href="{{ url('/events') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2 border border-transparent rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors flex items-center"
                    >
                        <i class="fas fa-check mr-2"></i>
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
