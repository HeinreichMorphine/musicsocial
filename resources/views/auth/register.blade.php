<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Create Account</h2>
        <p class="text-sm text-gray-500 mt-2">Join us and start sharing your music taste</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="space-y-3">
             <a href="{{ route('social.redirect', 'spotify') }}" class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="h-5 w-5 mr-3 text-[#1DB954]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                Sign up with Spotify
            </a>
            <a href="{{ route('social.redirect', 'google') }}" class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                 <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                </svg>
                Sign up with Google
            </a>
        </div>

        <div class="relative flex items-center justify-center text-sm text-center">
            <span class="bg-[#F3F4F6] px-2 text-gray-500">Or sign up with email</span>
            <div class="absolute inset-0 border-t border-gray-300 z-[-1]"></div>
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="sr-only" />
            <input id="name" class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" 
                   type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Full Name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="sr-only" />
            <input id="email" class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" 
                   type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Email Address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Profile Picture -->
        <div>
            <x-input-label for="profile_picture" :value="__('Profile Picture')" class="block text-sm font-medium text-gray-700 mb-1" />
            <input id="profile_picture" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                   type="file" name="profile_picture" />
            <x-input-error :messages="$errors->get('profile_picture')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="sr-only" />
            <input id="password" class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
             <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="sr-only" />
            <input id="password_confirmation" class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors">
                {{ __('Register') }}
            </button>
        </div>

        <div class="text-center mt-4">
             <p class="text-sm text-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('Log in') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
