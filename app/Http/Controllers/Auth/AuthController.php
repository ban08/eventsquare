<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; 
use App\Models\User;
use App\Models\Profile;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 * 
 * Handles user authentication and registration flows.
 * Custom implementation required due to non-standard database schema:
 * - Table: `user` (not `users`)
 * - Password column: `password_hash`
 * - Additional fields: `location`, `status`
 * - Profile creation on registration
 */
class AuthController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        $suggestedQuestions = Schema::hasTable('security_question')
            ? SecurityQuestion::orderBy('prompt')->get()
            : collect();

        return view('auth.register', compact('suggestedQuestions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        // 1. Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'location' => ['nullable', 'string', 'max:255'],
            'security_questions' => ['required', 'array', 'min:1'],
            'security_questions.*' => ['required', 'string', 'min:5', 'max:255'],
            'security_answers' => ['required', 'array', 'min:1'],
            'security_answers.*' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        // Process security questions
        $rawQuestions = $request->input('security_questions', []);
        $rawAnswers = $request->input('security_answers', []);
        $securityPairs = [];
        $pairCount = min(count($rawQuestions), count($rawAnswers));
        
        for ($i = 0; $i < $pairCount && count($securityPairs) < 3; $i++) {
            $q = trim((string) ($rawQuestions[$i] ?? ''));
            $a = trim((string) ($rawAnswers[$i] ?? ''));
            if ($q === '' || $a === '') continue;
            $securityPairs[] = ['question' => $q, 'answer' => $a];
        }

        // Enforce unique questions (case-insensitive) to avoid duplicates
        $uniqueQuestions = [];
        foreach ($securityPairs as $pair) {
            $key = mb_strtolower($pair['question']);
            if (isset($uniqueQuestions[$key])) {
                throw ValidationException::withMessages([
                    'security_questions.0' => ['Security questions must be unique.'],
                ]);
            }
            $uniqueQuestions[$key] = true;
        }
        if (empty($securityPairs)) {
            throw ValidationException::withMessages([
                'security_questions.0' => ['Please provide at least one security question and answer.'],
            ]);
        }

        // 2. Create user record
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'location' => $request->location,
            'status' => 'active',
        ]);

        // 3. Create associated profile
        DB::table('profile')->insert([
            'id_user' => $user->id_user,
        ]);

        // 4. Store security questions
        $rows = [];
        foreach ($securityPairs as $pair) {
            $rows[] = [
                'id_user' => $user->id_user,
                'question' => $pair['question'],
                'answer_hash' => Hash::make($pair['answer']),
            ];
        }
        DB::table('security_answer')->insert($rows);

        // 5. Authenticate user
        Auth::login($user);

        return redirect('/login')->with('success', 'Account created successfully!');
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('events.index');
        }
        
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->route('events.index');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Explicitly save the session to prevent race conditions with the redirect
        $request->session()->save();

        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    }
}