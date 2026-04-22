<x-app-layout pageTitle="Saved Posts">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-0 pt-4">
                         <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl transition-colors duration-300">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>
    
                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                    @include('profile.partials.header', ['user' => $user])
    
                    <div class="flex justify-between items-center mt-6 mb-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Bookmarks</h2>
                        @if(auth()->user()->spotify_token && $shares->count() > 0)
                            <form action="{{ route('export-playlist') }}" method="POST">
                                @csrf
                                <input type="hidden" name="source" value="saved">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold shadow text-sm transition-colors flex items-center space-x-2">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.54.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                    <span>Sync to Spotify</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="space-y-6">
                        @forelse ($shares as $share)
                            <x-share-card :share="$share" />
                        @empty
                            <div class="bg-white dark:bg-black rounded-3xl shadow-sm border border-gray-100 dark:border-white/10 p-8 text-center text-gray-500 dark:text-gray-400">
                                <!-- Ribbon Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0111.186 0z" />
                                </svg>
                                <p class="text-lg text-gray-900 dark:text-white">You haven't saved any posts yet.</p>
                                <a href="{{ route('dashboard') }}" class="text-custom-mid-blue dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors hover:underline mt-2 inline-block">Explore the feed</a>
                            </div>
                        @endforelse
    
                        <div class="mt-6">
                            {{ $shares->links() }}
                        </div>
                    </div>
                </div>
    
                <div class="hidden lg:block lg:col-span-3">
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
