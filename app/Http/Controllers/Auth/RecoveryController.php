<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecurityAnswer;
use App\Models\SecurityQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RecoveryController extends Controller
{
    // Show the initial "forgot password" form asking for email.
    public function showRequest()
    {
        return view('auth.forgot-password');
    }

    // After email submission, show the user's security question.
    public function showQuestion(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::with('securityAnswers')->where('email', $data['email'])->first();
        if (!$user || $user->securityAnswers->isEmpty()) {
            return back()->withErrors(['email' => 'We could not find recovery questions for that account.']);
        }

        // Load all suggested questions (fallback if table missing).
        $suggestedQuestions = [];
        if (Schema::hasTable('security_question')) {
            $suggestedQuestions = SecurityQuestion::query()->orderBy('prompt')->get();
        } else {
            $suggestedQuestions = collect([
                (object)['id_security_question' => 0, 'prompt' => "What is the name of a teacher you will never forget?"],
                (object)['id_security_question' => 0, 'prompt' => "What city did you visit on your first trip?"],
                (object)['id_security_question' => 0, 'prompt' => "What was your first pet's name?"],
            ]);
        }

        // Store the user we are recovering for in session to avoid tampering.
        $request->session()->put('recovery_user_id', $user->id_user);

        return view('auth.security-question', [
            'suggestedQuestions' => $suggestedQuestions,
            'userAnswers' => $user->securityAnswers,
            'email' => $user->email,
        ]);
    }

    // Validate answer + set new password.
    public function resetWithAnswer(Request $request)
    {
        $userId = $request->session()->get('recovery_user_id');
        if (!$userId) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please start the recovery process again.']);
        }

        $validator = Validator::make($request->all(), [
            'question_id' => ['required', 'integer'],
            'security_answer' => ['required', 'string', 'min:3', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::find($userId);
        $questionId = (int) $request->input('question_id');

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'We could not find recovery data for that account.']);
        }

        // Resolve the selected question prompt (from DB or fallback).
        $prompt = null;
        if (Schema::hasTable('security_question')) {
            $prompt = SecurityQuestion::where('id_security_question', $questionId)->value('prompt');
        }
        // If not found in suggestions table (e.g., table missing), use the submitted id only if 0 and fallback.
        if (!$prompt && $questionId === 0) {
            $prompt = $request->input('question_prompt'); // fallback not used in current form
        }

        if (!$prompt) {
            return back()->withErrors(['question_id' => 'Please select a valid question.'])->withInput();
        }

        // The user must have answered this question at signup.
        $answerRecord = SecurityAnswer::where('id_user', $user->id_user)
            ->where('question', $prompt)
            ->first();

        if (!$answerRecord || !Hash::check($request->security_answer, $answerRecord->answer_hash)) {
            return back()->withErrors(['security_answer' => 'The answer does not match our records.'])->withInput();
        }

        // Update password.
        $user->update([
            'password_hash' => Hash::make($request->password),
        ]);

        // Clear the session marker.
        $request->session()->forget('recovery_user_id');

        return redirect()->route('login')->with('success', 'Password updated. You can sign in now.');
    }
}
