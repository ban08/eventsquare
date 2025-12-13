{{--Use the "layouts/app.blade.php" layout file as the base HTML structure--}}
@extends('layouts.app')

{{--Set the page title--}}
@section('title', 'Sign In - EventSquare')

{{--Start of the main content section that will be inserted into the layout--}}
@section('content')

{{--Full-height area with a gradient background and centered content--}}
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

     {{--Limit content width and add space between sections--}}
    <div class="max-w-md w-full space-y-8">


        <!-- Header -->
         {{--Center all header elements (icon, title, subtitle)--}}
        <div class="text-center">
            
            {{--Circular blue background with an icon in the middle--}}
            <div class="mx-auto h-16 w-16 bg-indigo-600 rounded-full flex items-center justify-center mb-4">
                {{--Font Awesome calendar icon (white, bigger size)--}}
                <i class="fas fa-calendar-alt text-white text-2xl"></i>
            </div>

            {{--Main heading text for the page--}}
            <h2 class="text-3xl font-bold text-gray-900">Sign in to EventSquare</h2>
            {{--Small subtitle to welcome the user--}}
            <p class="mt-2 text-sm text-gray-600">Welcome back! Please sign in to continue.</p>
        </div>

        <!-- Sign In Form -->
         {{--White card with shadow and rounded corners containing the form--}}
        <div class="bg-white shadow-xl rounded-lg p-8">

            {{--Form that will send a POST request to the "login" route when submitted--}}
            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                {{--Security token required by Laravel to protect against CSRF attacks--}}
                @csrf

                <div>
                    {{--Email input field container--}}
                    {{--Text label for the email input--}}
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    
                    {{--Wrapper to position the icon inside the input field--}}
                    <div class="relative">
                        {{--Position the icon inside the input on the left side--}}
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            {{--Email icon (envelope)--}}
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>

                        {{--This input is for the user's email address--}}
                        {{--"name" is the label used when sending data to the server. 
                            On the server we read it as $request->email--}}
                        {{--"type=email" tells the browser this should be an email--}}
                        {{--"required" means the browser will not let the form submit if this box is empty--}}
                        <input                       
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @enderror"
                            placeholder="Enter your email"
                            value="{{ old('email') }}"
                        >
                        {{--"placeholder" is the light grey text shown before the user types--}}
                        {{--value="{{ old('email') }}" keeps what the user typed if the form has an error and reloads--}}                     
                    </div>

                    {{--If there is a validation error for "email", this block will show the error message--}}
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                     {{--Password input field container--}}
                     {{--Text label for the password input--}}
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>

                    {{--Wrapper to position the icon inside the input field--}}
                    <div class="relative">
                        {{--Position the lock icon inside the input on the left side--}}
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            {{--Lock icon for the password field--}}
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>

                        {{--This input is for the user's password--}}
                        {{--name="password" is how the server will read this field: $request->password--}}
                        {{--type="password" hides what the user types (shows dots instead of letters)--}}
                        {{--autocomplete="current-password" lets the browser suggest saved passwords--}}
                        {{--required means the browser will not let the form submit if this box is empty--}}
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @enderror"
                            placeholder="Enter your password"
                        >
                        {{--"placeholder" shows light grey helper text before the user types--}}
                    </div>

                    {{--If there is a validation error for "password", this block will show the error message--}}
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    {{--Submit button container--}}
                    {{--When clicked, this button will submit the form--}}
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium cursor-pointer rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    >
                        {{--Area inside the button to place the icon on the left--}}
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            {{--Sign-in icon that slightly changes color on hover--}}
                            <i class="fas fa-sign-in-alt text-indigo-500 group-hover:text-indigo-400"></i>
                        </span>
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-4 text-right">
                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>
                        
            {{--Section below the form for the "New to EventSquare?" message and registration link--}}
            <div class="mt-6">
                {{--Used to place the text on top of a line--}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        {{--Horizontal line across the card--}}
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    {{--Center the small text on top of the line--}}
                    <div class="relative flex justify-center text-sm">
                        {{--Text asking if the user is new--}}
                        <span class="px-2 bg-white text-gray-500">New to EventSquare?</span>
                    </div>
                </div>

                {{--Container for the registration link, centered--}}
                <div class="mt-6 text-center">
                    {{--Link to the "register" route where users can create a new account--}}
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                        Create an account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection