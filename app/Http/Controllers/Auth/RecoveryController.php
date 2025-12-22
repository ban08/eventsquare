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

/**
 * RecoveryController
 * 
 * Handles the password recovery process using security questions.
 * This replaces the standard email-based reset flow.
 */
class RecoveryController extends Controller
{
    /**
     * Display the initial password recovery request form.
     *
     * @return \Illuminate\View\View
     */
    public function showRequest()
    {
        return view('auth.forgot-password');
    }

    /**
     * Process the email submission and display security questions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showQuestion(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::with('securityAnswers')->where('email', $data['email'])->first();
        if (!$user || $user->securityAnswers->isEmpty()) {
            return back()->withErrors(['email' => 'We could not find recovery questions for that account.']);
        }

        // Load suggested questions
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

        // Store user ID in session for security
        $request->session()->put('recovery_user_id', $user->id_user);

        return view('auth.security-question', [
            'suggestedQuestions' => $suggestedQuestions,
            'userAnswers' => $user->securityAnswers,
            'email' => $user->email,
        ]);
    }

    /**
     * Validate the security answer and reset the password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // Resolve the selected question prompt
        $prompt = null;
        if (Schema::hasTable('security_question')) {
            $prompt = SecurityQuestion::where('id_security_question', $questionId)->value('prompt');
        }
        
        if (!$prompt && $questionId === 0) {
            $prompt = $request->input('question_prompt');
        }

        if (!$prompt) {
            return back()->withErrors(['question_id' => 'Please select a valid question.'])->withInput();
        }

        // Verify the answer against the stored hash
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
