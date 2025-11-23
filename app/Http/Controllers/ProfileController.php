<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    //RU01
    public function show(): View
    {
        $user = Auth::user();

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    // RU02 – Show edit form
    public function edit(): View
    {
        $user = Auth::user();

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    // RU02 – Handle update
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:250',
            'email'     => 'required|email|max:250|unique:user,email,' . $user->id_user . ',id_user',
            'location'  => 'nullable|string|max:250',
            'password'  => 'nullable|min:8|confirmed',
        ]);

        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->location = $request->location;

        if ($request->filled('password')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.show')
            ->with('success', 'Your profile has been updated successfully.');
    }
}
