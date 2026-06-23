<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
        <p class="text-sm text-gray-500 mt-2">Join us and start sharing your music taste</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-6" novalidate>
        @csrf

        <div class="space-y-3" x-data="{ showSpotifyModal: false }">
             <button type="button" @click="showSpotifyModal = true" class="w-full flex justify-center items-center gap-2.5 py-3.5 px-4 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-custom-mid-blue/10 transition-all active:scale-[0.99]">
                <svg class="h-5 w-5 text-[#1DB954] shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                <span>Sign up with Spotify</span>
            </button>

            <!-- Spotify Limitation Modal -->
            <div x-show="showSpotifyModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showSpotifyModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="showSpotifyModal = false" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showSpotifyModal" x-transition class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-12 sm:w-12">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Spotify Integration Limited</h3>
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-600">
                                            Due to Spotify's recent B2B API restrictions, native Spotify login is currently in <strong>Closed Beta</strong> and restricted to whitelisted development accounts.
                                        </p>
                                        <p class="text-sm text-gray-600 mt-2">
                                            If you are not an approved beta tester, your login will be rejected by Spotify. We recommend using <strong>Google</strong> or <strong>Email</strong> to register.
                                        </p>
                                        <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-100">
                                            <p class="text-sm text-blue-800">
                                                Want to link your Spotify? Email <a href="mailto:adamakib507@gmail.com" class="font-bold underline hover:text-blue-900">adamakib507@gmail.com</a> to request whitelist access!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100">
                            <a href="{{ route('social.redirect', 'spotify') }}" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-[#1DB954] text-sm font-bold text-white hover:bg-[#1ed760] focus:outline-none focus:ring-4 focus:ring-[#1DB954]/30 sm:w-auto transition-all active:scale-95">
                                Proceed Anyway
                            </a>
                            <button type="button" @click="showSpotifyModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 sm:mt-0 sm:w-auto transition-all active:scale-95">
                                Use Email / Google
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('social.redirect', 'google') }}" class="w-full flex justify-center items-center gap-2.5 py-3.5 px-4 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-custom-mid-blue/10 transition-all active:scale-[0.99]">
                 <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                </svg>
                <span>Sign up with Google</span>
            </a>
        </div>

        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-xs font-bold text-custom-slate-blue uppercase tracking-wider">Or sign up with email</span>
            <div class="flex-1 h-px bg-slate-200"></div>
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="sr-only" />
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                     <svg class="h-5 w-5 text-slate-400 group-focus-within:text-custom-mid-blue transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                     </svg>
                </div>
                <input id="name" class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:border-custom-mid-blue focus:ring-4 focus:ring-custom-mid-blue/10 focus:outline-none transition-all sm:text-sm" 
                       type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="User Name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
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
                       type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Email Address" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Profile Picture -->
        <div>
            <x-input-label for="profile_picture" :value="__('Profile Picture')" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1" />
            <input id="profile_picture" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border file:border-slate-200 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 transition-all cursor-pointer" 
                   type="file" name="profile_picture" />
            <x-input-error :messages="$errors->get('profile_picture')" class="mt-2" />
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
                                required autocomplete="new-password" placeholder="Password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
             <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="sr-only" />
             <div class="relative group">
                 <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-slate-400 group-focus-within:text-custom-mid-blue transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                 </div>
                 <input id="password_confirmation" class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:border-custom-mid-blue focus:ring-4 focus:ring-custom-mid-blue/10 focus:outline-none transition-all sm:text-sm"
                                 type="password"
                                 name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password" />
             </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-2xl shadow-sm text-sm font-bold text-white bg-black hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-950/10 active:scale-[0.99] transition-all">
                {{ __('Register') }}
            </button>
        </div>

        <div class="text-center mt-4">
             <p class="text-sm text-slate-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-custom-mid-blue hover:text-custom-dark-blue transition-colors">
                    {{ __('Log in') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
