@extends('layouts.app')

@section('title', 'Edit User ' . $user->name)

@section('content')
<div class="min-h-screen bg-slate-50/50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Back Navigation --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('admin.users.show', $user) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to User Details
                    </a>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden ring-1 ring-slate-900/5">
            <div class="p-8 sm:p-10">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit User #{{ $user->id_user }}</h1>
                    <p class="text-slate-500 mt-2">Update user information and status.</p>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_has_file" value="1">

                    {{-- Profile Picture Upload --}}
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <label class="block text-sm font-medium text-slate-900 mb-4">Profile Picture</label>
                        <div class="flex items-center gap-6">
                            <div class="relative w-24 h-24 rounded-full bg-white border-4 border-white shadow-md overflow-hidden shrink-0">
                                @if($user->profile && $user->profile->photo_url)
                                    <img id="preview-image" src="{{ Str::startsWith($user->profile->photo_url, 'http') ? $user->profile->photo_url : (Str::startsWith($user->profile->photo_url, 'storage/') ? asset($user->profile->photo_url) : asset('storage/' . $user->profile->photo_url)) }}" class="w-full h-full object-cover">
                                @else
                                    <div id="preview-placeholder" class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                        <i class="fas fa-user text-3xl"></i>
                                    </div>
                                    <img id="preview-image" src="#" class="hidden w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="photo" id="photo" accept="image/*" 
                                       class="block w-full text-sm text-slate-500
                                              file:mr-4 file:py-2.5 file:px-4
                                              file:rounded-xl file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-indigo-600 file:text-white
                                              hover:file:bg-indigo-700
                                              transition-all cursor-pointer">
                                <p class="mt-2 text-xs text-slate-500">JPG, PNG or GIF (Max 2MB)</p>
                                @error('photo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div>
                            <label for="location" class="block text-sm font-medium text-slate-700 mb-1.5">Location</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-slate-400"></i>
                                </div>
                                <input type="text" name="location" id="location" value="{{ old('location', $user->location) }}"
                                       class="w-full pl-10 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                            </div>
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status (Admin Only) --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">Account Status</label>
                            <select name="status" id="status" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                                @foreach(['active', 'blocked'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="border-slate-100 my-8">

                    <div class="flex items-center justify-between">
                        <button type="button" onclick="openDeleteUserModal()" class="text-rose-600 hover:text-rose-700 font-medium text-sm">
                            Delete User Account
                        </button>

                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all shadow-lg shadow-indigo-600/20">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete User Modal (Same as before) --}}
<form id="delete-user-form-{{ $user->id_user }}"
      action="{{ route('admin.users.destroy', $user) }}"
      method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<div id="delete-user-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <div class="p-6">
            <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mb-4">
                <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Delete User Account</h2>
            <p class="text-slate-600 text-sm leading-relaxed">
                Are you sure you want to delete this user? This action will permanently remove their profile, events, and all associated data. This cannot be undone.
            </p>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
            <button type="button" onclick="closeDeleteUserModal()" class="px-4 py-2 text-slate-700 font-medium text-sm hover:bg-slate-100 rounded-lg transition-colors">
                Cancel
            </button>
            <button type="button" onclick="document.getElementById('delete-user-form-{{ $user->id_user }}').submit()" class="px-4 py-2 bg-rose-600 text-white font-medium text-sm hover:bg-rose-700 rounded-lg shadow-lg shadow-rose-600/20 transition-all">
                Delete User
            </button>
        </div>
    </div>
</div>

<script>
    function openDeleteUserModal() {
        document.getElementById('delete-user-modal').classList.remove('hidden');
    }
    function closeDeleteUserModal() {
        document.getElementById('delete-user-modal').classList.add('hidden');
    }
    
    // Image preview script
    const photoInput = document.getElementById('photo');
    const previewImage = document.getElementById('preview-image');
    const previewPlaceholder = document.getElementById('preview-placeholder');

    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
                if(previewPlaceholder) previewPlaceholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
