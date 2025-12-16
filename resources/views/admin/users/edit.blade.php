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
                    @foreach(['active','blocked'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i>Save
                </button>
                {{-- AD06: Delete user button --}}
                <button type="button"
                        onclick="openDeleteUserModal()"
                        class="inline-flex items-center rounded-full bg-rose-700 px-4 py-2 text-sm font-medium text-white hover:bg-rose-800">
                    <i class="fas fa-trash mr-2"></i>Delete User
                </button>
                <a href="{{ route('admin.users.show', $user) }}"
                   class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </form>
    </div>
</div>

{{-- AD06: Delete user form (hidden) --}}
<form id="delete-user-form-{{ $user->id_user }}"
      action="{{ route('admin.users.destroy', $user) }}"
      method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

{{-- AD06: Delete user confirmation modal --}}
<div id="delete-user-modal"
     class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="px-4 py-3 border-b border-rose-200 bg-rose-50">
            <h2 class="text-base font-semibold text-rose-900">
                <i class="fas fa-exclamation-triangle mr-2"></i>Delete User Account
            </h2>
        </div>
        <div class="px-4 py-3">
            <p class="text-sm text-gray-700">
                <strong>Warning:</strong> You are about to permanently delete this user account. This action cannot be undone and will remove:
            </p>
            <ul class="text-sm text-gray-600 mt-2 ml-4 list-disc">
                <li>User profile and personal data</li>
                <li>All participations and applications</li>
                <li>All invitations sent to this user</li>
            </ul>
            <p class="text-sm text-gray-700 mt-3">
                User: <strong>{{ $user->name }}</strong> ({{ $user->email }})
            </p>
        </div>
        <div class="px-4 py-3 bg-gray-50 flex justify-end space-x-2">
            <button type="button"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-100"
                    onclick="closeDeleteUserModal()">
                Cancel
            </button>
            <button type="button"
                    class="px-3 py-1.5 text-sm border border-transparent rounded-md text-white bg-rose-700 hover:bg-rose-800"
                    onclick="confirmDeleteUser()">
                <i class="fas fa-trash mr-1"></i>Delete Permanently
            </button>
        </div>
    </div>
</div>

{{-- JavaScript for delete modal --}}
<script>
    function openDeleteUserModal() {
        const modal = document.getElementById('delete-user-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeDeleteUserModal() {
        const modal = document.getElementById('delete-user-modal');
        if (modal) modal.classList.add('hidden');
    }

    function confirmDeleteUser() {
        const form = document.getElementById('delete-user-form-{{ $user->id_user }}');
        if (form) form.submit();
    }
</script>
@endsection
