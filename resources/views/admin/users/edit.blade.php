@extends('layouts.app')

@section('title', 'Edit User '.$user->id_user)

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-slate-900 mb-4">Edit User #{{ $user->id_user }}</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <input type="hidden" name="_has_file" value="1">

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Profile Picture</label>
                    @if($user->profile && $user->profile->photo_url)
                        <img src="{{ asset($user->profile->photo_url) }}" alt="Profile photo" class="w-24 h-24 min-w-24 min-h-24 max-w-24 max-h-24 rounded-full object-cover border mb-2" style="width:96px;height:96px;" />
                    @endif
                    <label for="photo" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm cursor-pointer hover:bg-indigo-700 mt-2">
                        <i class="fas fa-upload"></i>
                        <span>Upload new photo</span>
                        <input type="file" id="photo" name="photo" accept="image/*" class="hidden">
                    </label>
                    <span class="text-xs text-slate-500 block mt-1">Leave empty to keep current photo.</span>
                </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Location</label>
                <input type="text" name="location" value="{{ old('location', $user->location) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Password (leave empty to keep)</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach(['active','blocked','deleted'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <button class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Save</button>
        </form>
    </div>
</div>
@endsection
