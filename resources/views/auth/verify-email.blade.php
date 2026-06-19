<x-guest-layout>
    <div class="flex flex-col items-center text-center">
        <!-- Simplified Icon Area -->
        <div class="mb-6">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-indigo-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">
            Verify your email
        </h2>
        
        <p class="text-gray-500 text-sm mb-8 leading-relaxed max-w-[280px]">
            {{ __('Please click the link in your email to verify your account and start discovering music.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="w-full mb-6 p-3 bg-green-50 border border-green-100 rounded-lg flex items-center space-x-2">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-xs font-medium text-green-700">
                    {{ __('New verification link sent!') }}
                </p>
            </div>
        @endif

        <div class="w-full space-y-6">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    {{ __('Resend Email') }}
                </button>
            </form>

            <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                <a href="{{ session('url.intended', route('dashboard')) }}" class="text-xs font-medium text-gray-400 hover:text-gray-600 transition-colors">
                    {{ __('Skip for now') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-gray-400 hover:text-red-500 transition-colors">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
