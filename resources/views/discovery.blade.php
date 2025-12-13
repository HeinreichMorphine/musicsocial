<x-app-layout pageTitle="Discovery">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <!-- Left Navigation -->
            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                     <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-span-12 md:col-span-7">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Discover New Songs</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if($recommendedSongs->isEmpty())
                <p class="text-center text-gray-500">No recommendations available at the moment.</p>
            @else
                @foreach ($recommendedSongs as $song)
                    <x-discovery-card :song="$song" />
                @endforeach
            @endif
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                    <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                </div>
            </div>
        </div>
        <x-music-share-modal />
    </div>
</x-app-layout>
