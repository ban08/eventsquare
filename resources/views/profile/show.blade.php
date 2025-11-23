@extends('layouts.app')

@section('title', 'My Profile - EventSquare')

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <a href="{{ route('home') }}"
               class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to home
            </a>

            <div class="overflow-hidden rounded-3xl bg-white/95 shadow-[0_10px_30px_rgba(15,23,42,0.12)]">
                <div class="h-28 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

                <div class="p-6 sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">My Profile</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Review your personal details to ensure they are accurate.
                            </p>
                        </div>

                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-indigo-50">
                            <i class="fas fa-user text-indigo-600 text-xl"></i>
                        </div>
                    </div>

                    <dl class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                            <dt class="text-sm font-medium text-slate-500">Full name</dt>
                            <dd class="mt-1 text-sm text-slate-900 sm:mt-0">
                                {{ $user->name }}
                            </dd>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                            <dt class="text-sm font-medium text-slate-500">Email</dt>
                            <dd class="mt-1 text-sm text-slate-900 sm:mt-0">
                                {{ $user->email }}
                            </dd>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
                            <dt class="text-sm font-medium text-slate-500">Location</dt>
                            <dd class="mt-1 text-sm text-slate-900 sm:mt-0">
                                {{ $user->location ?? 'Not provided' }}
                            </dd>
                        </div>

                        @if(property_exists($user, 'status') || isset($user->status))
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <dt class="text-sm font-medium text-slate-500">Account status</dt>
                                <dd class="mt-1 text-sm sm:mt-0">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                                        {{ $user->status === 'active'
                                            ? 'bg-green-50 text-green-700'
                                            : 'bg-slate-100 text-slate-700' }}">
                                        <span class="h-1.5 w-1.5 rounded-full mr-2
                                            {{ $user->status === 'active' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                        {{ ucfirst($user->status ?? 'unknown') }}
                                    </span>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('events.index') }}"
                           class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <i class="fas fa-calendar-days mr-2 text-xs"></i>
                            View events
                        </a>

                        <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            <i class="fas fa-pen mr-2 text-xs"></i>
                            Edit profile
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
