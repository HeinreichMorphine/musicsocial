<x-app-layout pageTitle="{{ $user->name }}'s Profile">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                     <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                @include('profile.partials.header', ['user' => $user])

                <div class="space-y-6">
                    @forelse ($user->shares as $share)
                        <x-share-card :share="$share" />
                    @empty
                        <p class="text-gray-600 dark:text-gray-400">{{ $user->name }} has not shared anything yet.</p>
                    @endforelse
                </div>
            </div>

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

