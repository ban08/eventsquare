<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    //Show the authenticated user's profile.
    public function show(): View
    {
        $user = Auth::user();

        return view('profile.show', [
            'user' => $user,
        ]);
    }
}
