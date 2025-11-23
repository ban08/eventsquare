@extends('layouts.app')

@section('title', 'New User')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-slate-900 mb-4">Create User</h1>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="active">active</option>
                    <option value="blocked">blocked</option>
                    <option value="deleted">deleted</option>
                </select>
            </div>

            <div>
                <label for="photo" class="block text-sm font-medium text-slate-700">Profile Picture</label>
                <label for="photo" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm cursor-pointer hover:bg-indigo-700 mt-2">
                    <i class="fas fa-upload"></i>
                    <span>Upload photo</span>
                    <input type="file" id="photo" name="photo" accept="image/*" class="hidden">
                </label>
                <span class="text-xs text-slate-500 block mt-1">Optional. You can add a profile photo now.</span>
            </div>

            <button class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Save</button>
        </form>
    </div>
</div>
@endsection
