@props(['usersToSuggest'])

<div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-6 border border-white/40 dark:border-white/10 shadow-xl mb-6">
    <div class="text-gray-900 dark:text-white">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Who to Follow</h3>

        @forelse ($usersToSuggest as $suggestedUser)
            <div class="flex items-center justify-between mb-5 last:mb-0" x-data="{ followed: {{ auth()->user()->following->contains($suggestedUser) ? 'true' : 'false' }}, followersCount: {{ $suggestedUser->followers()->count() }} }" x-show="!followed" x-transition.duration.300ms>
                <div class="flex items-center min-w-0 flex-1 mr-2 xl:mr-3">
                    <x-user-avatar :user="$suggestedUser" class="w-10 h-10 xl:w-12 xl:h-12 mr-3 shrink-0 border border-white dark:border-gray-700 shadow-sm" />
                    <div class="min-w-0 flex-1 pr-2">
                        <a href="{{ route('profile.show', $suggestedUser->name) }}" class="block font-bold text-gray-800 dark:text-gray-100 hover:underline truncate text-sm">{{ $suggestedUser->name }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate font-medium">{{ ' @' . $suggestedUser->username }}</p>
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
                    <button type="submit" 
                            x-text="followed ? 'Unfollow' : 'Follow'" 
                            :class="followed ? 'bg-red-500 hover:bg-red-600 shadow-red-500/30' : 'bg-blue-600 hover:bg-blue-500 shadow-blue-500/30'" 
                            class="shrink-0 text-white text-xs font-bold py-1.5 px-3 xl:py-2 xl:px-5 rounded-full transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    </button>
                </form>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No suggestions right now.</p>
        @endforelse
    </div>
</div>
