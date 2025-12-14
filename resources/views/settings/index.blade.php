<x-app-layout pageTitle="Settings">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                            @php
                                $isHome = Route::is('dashboard');
                                $isProfile = false;
                                $isSettings = true;
                                $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                                $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                            @endphp
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-7 space-y-6">
                    <!-- Session Status -->
                     @if (session('status'))
                        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                            <span class="font-medium">{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                            <span class="font-medium">Error!</span> {{ session('error') }}
                        </div>
                    @endif

                    <!-- Email Verification Section -->
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Email Verification') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Check your email verification status.") }}
                            </p>
                        </header>

                        <div class="mt-6">
                             @if (auth()->user()->hasVerifiedEmail())
                                <div class="flex items-center text-green-600 font-medium p-4 bg-green-50 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('Your email address is verified.') }}
                                </div>
                            @else
                                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                {{ __('Your email address is unverified.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Resend Verification Email') }}
                                        </button>
                                    </div>
                                </form>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-4 font-medium text-sm text-green-600 animate-pulse">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.connect-social-accounts')
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

                <div class="hidden md:block col-span-3">
                    <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>
            </div>
        </div>
        <x-music-share-modal />
    </div>
</x-app-layout>