{{-- 
    All pages (login, register, etc.) will "extend" this layout.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    {{--Basic HTML configuration--}}

    {{--Sets the character encoding so special characters work correctly--}}
    <meta charset="utf-8">

    {{--Makes the page responsive on phones and tablets--}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{--CSRF token used by Laravel to protect POST forms from attacks--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--<title> shown in the browser tab.
        @yield('title', 'EventSquare') means:
        - If a child view defines @section('title', 'Something'), use that.
        - Otherwise, use "EventSquare" as the default title.--}}
    <title>@yield('title', config('app.name', 'EventSquare'))</title>

    {{--Load Font Awesome from a CDN so we can use icon classes like <i class="fas fa-user">--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Load our compiled CSS and JS using Vite.
        These files live under resources/css and resources/js and are bundled by Vite.--}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--@stack('styles') lets child views push extra CSS tags 
        using @push('styles') ... @endpush --}}
    @stack('styles')
</head>

{{-- Tailwind class "font-sans" sets a clean font,
    "antialiased" makes text smoother.--}}
<body class="font-sans antialiased">

    {{--HEADER (top bar)--}}
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        {{--Center the header content and limit its width.
            max-w-7xl = maximum width,
            mx-auto = center horizontally,
            px-* = horizontal padding on different screen sizes.--}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{--Flex container for logo on the left and navigation/user menu on the right.
                h-16 = fixed header height (64px).--}}
            <div class="flex justify-between items-center h-16">

                {{--LOGO AREA--}}
                {{-- Clicking the logo sends the user to the homepage ("/").
                    flex items-center = icon and text on the same row.--}}
                <a href="{{ url('/') }}" class="flex items-center space-x-3 text-xl font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    {{--Calendar icon from Font Awesome--}}
                    <i class="fas fa-calendar-alt text-2xl"></i>
                    {{--Brand name--}}
                    <span>EventSquare</span>
                </a>

                {{--MAIN NAVIGATION (center)--}}
                {{--Hidden on small screens (hidden),
                    shown from medium screens up (md:flex).--}}
                <!--<nav class="hidden md:flex items-center space-x-8">
                    {{--@auth means: only show this block when the user is logged in.
                        If not logged in, this part is skipped.--}}
                    @auth
                    {{-- Link to the "Create Event" page.
                        Right now, this uses a plain URL (/events/create).
                        You can later change it to route('events.create') once you have that route.--}}
                    <a href="{{ url('/events/create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Event
                    </a>
                    @endauth
                </nav>-->

                {{--USER MENU (right side)--}}
                <div class="flex items-center space-x-4">

                    {{-- @guest means: only show this block when the user is NOT logged in.
                        So these are the "Sign In" and "Sign Up" links for visitors.--}}
                    @guest
                    {{-- Link to the login page --}}
                    <a href="{{ url('/login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Sign In
                    </a>

                    {{-- Link to the registration page --}}
                    <a href="{{ url('/register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        Sign Up
                    </a>

                    {{-- @else is the opposite of @guest (i.e., the user IS logged in).
                        So this block shows the welcome text and logout button.--}}
                    @else
                    <div class="flex items-center space-x-3">
                        {{-- Show the logged-in user’s name --}}
                        <span class="text-gray-700 text-sm">Welcome, {{ Auth::user()->name }}</span>

                        {{--Logout form.
                            We use a <form> with POST because Laravel's logout route 
                            is usually defined as POST and needs a CSRF token.--}}
                        <form action="{{ url('/logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition-colors cursor-pointer">
                                <i class="fas fa-sign-out-alt mr-1"></i>Logout
                            </button>
                        </form>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    {{--MAIN CONTENT AREA--}}
    <main>
        {{--Flash message: success.
            In controllers you can set: return back()->with('success', 'Message here');
            If such a message exists in the session, show this green box.--}}
        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{--Flash message: error.
            Similar to "success" but styled in red.--}}
        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{--Form validation errors.
            $errors is provided by Laravel when validation fails.
            $errors->any() checks if there is at least one error.--}}
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    {{-- Show how many errors we have --}}
                    <h3 class="text-sm font-medium text-red-800">
                        There were {{ count($errors->all()) }} error(s) with your submission:
                    </h3>

                    {{-- List each error message --}}
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- @yield('content') is where each child view will inject its own page content.
            For example, the login page defines @section('content') ... @endsection,
            and that content will appear here.--}}
        @yield('content')
    </main>

    {{--@stack('scripts') works like @stack('styles').
        Child views can push extra <script> tags using @push('scripts').--}}
    @stack('scripts')
</body>
</html>
