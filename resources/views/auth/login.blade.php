<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
        <p class="text-sm text-gray-500 mt-2">Enter your credentials to access your account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6" novalidate>
        @csrf

        <div class="space-y-3" x-data="{ showSpotifyModal: false }">

            <!-- Spotify Button -->
            <button type="button" @click="showSpotifyModal = true"
                class="w-full flex justify-center items-center gap-2.5 py-3.5 px-4 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-custom-mid-blue/10 transition-all active:scale-[0.99]">
                <svg class="h-5 w-5 text-[#1DB954] shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                <span>Continue with Spotify</span>
            </button>

            <!-- ============================================================ -->
            <!-- SPOTIFY CLOSED BETA MODAL                                    -->
            <!-- ============================================================ -->
            <div x-show="showSpotifyModal" style="display: none;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog" aria-modal="true" aria-labelledby="spotify-modal-title">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-950/80 backdrop-blur-md" @click="showSpotifyModal = false"></div>

                <!-- Modal Card -->
                <div x-show="showSpotifyModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

                    <!-- Green accent header bar -->
                    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #1DB954, #158a3e);"></div>

                    <!-- Modal Body -->
                    <div class="p-8">
                        <!-- Icon + Title -->
                        <div class="flex items-center gap-4 mb-6">
                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #1DB954/15, #e8fdf0); background-color: #e8fdf0;">
                                <svg class="w-7 h-7" fill="#1DB954" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-[#1DB954] mb-0.5">Closed Beta</p>
                                <h3 class="text-xl font-bold text-gray-900 leading-tight" id="spotify-modal-title">Spotify Login Restricted</h3>
                            </div>
                        </div>

                        <!-- Explanation -->
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">
                            Due to Spotify's new <strong class="text-gray-800">B2B API policy</strong>, our Spotify integration is in a closed development mode. Only manually approved accounts can sign in via Spotify.
                        </p>

                        <!-- Info cards -->
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-3 p-3.5 bg-amber-50 rounded-xl border border-amber-100">
                                <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-amber-800 leading-relaxed">
                                    <strong>Not on the allowlist?</strong> Your Spotify login will be rejected with a 403 error. Please use Google or Email instead.
                                </p>
                            </div>
                            <div class="flex items-start gap-3 p-3.5 bg-blue-50 rounded-xl border border-blue-100">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-blue-800 leading-relaxed">
                                    <strong>Want access?</strong> Email <a href="mailto:adamakib507@gmail.com" class="font-bold underline decoration-blue-300 hover:text-blue-900 transition-colors">adamakib507@gmail.com</a> to request whitelist approval.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('social.redirect', 'google') }}"
                                class="w-full flex items-center justify-center gap-2.5 py-3 px-4 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-all active:scale-[0.99]">
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/></svg>
                                Continue with Google instead
                            </a>
                            <button type="button" @click="showSpotifyModal = false"
                                class="w-full py-3 px-4 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Go back
                            </button>
                            <div class="text-center pt-1 border-t border-slate-100">
                                <a href="{{ route('social.redirect', 'spotify') }}" class="text-xs text-slate-400 hover:text-slate-600 transition-colors underline decoration-dashed">
                                    Whitelisted beta tester? Proceed anyway →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Close button -->
                    <button type="button" @click="showSpotifyModal = false"
                        class="absolute top-5 right-5 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <a href="{{ route('social.redirect', 'google') }}" class="w-full flex justify-center items-center gap-2.5 py-3.5 px-4 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-custom-mid-blue/10 transition-all active:scale-[0.99]">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                </svg>
                <span>Continue with Google</span>
            </a>
        </div>

        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-xs font-bold text-custom-slate-blue uppercase tracking-wider">Or continue with email</span>
            <div class="flex-1 h-px bg-slate-200"></div>
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="sr-only" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                     <svg class="h-5 w-5 text-slate-400 group-focus-within:text-custom-mid-blue transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input id="email" class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:border-custom-mid-blue focus:ring-4 focus:ring-custom-mid-blue/10 focus:outline-none transition-all sm:text-sm"
                       type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email address" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="sr-only" />
            <div class="relative group">
                 <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-custom-mid-blue transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:border-custom-mid-blue focus:ring-4 focus:ring-custom-mid-blue/10 focus:outline-none transition-all sm:text-sm"
                                type="password"
                                name="password"
                                required autocomplete="current-password" placeholder="Password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" class="h-4.5 w-4.5 text-custom-mid-blue focus:ring-custom-mid-blue/30 border-slate-300 rounded-lg" name="remember">
                <label for="remember_me" class="ml-2 block text-sm text-slate-600 font-medium select-none cursor-pointer">
                    {{ __('Remember me') }}
                </label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-bold text-custom-mid-blue hover:text-custom-dark-blue transition-colors">
                        {{ __('Forgot password?') }}
                    </a>
                </div>
            @endif
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-2xl shadow-sm text-sm font-bold text-white bg-black hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-950/10 active:scale-[0.99] transition-all">
                {{ __('Sign in') }}
            </button>
        </div>

        <div class="text-center mt-4">
             <p class="text-sm text-slate-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-bold text-custom-mid-blue hover:text-custom-dark-blue transition-colors">
                    {{ __('Register now') }}
                </a>
             </p>
        </div>
    </form>
</x-guest-layout>
