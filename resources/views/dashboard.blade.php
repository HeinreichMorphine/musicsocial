<x-app-layout pageTitle="Home Feed">

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-24 pt-4">
                        <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl transition-colors duration-300">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                    <!-- Post Composer -->
                    <div class="mb-8">
                        <x-post-composer />
                    </div>

                    <div class="flex space-x-6 mb-6 border-b border-gray-200 dark:border-gray-800">
                        <a href="{{ route('dashboard', ['feed' => 'following']) }}"
                           class="pb-3 text-lg font-bold transition-colors border-b-2 {{ $feedType === 'following' ? 'text-gray-900 dark:text-white border-custom-mid-blue' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300' }}">
                            Following
                        </a>
                        <a href="{{ route('dashboard', ['feed' => 'explore']) }}"
                           class="pb-3 text-lg font-bold transition-colors border-b-2 {{ $feedType === 'explore' ? 'text-gray-900 dark:text-white border-custom-mid-blue' : 'text-gray-400 dark:text-gray-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300' }}">
                            Explore
                        </a>
                    </div>

                    <div id="feed-container" class="space-y-6">
                        @forelse ($shares as $share)
                            <x-share-card :share="$share" />
                        @empty
                            <div class="text-center py-10">
                                <p class="text-gray-500 text-lg">
                                    {{ $feedType === 'following' ? 'No posts from people you follow yet.' : 'No posts found.' }}
                                </p>
                                @if($feedType === 'following')
                                    <p class="text-gray-400 text-sm mt-2">Try the <a href="{{ route('dashboard', ['feed' => 'explore']) }}" class="text-custom-mid-blue dark:text-blue-400 font-bold hover:underline hover:text-blue-600 dark:hover:text-blue-300 transition-colors">Explore</a> feed to find new music!</p>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        {{ $shares->links() }}
                    </div>

                </div>
                <div class="hidden lg:block lg:col-span-3 xl:col-span-3">
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