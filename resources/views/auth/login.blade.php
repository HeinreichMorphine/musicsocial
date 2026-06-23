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

        <div class="space-y-3">
             <a href="{{ route('social.redirect', 'spotify') }}" class="w-full flex justify-center items-center gap-2.5 py-3.5 px-4 border border-slate-200 rounded-2xl shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-custom-mid-blue/10 transition-all active:scale-[0.99]">
                <svg class="h-5 w-5 text-[#1DB954] shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                <span>Continue with Spotify</span>
            </a>
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
