@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="bg-white">
    {{-- Hero Section --}}
    <div class="relative isolate overflow-hidden bg-gradient-to-b from-indigo-100/20">
        <div class="mx-auto max-w-7xl pb-24 pt-10 sm:pb-32 lg:grid lg:grid-cols-2 lg:gap-x-8 lg:px-8 lg:py-40">
            <div class="px-6 lg:px-0 lg:pt-4">
                <div class="mx-auto max-w-2xl">
                    <div class="max-w-lg">
                        <div class="mt-24 sm:mt-32 lg:mt-16">
                            <a href="#" class="inline-flex space-x-6">
                                <span class="rounded-full bg-indigo-600/10 px-3 py-1 text-sm font-semibold leading-6 text-indigo-600 ring-1 ring-inset ring-indigo-600/10">What's new</span>
                                <span class="inline-flex items-center space-x-2 text-sm font-medium leading-6 text-gray-600">
                                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                                </span>
                            </a>
                        </div>
                        <h1 class="mt-10 text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                            Events made simple.
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-600">
                            EventSquare is the ultimate platform for discovering, organizing, and joining events in your community. Whether it's a small workshop or a large conference, we've got you covered.
                        </p>
                        <div class="mt-10 flex items-center gap-x-6">
                            <a href="{{ route('events.index') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Browse Events</a>
                            <a href="{{ route('register') }}" class="text-sm font-semibold leading-6 text-gray-900">Get started <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Removed Testimonial Card --}}
        </div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-white sm:h-32"></div>
    </div>

    {{-- Features Section --}}
    <div class="mx-auto max-w-7xl px-6 lg:px-8 py-24 sm:py-32">
        <div class="mx-auto max-w-2xl lg:text-center">
            <h2 class="text-base font-semibold leading-7 text-indigo-600">Everything you need</h2>
            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">No more spreadsheet chaos</p>
            <p class="mt-6 text-lg leading-8 text-gray-600">
                Manage your events with professional tools designed for efficiency and ease of use.
            </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                
                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        Community Focused
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Connect with people who share your interests. Join public events or create private gatherings for your circle.</dd>
                </div>

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                            <i class="fas fa-lock text-white"></i>
                        </div>
                        Secure & Private
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Control who sees your events. We offer robust privacy settings for organizers and participants alike.</dd>
                </div>

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                            <i class="fas fa-bolt text-white"></i>
                        </div>
                        Real-time Updates
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Get instant notifications for invitations, changes, and announcements. Never miss a beat.</dd>
                </div>

                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                            <i class="fas fa-search text-white"></i>
                        </div>
                        Advanced Search
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Find exactly what you're looking for with our powerful search and filtering capabilities.</dd>
                </div>

            </dl>
        </div>
    </div>

    {{-- Contact Section --}}
    <div id="help-center" class="bg-gray-900 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Contact Us</h2>
                <p class="mt-6 text-lg leading-8 text-gray-300">
                    Have questions or feedback? We'd love to hear from you. Reach out to our team.
                </p>
            </div>
            <div class="mx-auto mt-10 grid max-w-2xl grid-cols-1 gap-8 text-base leading-7 sm:grid-cols-2 sm:gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                <div>
                    <h3 class="border-l-2 border-indigo-500 pl-6 font-semibold text-white">Address</h3>
                    <address class="border-l-2 border-gray-800 pl-6 pt-2 not-italic text-gray-300">
                        <p>Rua Dr. Roberto Frias</p>
                        <p>4200-465 Porto, Portugal</p>
                    </address>
                </div>
                <div>
                    <h3 class="border-l-2 border-indigo-500 pl-6 font-semibold text-white">Email</h3>
                    <div class="border-l-2 border-gray-800 pl-6 pt-2 text-gray-300">
                        <p><a href="mailto:support@eventsquare.pt" class="hover:text-white">support@eventsquare.pt</a></p>
                        <p><a href="mailto:info@eventsquare.pt" class="hover:text-white">info@eventsquare.pt</a></p>
                    </div>
                </div>
                <div>
                    <h3 class="border-l-2 border-indigo-500 pl-6 font-semibold text-white">Social</h3>
                    <div class="border-l-2 border-gray-800 pl-6 pt-2 text-gray-300 flex gap-4">
                        <a href="#" class="hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-github text-xl"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="border-l-2 border-indigo-500 pl-6 font-semibold text-white">Legal</h3>
                    <div class="border-l-2 border-gray-800 pl-6 pt-2 text-gray-300">
                        <p><a href="{{ route('privacy-policy') }}" class="hover:text-white">Privacy Policy</a></p>
                        <p><a href="{{ route('terms-of-service') }}" class="hover:text-white">Terms of Service</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection