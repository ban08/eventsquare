@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Back Navigation --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('profile.show') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Profile
                    </a>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden ring-1 ring-slate-900/5">
            <div class="p-8 sm:p-10">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Profile</h1>
                    <p class="text-slate-500 mt-2">Update your personal information and public profile.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    {{-- Profile Picture Upload --}}
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <label class="block text-sm font-medium text-slate-900 mb-4">Profile Picture</label>
                        <div class="flex items-center gap-6">
                            <div class="relative w-24 h-24 rounded-full bg-white border-4 border-white shadow-md overflow-hidden shrink-0">
                                @if($user->profile && $user->profile->photo_url)
                                    <img id="preview-image" src="{{ Str::startsWith($user->profile->photo_url, 'http') ? $user->profile->photo_url : (Str::startsWith($user->profile->photo_url, 'storage/') ? asset($user->profile->photo_url) : asset('storage/' . $user->profile->photo_url)) }}" alt="Current profile photo" class="w-full h-full object-cover">
                                @else
                                    <div id="preview-placeholder" class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                        <i class="fas fa-user text-3xl"></i>
                                    </div>
                                    <img id="preview-image" src="#" alt="New profile photo preview" class="hidden w-full h-full object-cover">
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
                    </div>

                    <hr class="border-slate-100 my-8">

                    {{-- Password Change (Optional) --}}
                    <div class="bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                        <h3 class="text-base font-semibold text-slate-900 mb-4">Change Password</h3>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
                                <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                                       class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm transition-colors py-2.5">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <a href="{{ route('profile.show') }}" class="text-slate-600 hover:text-slate-900 font-medium mr-6 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-600/20 transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>

                <hr class="border-slate-100 my-10">

                {{-- Delete Account Button --}}
                <div class="flex justify-start">
                    <button type="button" onclick="document.getElementById('delete-account-modal').classList.remove('hidden')" 
                            class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-200 rounded-lg font-medium text-red-600 hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Account Modal --}}
<div id="delete-account-modal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" onclick="document.getElementById('delete-account-modal').classList.add('hidden')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('DELETE')

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Account</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete your account? All of your data will be permanently removed. This action cannot be undone.
                            </p>
                        </div>
                        <div class="mt-4">
                            <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="delete_password" required placeholder="Enter your password to confirm"
                                   class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                            @error('password', 'userDeletion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete Account
                    </button>
                    <button type="button" onclick="document.getElementById('delete-account-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview-image');
                const placeholder = document.getElementById('preview-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection