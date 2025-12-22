@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-slate-50 py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <h1 class="text-3xl font-bold text-slate-900">Privacy Policy</h1>
            <p class="mt-3 text-sm text-slate-600">
                This policy explains how EventSquare handles your data.
            </p>

            <div class="mt-8 space-y-6 text-sm text-slate-600">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Data we collect</h2>
                    <p class="mt-2">
                        We collect account details, event participation, and basic usage analytics.
                    </p>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">How we use data</h2>
                    <p class="mt-2">
                        Data is used to operate the platform, improve features, and provide support.
                    </p>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Your choices</h2>
                    <p class="mt-2">
                        You can update your profile, manage notifications, or request account deletion.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-900 -mb-8 py-24 sm:py-32">
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
@endsection
