<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // RU01
    public function show(User $user = null)
    {
        // If no user provided, show current user
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('login');
        }

        // If current user is admin, redirect to admin user view
        if (Gate::allows('admin')) {
            return redirect()->route('admin.users.show', $user);
        }

        // Prevent interaction with deleted users
        if ($user->status === 'deleted') {
            abort(404);
        }

        // Prevent viewing admin profiles
        if (\App\Models\Admin::where('email', $user->email)->exists()) {
            abort(404);
        }

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
        
        // Ensure user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

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

    // RU07 - Delete Account
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        // Delete profile picture if exists
        if ($user->profile && $user->profile->photo_url) {
            Storage::disk('public')->delete($user->profile->photo_url);
            $user->profile->update(['photo_url' => null]);
        }

        // Remove participations, applications, invitations
        $user->participations()->delete();
        $user->applications()->delete();
        $user->invitations()->delete();

        // Cancel all active events organized by this user
        $organizedEvents = \App\Models\Event::where('id_organizer', $user->id_user)
            ->where('status', 'published')
            ->where('start_at', '>', now())
            ->get();

        foreach ($organizedEvents as $event) {
            $event->update(['status' => 'canceled']);
            
            // Notify participants
            $participants = $event->participants()->wherePivot('left_at', null)->get();
            foreach ($participants as $participant) {
                if ($participant->id_user !== $user->id_user) {
                    \App\Models\Notification::create([
                        'id_user' => $participant->id_user,
                        'message' => 'event canceled',
                        'id_event' => $event->id_event,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Anonymize and Soft Delete
        $user->name = 'Deleted User';
        $user->email = 'deleted_' . $user->id_user . '_' . time() . '@eventsquare.local';
        $user->password_hash = Hash::make(uniqid()); // Scramble password
        $user->status = 'deleted';
        $user->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been successfully deleted.');
    }
}

