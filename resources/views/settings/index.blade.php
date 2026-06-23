<x-app-layout pageTitle="Settings">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-24 pt-4">
                        <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl">
                            @php
                                $isHome = Route::is('dashboard');
                                $isProfile = false;
                                $isSettings = true;
                                $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition duration-150';
                                $activeClasses = ' !text-gray-900 dark:!text-white !font-bold bg-gray-100 dark:bg-white/10';
                            @endphp
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-6">
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
                    <div class="p-4 sm:p-8 bg-white dark:bg-black shadow sm:rounded-lg border border-gray-200 dark:border-white/10">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Email Verification') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Check your email verification status.") }}
                            </p>
                        </header>

                        <div class="mt-6">
                             @if (auth()->user()->hasVerifiedEmail())
                                <div class="flex items-center text-green-600 dark:text-green-400 font-medium p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('Your email address is verified.') }}
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border-l-4 border-yellow-400 dark:border-yellow-500 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                                {{ __('Your email address is unverified.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                            {{ __('Resend Verification Email') }}
                                        </button>
                                    </div>
                                </form>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-4 font-medium text-sm text-green-600 dark:text-green-400 animate-pulse">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Appearance Section (Mobile Only) -->
                    <div class="sm:hidden p-4 bg-white dark:bg-black shadow rounded-lg border border-gray-200 dark:border-white/10">
                        <header class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Appearance') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __("Toggle Dark Mode") }}
                                </p>
                            </div>
                            <div x-data="{
                                darkMode: localStorage.getItem('theme') ? localStorage.getItem('theme') === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches,
                                toggleDarkMode() {
                                    this.darkMode = !this.darkMode;
                                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                                    document.documentElement.classList.toggle('dark', this.darkMode);
                                }
                            }">
                                <button @click="toggleDarkMode()" class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white focus:outline-none transition-colors duration-200 shadow-sm" title="Toggle Dark Mode">
                                    <!-- Sun Icon (show when dark) -->
                                    <svg x-show="darkMode" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <!-- Moon Icon (show when light) -->
                                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 dark:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                </button>
                            </div>
                        </header>
                    </div>

                    <!-- Log Out Section (Mobile Only) -->
                    <div class="sm:hidden p-4 bg-white dark:bg-black shadow rounded-lg border border-gray-200 dark:border-white/10">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Session') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __("Log out of your account") }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 font-bold rounded-lg transition duration-150 ease-in-out shadow-sm text-sm uppercase tracking-wider">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-black shadow sm:rounded-lg border border-gray-200 dark:border-white/10">
                        @include('profile.partials.connect-social-accounts')
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-black shadow sm:rounded-lg border border-gray-200 dark:border-white/10">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-black shadow sm:rounded-lg border border-gray-200 dark:border-white/10">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-black shadow sm:rounded-lg border border-gray-200 dark:border-white/10">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-24 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>
            </div>
        </div>
        <x-music-share-modal />
    </div>
</x-app-layout>