<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Connect Social Accounts') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Connect your Spotify and Google accounts to enable music features and import playlists.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <!-- Spotify Connection -->
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Spotify</h3>
                    @if(Auth::user()->spotify_id)
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">Connected</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Not connected</p>
                    @endif
                </div>
            </div>

            <div>
                 @if(Auth::user()->spotify_id)
                    <button disabled class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-md text-sm font-medium cursor-not-allowed">
                        Connected
                    </button>
                    {{-- Optional: Add Disconnect Logic Here --}}
                @else
                    <a href="{{ route('social.redirect', 'spotify') }}" class="inline-flex items-center px-4 py-2 bg-[#1DB954] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1ed760] focus:bg-[#1ed760] active:bg-[#1aa34a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1DB954] transition ease-in-out duration-150">
                        Connect Spotify
                    </a>
                @endif
            </div>
        </div>

        <!-- Google Connection -->
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Google / YouTube</h3>
                    @if(Auth::user()->google_id)
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium">Connected</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Not connected</p>
                    @endif
                </div>
            </div>

             <div>
                 @if(Auth::user()->google_id)
                    <button disabled class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-md text-sm font-medium cursor-not-allowed">
                        Connected
                    </button>
                @else
                    <a href="{{ route('social.redirect', 'google') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Connect Google
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
