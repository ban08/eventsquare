{{--Use the main layout file "layouts/app.blade.php"--}}
@extends('layouts.app')

{{--Set the page title--}}
@section('title', 'Sign Up - EventSquare')

{{--Start of the main content for this page--}}
@section('content')

{{--Full-height area with a gradient background and centered content--}}
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

    {{--Limit content width and add space between sections--}}
    <div class="max-w-md w-full space-y-8">
        
        <!-- Header -->
        {{--Center all header elements (icon, title, subtitle)--}}
        <div class="text-center">

            <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center mb-4">
                {{--Purple circle with an "add user" icon inside--}}
                <i class="fas fa-user-plus text-white text-2xl"></i>
            </div>

            {{--Main heading for the page--}}
            <h2 class="text-3xl font-bold text-gray-900">Join EventSquare</h2>
            {{--Short description text under the title--}}
            <p class="mt-2 text-sm text-gray-600">Create your account and start organizing events</p>
        </div>

        <!-- Sign Up Form -->
        {{--White card with shadow and rounded corners that holds the form--}}
        <div class="bg-white shadow-xl rounded-lg p-8">

            {{--Send the form data to the "register" route using POST--}}
            <form class="space-y-6" action="{{ route('register') }}" method="POST">
                {{--Add a CSRF token for security--}}
                @csrf

                <!-- Name -->
                <div>
                    {{--Label for the full name field--}}
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    {{--Wrapper so we can position the icon inside the input--}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            {{--User icon on the left side of the input--}}
                            <i class="fas fa-user text-gray-400"></i>
                        </div>

                        {{--Full name input--}}
                        {{--name="name" is the key used on the server side: $request->name--}}
                        {{--type="text" is a normal text field--}}
                        {{--required means the browser will not submit the form if this is empty--}}
                        <input
                            id="name"
                            name="name"
                            type="text"
                            required
                            class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                            placeholder="Name"
                            value="{{ old('name') }}"
                        >
                        {{--value="{{ old('name') }}" keeps what the user typed if the form has an error and reloads--}}
                    </div>

                    {{--If Laravel finds a validation error for "name", show the message here--}}
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    {{--Label for the email field--}}
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            {{--Envelope icon inside the input--}}
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>

                        {{--Email input--}}
                        {{--name="email" will be read as $request->email--}}
                        {{--type="email" gives basic email validation--}}
                        {{--autocomplete="email" helps the browser autofill the email--}}
                        {{--required means this cannot be empty when submitting--}}
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @enderror"
                            placeholder="name@email.com"
                            value="{{ old('email') }}"
                        >
                    </div>

                    {{--Show email validation error, if there is one--}}
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    {{--Label for the location field. This field is optional.--}}
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location (Optional)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            {{--Map pin icon inside the input--}}
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>

                        {{--Location input--}}
                        <input
                            id="location"
                            name="location"
                            type="text"
                            class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('location') border-red-300 @enderror"
                            placeholder="Porto, Portugal"
                            value="{{ old('location') }}"
                        >
                    </div>

                    {{--Show location validation error, if there is one--}}
                    @error('location')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password and Confirm Password in same row -->
                {{--Password fields side by side on larger screens--}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        {{--Label for the password field--}}
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                {{--Lock icon inside the input--}}
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>

                            {{--Password input--}}
                            {{--type="password" hides the characters as dots--}}
                            {{--required: user must fill this in before submitting--}}
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @enderror"
                                placeholder="••••••••"
                            >
                        </div>

                        {{--Show password validation error, if there is one--}}
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        {{--Label for the confirm password field--}}
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                {{--Another lock icon--}}
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>

                            {{--Confirm password input--}}
                            {{--name="password_confirmation" is the default name Laravel expects for confirming passwords--}}
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password_confirmation') border-red-300 @enderror"
                                placeholder="••••••••"
                            >
                        </div>

                        {{--Show confirm password validation error, if there is one--}}
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    {{--Submit button container--}}
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium cursor-pointer rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            {{--Icon area on the left side of the button--}}
                            <i class="fas fa-user-plus text-indigo-500 group-hover:text-indigo-400"></i>
                        </span>
                        Create Account
                    </button>
                </div>
            </form>

            {{--Section under the form with a divider and a login link--}}
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        {{--Horizontal line--}}
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    {{--Center the small text on top of the line--}}
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                        Sign in
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
