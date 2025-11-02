<x-app-layout pageTitle="Discovery">
    <div x-data="{ isMusicShareModalOpen: false }">
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @if($recommendedShares->isEmpty())
                <p class="text-center text-gray-500">No recommendations available at the moment.</p>
            @else
                @foreach ($recommendedShares as $share)
                    <x-discovery-card :share="$share" />
                @endforeach
            @endif
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <div class="p-6 text-gray-900 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Who to Follow</h3>

                        @forelse ($usersToSuggest as $suggestedUser)
                            <div class="flex items-center justify-between mb-4 last:mb-0" x-data="{ followed: {{ auth()->user()->following->contains($suggestedUser) ? 'true' : 'false' }}, followersCount: {{ $suggestedUser->followers()->count() }} }">
                                <div class="flex items-center">
                                    <img class="w-10 h-10 rounded-full mr-3" src="{{ $suggestedUser->profile_picture_url ?: asset('images/default-profile.png') }}" alt="{{ $suggestedUser->name }}">
                                    <div>
                                        <a href="{{ route('profile.show', $suggestedUser->name) }}" class="font-semibold text-gray-800 hover:underline">{{ $suggestedUser->name }}</a>
                                        <p class="text-sm text-gray-500">{{ ' @' . $suggestedUser->username }}</p>
                                    </div>
                                </div>
                                <form @submit.prevent="
                                    fetch('{{ route('users.follow', $suggestedUser) }}', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        followed = data.followed;
                                        followersCount = data.followersCount;
                                    })
                                    .catch(error => console.error('Error:', error));
                                ">
                                    <button type="submit" x-text="followed ? 'Unfollow' : 'Follow'" :class="followed ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'" class="text-white text-sm font-bold py-1 px-3 rounded-full transition duration-150">
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p>No new users to suggest right now.</p>
                        @endforelse
                    </div>
                    <x-sidebar-right :recommendedShares="$recommendedShares" />
                </div>
            </div>
        </div>
        <x-music-share-modal />
    </div>
</x-app-layout>
