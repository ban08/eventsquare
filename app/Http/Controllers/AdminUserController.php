<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\AdminAction;
use App\Models\AdminUserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        // Eager load profile (R03) for ER alignment
        $query = User::with('profile');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'ILIKE', "%{$q}%")
                    ->orWhere('email', 'ILIKE', "%{$q}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function show(User $user)
    {
        // Eager load profile for photo_url
        $user->load('profile');
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:user,email'],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'status'   => ['required', 'in:active,blocked,deleted'],
            'photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        $currentUser = Auth::user();
        $admin = Admin::where('email', $currentUser->email)->first();

        $user = null;

        DB::transaction(function () use ($data, $admin, &$user, $request) {
            $user = User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'location'      => $data['location'] ?? null,
                'password_hash' => Hash::make($data['password']),
                'status'        => $data['status'],
            ]);

            // Handle profile photo upload
            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $path = $file->store('profile_photos', 'public');
                $photoUrl = 'storage/' . $path;
            }

            // Create profile row per ER (R03)
            DB::table('profile')->insert([
                'id_user' => $user->id_user,
                'photo_url' => $photoUrl,
            ]);

            if ($admin) {
                $action = AdminAction::create([
                    'id_admin'   => $admin->id_admin,
                    'details'    => 'Created user ' . $user->email,
                    'created_at' => now(),
                ]);

                AdminUserAction::create([
                    'id_action'   => $action->id_action,
                    'action'      => 'create user account',
                    'target_user' => $user->id_user,
                ]);
            }
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User criado com sucesso.');
    }

    public function edit(User $user)
    {
        // Eager load profile for photo_url
        $user->load('profile');
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:user,email,' . $user->id_user . ',id_user'],
            'location' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'status'   => ['required', 'in:active,blocked,deleted'],
            'photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        $currentUser = Auth::user();
        $admin = Admin::where('email', $currentUser->email)->first();

        DB::transaction(function () use ($data, $admin, $user, $request) {
            $user->name     = $data['name'];
            $user->email    = $data['email'];
            $user->location = $data['location'] ?? null;
            $user->status   = $data['status'];

            if (!empty($data['password'])) {
                $user->password_hash = Hash::make($data['password']);
            }

            $user->save();

            // Handle profile photo upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $path = $file->store('profile_photos', 'public');
                $user->profile->photo_url = 'storage/' . $path;
                $user->profile->save();
            }

            if ($admin) {
                $action = AdminAction::create([
                    'id_admin'   => $admin->id_admin,
                    'details'    => 'Edited user ' . $user->email,
                    'created_at' => now(),
                ]);

                AdminUserAction::create([
                    'id_action'   => $action->id_action,
                    'action'      => 'edit user account',
                    'target_user' => $user->id_user,
                ]);
            }
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User atualizado com sucesso.');
    }
}
