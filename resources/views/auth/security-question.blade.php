@extends('layouts.app')

@section('title', 'Answer Security Question')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">Answer your security question</h2>
            <p class="mt-2 text-sm text-gray-600">We found your account ({{ $email }}). Choose one question to reset your password.</p>
        </div>

        <div class="bg-white shadow-xl rounded-lg p-8">
            <form class="space-y-6" method="POST" action="{{ route('password.reset.security') }}">
                @csrf

                <div>
                    <label for="question_id" class="block text-sm font-semibold text-gray-800 mb-2">Select a question</label>
                    <select
                        id="question_id"
                        name="question_id"
                        required
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('question_id') border-red-300 @enderror"
                    >
                        <option value="" disabled selected>Choose a question</option>
                        @foreach($suggestedQuestions as $q)
                            <option value="{{ $q->id_security_question ?? 0 }}" @selected(old('question_id') == ($q->id_security_question ?? 0))>
                                {{ $q->prompt }}
                            </option>
                        @endforeach
                    </select>
                    @error('question_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Only questions you answered at signup will work.</p>
                </div>

                <div>
                    <label for="security_answer" class="block text-sm font-medium text-gray-700 mb-1">Your answer</label>
                    <input
                        id="security_answer"
                        name="security_answer"
                        type="text"
                        required
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('security_answer') border-red-300 @enderror"
                        placeholder="Type your answer"
                        value="{{ old('security_answer') }}"
                    >
                    @error('security_answer')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-300 @enderror"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('password_confirmation') border-red-300 @enderror"
                            placeholder="Repeat password"
                        >
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Reset password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
