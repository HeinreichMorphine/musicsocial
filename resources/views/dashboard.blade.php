<x-app-layout pageTitle="Home Feed">

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-7">

                    <x-post-composer />

                    <div id="feed-container" class="space-y-6">
                        @foreach ($shares as $share)
                            <x-share-card :share="$share" />
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $shares->links() }}
                    </div>

                </div>
                <div class="hidden md:block col-span-3">
                    <div class="sticky top-0 pt-4">
                            <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                            <x-sidebar-right :recommendedSongs="$recommendedSongs" />

                        </div>
                    </div>
                </div>
                </div>
        </div>

        <x-music-share-modal />

    </div>
</x-app-layout>