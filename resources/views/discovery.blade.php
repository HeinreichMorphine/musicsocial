<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <!-- Left Navigation -->
            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    @include('layouts.navigation-social')
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-span-12 md:col-span-7">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Suggested for you</h3>

                    @forelse ($recommendedShares as $share)
                        <x-discovery-card :share="$share" />
                    @empty
                        <p>No recommendations for you right now. Share and like more music to get suggestions!</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Sidebar -->
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
</x-app-layout>
