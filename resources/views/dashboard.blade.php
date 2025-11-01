<x-app-layout pageTitle="Home Feed">

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        @include('layouts.navigation-social')
                    </div>
                </div>
                <div class="col-span-12 md:col-span-7">

                    <x-post-composer />

                    <div class="bg-white shadow-sm rounded-xl divide-y">
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
                        <div class="p-6 text-gray-900 mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Who to Follow</h3>

                            @forelse ($usersToSuggest as $suggestedUser)
                                <div class="flex items-center justify-between mb-4 last:mb-0">
                                    <div class="flex items-center">
                                        <img class="w-10 h-10 rounded-full mr-3" src="{{ $suggestedUser->profile_picture_url ?: asset('images/default-profile.png') }}" alt="{{ $suggestedUser->name }}">
                                        <div>
                                            <a href="{{ route('profile.show', $suggestedUser->name) }}" class="font-semibold text-gray-800 hover:underline">{{ $suggestedUser->name }}</a>
                                            <p class="text-sm text-gray-500">{{ ' @' . $suggestedUser->username }}</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('users.follow', $suggestedUser) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold py-1 px-3 rounded-full transition duration-150">
                                            Follow
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p>No new users to suggest right now.</p>
                            @endforelse
                        </div>
                        <x-sidebar-right :recommendedShares="$recommendedShares" />
                        <div class="mt-4">
                            <button @click="isMusicShareModalOpen = true" class="bg-custom-mid-blue hover:bg-custom-dark-blue p-3 rounded-full shadow-lg transition w-full flex items-center justify-center">
                                <img src="{{ asset('icons/share.png') }}" alt="Share Music" class="w-8 h-8 mr-2">
                                <span class="text-white font-semibold">Share Music</span>
                            </button>
                        </div>
                    </div>
                </div>
                </div>
        </div>

        <x-music-share-modal />

    </div>
</x-app-layout>