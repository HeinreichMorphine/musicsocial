<x-app-layout pageTitle="{{ $user->name }} is Following">
    <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    @php
                        $isHome = Route::is('dashboard');
                        $isProfile = false;
                        $isSettings = false;
                        $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                        $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                    @endphp
                    @include('layouts.navigation-social')
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            {{ $user->name }} is Following
                        </h2>

                        <ul class="space-y-4">
                            @foreach ($following as $followedUser)
                                <li class="flex items-center justify-between space-x-4" x-data="{ isFollowing: true }">
                                    <div class="flex items-center space-x-4">
                                        <a href="{{ route('profile.show', $followedUser->name) }}">
                                            <img src="{{ $followedUser->profile_picture ? Storage::url($followedUser->profile_picture) : 'https://via.placeholder.com/40' }}" alt="{{ $followedUser->name }}'s Avatar" class="h-10 w-10 rounded-full object-cover">
                                        </a>
                                        <a href="{{ route('profile.show', $followedUser->name) }}" class="font-semibold text-gray-800 hover:underline">
                                            {{ $followedUser->name }}
                                        </a>
                                    </div>
                                    <form x-show="isFollowing" @submit.prevent="
                                        fetch('{{ route('users.follow', $followedUser) }}', {
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
                                            if (!data.followed) {
                                                isFollowing = false;
                                            }
                                        })
                                        .catch(error => console.error('Error:', error));
                                    ">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-1 px-3 rounded-full transition duration-150">
                                            Unfollow
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        {{ $following->links() }}
                    </div>
                </div>
            </div>

            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <x-sidebar-right :recommendedShares="$recommendedShares" :usersToSuggest="$usersToSuggest" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
