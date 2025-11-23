{{-- 
    This is the main layout for the site.
    All pages (login, register, events etc.) will "extend" this layout.
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
        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        @stack('scripts')
    </head>
 
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
                <nav class="hidden md:flex items-center space-x-8">

                </nav>


                {{--USER MENU (right side)--}}
                <div class="flex items-center space-x-4">

                    {{-- @guest means: only show this block when the user is NOT logged in.
                        So these are the "Sign In" and "Sign Up" links for visitors.--}}
                    @guest
                    {{-- Link to the login page --}}
                    <a href="{{ url('/login') }}" class="px-4 py-2 rounded-full text-sm font-medium text-slate-800 hover:text-[#4f46e5] transition-colors">
                        Sign In
                    </a>

                    {{-- Link to the registration page --}}
                    <a href="{{ url('/register') }}" class="inline-flex items-center justify-center rounded-full bg-[#4f46e5] px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#4338ca] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4f46e5] focus-visible:ring-offset-2 transition">
                        Sign Up
                    </a>

                    {{-- @else is the opposite of @guest (i.e., the user IS logged in).
                        So this block shows the welcome text, optional admin link and logout button.--}}
                    @else
                    <div class="flex items-center gap-4 text-sm text-gray-700">
                        @php
                            // Lightweight pending invitations count (prototype; consider ViewComposer for production)
                            $pendingInvCount = \App\Models\Invitation::where('id_invitee', Auth::id())
                                ->where('status','pending')
                                ->count();
                        @endphp
                        <span class="flex items-center gap-2">
                            Welcome,&nbsp;
                            <a
                                href="{{ route('profile.show') }}"
                                class="font-semibold hover:underline"
                                title="View profile"
                            >
                                {{ Auth::user()->name }}
                            </a>
                            @can('admin')
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-full bg-white border border-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition">
                                <i class="fas fa-shield-alt mr-1 text-[11px]"></i>
                                <span>Admin</span>
                            </a>
                            @endcan
                        </span>

                        <a href="{{ route('events.mine') }}" class="inline-flex items-center rounded-full bg-white border border-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition">
                            <i class="fas fa-calendar-alt mr-1 text-[11px]"></i>
                            <span>My events</span>
                        </a>
                        <a href="{{ url('/events/create') }}" class="inline-flex items-center rounded-full bg-white border border-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition">
                            <i class="fas fa-plus mr-1 text-[11px]"></i>
                            <span>Create event</span>
                        </a>
                        <a href="{{ route('invitations.index') }}" class="inline-flex items-center rounded-full bg-white border border-indigo-100 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition">
                            <i class="fas fa-envelope-open-text mr-1 text-[12px] text-indigo-600"></i>
                            <span class="flex items-center gap-1">Invites
                                @if($pendingInvCount > 0)
                                    <span class="text-slate-700 font-semibold text-[10px] leading-none">({{ $pendingInvCount }})</span>
                                @endif
                            </span>
                        </a>

                        <form action="{{ url('/logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-full bg-white border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition">
                                <i class="fas fa-sign-out-alt mr-1 text-[11px]"></i>
                                <span>Logout</span>
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
