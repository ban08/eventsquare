<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // RU01
    public function show(): View
    {
        $user = Auth::user();
        // Ensure profile exists (lazy creation if missing)
        if (!$user->profile) {
            Profile::create(['id_user' => $user->id_user]);
            $user->refresh();
        }

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    // RU02 – Show edit form
    public function edit(): View
    {
        $user = Auth::user();
        if (!$user->profile) {
            Profile::create(['id_user' => $user->id_user]);
            $user->refresh();
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    // RU02 & RU03 – Handle update
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:250',
            'email'     => 'required|email|max:250|unique:user,email,' . $user->id_user . ',id_user',
            'location'  => 'nullable|string|max:250',
            'password'  => 'nullable|min:8|confirmed',
            'photo'     => 'nullable|image|max:2048', // RU03: Max 2MB image
        ]);

        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->location = $request->location;

        if ($request->filled('password')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        // RU03: Handle Profile Picture Upload
        if ($request->hasFile('photo')) {
            $profile = $user->profile ?? Profile::create(['id_user' => $user->id_user]);

            // Delete old photo if exists
            if ($profile->photo_url) {
                Storage::disk('public')->delete($profile->photo_url);
            }

            // Store new photo
            $path = $request->file('photo')->store('profiles', 'public');
            $profile->photo_url = $path;
            $profile->save();
        }

        return redirect()->route('profile.show')
            ->with('success', 'Your profile has been updated successfully.');
    }
}
