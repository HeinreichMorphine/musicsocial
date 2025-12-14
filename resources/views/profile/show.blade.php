<x-app-layout pageTitle="{{ $user->name }}'s Profile">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                     <div class="bg-white/60 backdrop-blur-lg rounded-3xl p-4 border border-white/40 shadow-xl">
                        @php
                            $isHome = Route::is('dashboard');
                            $isProfile = Route::is('profile.show') && (isset($user) && $user->id === auth()->id());
                            $isSettings = false;
                            $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                            $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                        @endphp
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                @include('profile.partials.header', ['user' => $user])

                <div class="space-y-6">
                    @forelse ($user->shares as $share)
                        <x-share-card :share="$share" />
                    @empty
                        <p class="text-gray-600">{{ $user->name }} has not shared anything yet.</p>
                    @endforelse
                </div>
            </div>

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

