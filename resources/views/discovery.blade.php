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
                        <h3 class="hidden lg:block text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Discover
                            New Songs</h3>

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