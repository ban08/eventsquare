@extends('layouts.app')

@section('title', 'Edit Profile - EventSquare')

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <a href="{{ route('profile.show') }}"
               class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to profile
            </a>

            <div class="overflow-hidden rounded-3xl bg-white/95 shadow-[0_10px_30px_rgba(15,23,42,0.12)]">
                <div class="h-28 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-slate-900">Edit Profile</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Update your personal information below.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Full Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                Full name
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                                Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">
                                Location (optional)
                            </label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location', $user->location) }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('location') border-red-500 @enderror"
                                placeholder="e.g., Porto, Portugal"
                            >
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('profile.show') }}"
                               class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                <i class="fas fa-save mr-2 text-xs"></i>
                                Save changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
