@extends('layouts.app')

@section('title', 'Admin - User ' . $user->name)

@section('content')
<div class="min-h-screen bg-slate-50/50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Back Navigation --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-users mr-2"></i> Manage Users
                    </a>
                </li>
                <li><span class="text-slate-300">/</span></li>
                <li><span class="text-slate-600 font-medium">{{ $user->name }}</span></li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden ring-1 ring-slate-900/5">
            
            {{-- Decorative Cover --}}
            <div class="h-48 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-black/20 to-transparent"></div>
            </div>

            <div class="px-8 pb-10">
                <div class="relative flex flex-col sm:flex-row items-end -mt-16 mb-6 sm:space-x-6">
                    
                    {{-- Avatar --}}
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-2xl border-4 border-white bg-white shadow-lg overflow-hidden relative z-10">
                            @if($user->profile && $user->profile->photo_url)
                                <img src="{{ Storage::url($user->profile->photo_url) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-300">
                                    <i class="fas fa-user text-4xl"></i>
                                </div>
                            @endif
                        </div>
                        {{-- Status Indicator --}}
                        @php
                            $statusColor = match($user->status) {
                                'active' => 'bg-emerald-500',
                                'blocked' => 'bg-rose-500',
                                'deleted' => 'bg-slate-500',
                                default => 'bg-gray-500',
                            };
                        @endphp
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 {{ $statusColor }} border-4 border-white rounded-full z-20" title="{{ ucfirst($user->status) }}"></div>
                    </div>

                    {{-- Name & Actions --}}
                    <div class="flex-1 w-full sm:w-auto mt-4 sm:mt-0 text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                                <div class="flex items-center justify-center sm:justify-start text-slate-500 mt-1 space-x-4">
                                    <span class="flex items-center text-sm">
                                        <i class="fas fa-envelope w-4 text-slate-400 mr-1.5"></i>
                                        {{ $user->email }}
                                    </span>
                                    @if($user->location)
                                        <span class="flex items-center text-sm">
                                            <i class="fas fa-map-marker-alt w-4 text-slate-400 mr-1.5"></i>
                                            {{ $user->location }}
                                        </span>
                                    @endif
                                    <span class="flex items-center text-sm">
                                        <i class="fas fa-id-badge w-4 text-slate-400 mr-1.5"></i>
                                        ID: {{ $user->id_user }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Action Buttons --}}
                            <div class="mt-4 sm:mt-0 flex space-x-3 justify-center sm:justify-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 focus:ring-4 focus:ring-slate-200 transition-all shadow-lg shadow-slate-900/20">
                                    <i class="fas fa-pen-to-square mr-2"></i>
                                    Edit User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500 text-sm font-medium">Member Since</span>
                            <i class="fas fa-calendar-alt text-indigo-400"></i>
                        </div>
                        <div class="text-2xl font-bold text-slate-900">{{ $user->created_at->format('M Y') }}</div>
                    </div>
                    
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500 text-sm font-medium">Events Organized</span>
                            <i class="fas fa-bullhorn text-purple-400"></i>
                        </div>
                        <div class="text-2xl font-bold text-slate-900">{{ $user->events()->count() }}</div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-slate-500 text-sm font-medium">Participations</span>
                            <i class="fas fa-ticket-alt text-pink-400"></i>
                        </div>
                        <div class="text-2xl font-bold text-slate-900">{{ $user->participations()->count() }}</div>
                    </div>
                </div>

                {{-- Bio Section (if exists) --}}
                @if($user->profile && $user->profile->bio)
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">About</h3>
                    <p class="text-slate-600 leading-relaxed">{{ $user->profile->bio }}</p>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
