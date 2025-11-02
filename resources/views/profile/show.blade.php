<x-app-layout pageTitle="{{ $user->name }}'s Profile">
    <div x-data="{ isMusicShareModalOpen: false }">
        <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
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

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            {{ $user->name }}
                        </h2>

                        <div class="flex items-center space-x-4">
                            <a href="{{ route('profile.followers', $user) }}" class="text-blue-500 hover:underline">Followers ({{ $user->followers()->count() }})</a>
                            <a href="{{ route('profile.following', $user) }}" class="text-blue-500 hover:underline">Following ({{ $user->following()->count() }})</a>
                            @if (auth()->id() === $user->id)
                                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Edit Profile</a>
                            @endif
                        </div>
                    </div>
                </div>

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
                    <div class="mt-4">
                        <button @click="isMusicShareModalOpen = true" class="bg-custom-mid-blue hover:bg-custom-dark-blue p-3 rounded-full shadow-lg transition w-full flex items-center justify-center">
                            <img src="{{ asset('icons/share.png') }}" alt="Share Music" class="w-8 h-8 mr-2">
                            <span class="text-white font-semibold">Share Music</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <x-music-share-modal />
    </div>
</x-app-layout>

