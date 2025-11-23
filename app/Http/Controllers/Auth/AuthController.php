<?php

/**
 * AuthController - Custom authentication controller.
 *
 * We cannot use Laravel's default authentication scaffolding because:
 * - users are stored in the `user` table (not the default `users`);
 * - passwords are stored in the `password_hash` column;
 * - extra fields like `location` and `status` must be
 *   validated and saved on registration;
 * - each new user must also get an associated row in the `profile` table.
 *
 * This controller handles user authentication:
 * - Showing the registration and login forms
 * - Validating registration and login input
 * - Creating a new user and an empty profile on registration
 * - Hashing and storing the user's password securely
 * - Logging users in and redirecting them to the events page
 * - Logging users out and clearing their session
 */

namespace App\Http\Controllers\Auth;

// Import other classes
use App\Http\Controllers\Controller; 
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// AuthController will handle register, login, logout, etc.
class AuthController extends Controller
{
    /**
     * Show the registration form.
     * Called when the user visits the register page.
     */
    public function showRegister()
    {
        // Return the Blade view stored at resources/views/auth/register.blade.php
        return view('auth.register');
    }

    /**
     * Handle a registration request (when user submits the register form).
     */
    public function register(Request $request)
    {
        // 1. Validate the incoming form data.
        // $request->all() contains all input fields from the form.        
        $validator = Validator::make($request->all(), [
            // 'name' is required, must be a string, and max length 255 characters.
            'name' => ['required', 'string', 'max:255'],

            // 'email' is required, must look like an email, max 255 chars,
            // and must be unique in the "user" table, "email" column.            
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user,email'],

            // 'password' is required, must be a string, at least 8 chars,
            // and 'confirmed' means there must also be a 'password_confirmation'
            // field and it must match 'password'.            
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // 'location' can be null (nullable). If it's present, it must be
            // a string with max 255 chars.            
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        // 2. If validation fails, throw a ValidationException with error messages
        // Laravel redirects automatically back to the form and show these errors        
        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // 3. If validation passes, create a new user record in the database.
        // User::create() will insert a new row into the user table.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // Hash::make() encrypts the password so it is NOT stored in plain text
            'password_hash' => Hash::make($request->password),
            'location' => $request->location,
            // Set default status for new users
            'status' => 'active',
        ]);

        // 4. After creating the user, also create an empty profile for this user.
        // Directly insert into the "profile" table.
        // 'id_user' is a foreign key that links this profile to the user just created.        
        DB::table('profile')->insert([
            'id_user' => $user->id_user,
        ]);

        // 5. Log the user in immediately after registration
        Auth::login($user);

        // 6. Redirect the user to the /login page with a success message in the session.
        // Message shown in the view using session('success').        
        return redirect('/login')->with('success', 'Account created successfully!');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('events.index');
        }
        
        // Return the Blade view stored at resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {

        // 1. Validate login form input
        // $request->validate() automatically redirects back with errors if validation fails        
        $credentials = $request->validate([
            // 'email' is required and must be a valid email format
            'email' => ['required', 'email'],

            // 'password' is required
            'password' => ['required'],
        ]);

        // 2. Try to log the user in.
        // Auth::attempt() will check the 'user' table for a matching email
        // and verify that the password matches the stored (hashed) password.        
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {

            // If login is successful:
            // Regenerate the session ID for security.            
            $request->session()->regenerate();

            // Redirect the user to the route named 'events.create'
            // return redirect()->route('events.create');
            // return redirect()->route('login');
            return redirect()->route('events.index');
;
        }

        // 3. If login failed (wrong email or password), throw a ValidationException
        // The error will be attached to the 'email' field and shown in the form.        
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }


    /**
     * Handle a logout request (when user clicks Logout).
     */
    public function logout(Request $request)
    {
        // 1. Log the user out.
        Auth::logout();

        // 2. Clear all session data.
        $request->session()->invalidate();

        // 3. Regenerate the CSRF token for security.
        $request->session()->regenerateToken();

        // 4. Redirect the logged out user back to the login page.
        return redirect()->route('login')
        ->withSuccess('You have logged out successfully!');
    }
}