@extends('layouts.app')

@section('title', 'Admin – Users')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Manage Users</h1>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                New User
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Search by name or email"
                   class="flex-1 rounded-full border border-slate-300 px-3 py-2 text-sm">
            <button class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl bg-white shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Created</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                    <tr>
                        <td class="px-3 py-2">{{ $user->id_user }}</td>
                        <td class="px-3 py-2">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->email }}</td>
                        <td class="px-3 py-2">{{ $user->status }}</td>
                        <td class="px-3 py-2 text-xs text-slate-500">{{ $user->created_at }}</td>
                        <td class="px-3 py-2 space-x-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 text-xs">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-600 text-xs">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
