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
use Illuminate\Support\Facades\Storage;

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
            'status'   => ['required', 'in:active,blocked'],
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
            'status'   => ['required', 'in:active,blocked'],
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

    /**
     * AD06: Delete a user account permanently.
     * This is a hard delete - the user and all related data will be removed.
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        $admin = Admin::where('email', $currentUser->email)->first();

        // Prevent admin from deleting themselves
        if ($user->email === $currentUser->email) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'You cannot delete your own account.');
        }

        // Store user info for logging before deletion
        $userEmail = $user->email;
        $userName = $user->name;
        $userId = $user->id_user;

        DB::transaction(function () use ($user, $admin, $userEmail, $userId) {
            // Log admin action before deleting user
            if ($admin) {
                $action = AdminAction::create([
                    'id_admin'   => $admin->id_admin,
                    'details'    => 'Deleted user account: ' . $userEmail . ' (ID: ' . $userId . ')',
                    'created_at' => now(),
                ]);

                AdminUserAction::create([
                    'id_action'   => $action->id_action,
                    'action'      => 'delete user account',
                    'target_user' => $userId,
                ]);
            }

            // Delete profile photo if exists
            if ($user->profile && $user->profile->photo_url) {
                Storage::disk('public')->delete($user->profile->photo_url);
            }

            // Delete profile (R03)
            DB::table('profile')->where('id_user', $user->id_user)->delete();

            // Remove participations, applications, invitations
            $user->participations()->delete();
            $user->applications()->delete();
            $user->invitations()->delete();

            // Anonymize and Soft Delete
            $user->name = 'Deleted User';
            $user->email = 'deleted_' . $user->id_user . '_' . time() . '@eventsquare.local';
            $user->password_hash = Hash::make(uniqid()); // Scramble password
            $user->status = 'deleted';
            $user->location = null;
            $user->save();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User "' . $userName . '" (' . $userEmail . ') deleted permanently.');
    }
}
