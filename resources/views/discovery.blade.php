<x-app-layout pageTitle="Discovery">
    <div x-data="{ isMusicShareModalOpen: false, activeTab: 'songs' }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <!-- Left Navigation -->
                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-24 pt-4">
                        <div
                            class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                    <div class="p-4 md:p-6 text-gray-900 dark:text-gray-100">
                        <!-- Mobile Tabs (Hidden on Desktop) -->
                        <div class="block lg:hidden border-b border-gray-100 dark:border-gray-700 mb-6 -mt-2">
                            <nav class="-mb-px flex space-x-8 px-2" aria-label="Tabs">
                                <button @click="activeTab = 'songs'"
                                    :class="activeTab === 'songs' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center focus:outline-none">
                                    Discover Songs
                                </button>

                                <button @click="activeTab = 'people'"
                                    :class="activeTab === 'people' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center focus:outline-none">
                                    Who to Follow
                                </button>
                            </nav>
                        </div>

                        <!-- Desktop Header -->
                        <div class="hidden lg:flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Discover New Songs</h3>
                            @if(auth()->user()->spotify_token && $recommendedSongs->count() > 0)
                                <form action="{{ route('export-playlist') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="source" value="discovery">
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold shadow text-sm transition-colors flex items-center space-x-2">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.54.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                        <span>Sync to Spotify</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Songs Content -->
                        <div :class="activeTab === 'songs' ? 'block' : 'hidden lg:block'">
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                                @if($recommendedSongs->isEmpty())
                                    <p class="text-center text-gray-500 dark:text-gray-400">No recommendations available at
                                        the moment.</p>
                                @else
                                    @foreach ($recommendedSongs as $song)
                                        <x-discovery-card :song="$song" />
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Who to Follow Content (Mobile Only) -->
                        <div :class="activeTab === 'people' ? 'block lg:hidden' : 'hidden'" x-cloak>
                            <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>
            </div>
            <x-music-share-modal />
        </div>
</x-app-layout>