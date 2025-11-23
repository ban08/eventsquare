@extends('layouts.app')

@section('title', 'User '.$user->id_user)

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-slate-900 mb-4">User #{{ $user->id_user }}</h1>

        <div class="rounded-3xl bg-white p-6 shadow">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="font-medium text-slate-700">Name</dt>
                    <dd class="text-slate-900">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-700">Email</dt>
                    <dd class="text-slate-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-700">Location</dt>
                    <dd class="text-slate-900">{{ $user->location ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-700">Status</dt>
                    <dd class="text-slate-900">{{ $user->status }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-700">Created at</dt>
                    <dd class="text-slate-900">{{ $user->created_at }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-700">Profile Picture</dt>
                    <dd>
                        @if($user->profile && $user->profile->photo_url)
                            <img src="{{ asset($user->profile->photo_url) }}" alt="Profile photo" class="w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 rounded-full object-cover border" style="width:96px;height:96px;" />
                        @else
                            <span class="text-slate-500">No photo</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                    Edit
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                    Back to list
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
