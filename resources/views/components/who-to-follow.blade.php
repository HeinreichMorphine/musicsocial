@props(['usersToSuggest'])

<div class="bg-white/60 backdrop-blur-lg rounded-3xl p-6 border border-white/40 shadow-xl mb-6">
    <div class="text-gray-900">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Who to Follow</h3>

        @forelse ($usersToSuggest as $suggestedUser)
            <div class="flex items-center justify-between mb-4 last:mb-0" x-data="{ followed: {{ auth()->user()->following->contains($suggestedUser) ? 'true' : 'false' }}, followersCount: {{ $suggestedUser->followers()->count() }} }" x-show="!followed" x-transition.duration.300ms>
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
</div>
