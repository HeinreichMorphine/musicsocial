<x-app-layout pageTitle="Home Feed">

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-24 pt-4">
                        <div class="bg-white/60 dark:bg-black border border-white/40 dark:border-white/5 rounded-3xl p-6 shadow-2xl flex flex-col gap-4">
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

                    @if($feedType === 'explore' && $usersToSuggest->isNotEmpty())
                        <div class="block lg:hidden mb-8">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Who to Follow</h3>
                            </div>
                            <div class="flex overflow-x-auto space-x-4 pb-2 -mx-4 px-4 custom-scrollbar">
                                @foreach($usersToSuggest->take(8) as $suggestedUser)
                                    <div class="flex-none w-40 bg-white/60 dark:bg-black backdrop-blur-lg rounded-2xl p-4 border border-gray-200/50 dark:border-white/10 shadow-sm text-center flex flex-col items-center" x-data="{ followed: {{ auth()->user()->following->contains($suggestedUser) ? 'true' : 'false' }} }" x-show="!followed" x-transition>
                                        <a href="{{ route('profile.show', $suggestedUser->name) }}" class="block mb-2">
                                            <x-user-avatar :user="$suggestedUser" class="w-16 h-16 shadow-sm border border-gray-100 dark:border-gray-700" />
                                        </a>
                                        <div class="min-w-0 w-full mb-3">
                                            <a href="{{ route('profile.show', $suggestedUser->name) }}" class="block font-bold text-gray-800 dark:text-gray-100 hover:underline truncate text-sm">{{ $suggestedUser->name }}</a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@ {{ $suggestedUser->username }}</p>
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
                                            .then(data => { followed = data.followed; })
                                            .catch(error => console.error('Error:', error));
                                        " class="mt-auto w-full">
                                            <button type="submit" 
                                                    x-text="followed ? 'Unfollow' : 'Follow'" 
                                                    :class="followed ? 'bg-red-500 hover:bg-red-600 shadow-red-500/30' : 'bg-blue-600 hover:bg-blue-500 shadow-blue-500/30'" 
                                                    class="w-full text-white text-xs font-bold py-1.5 px-3 rounded-full transition duration-300 shadow-lg hover:shadow-xl">
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

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
