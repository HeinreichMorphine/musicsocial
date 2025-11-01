@props(['share'])

<div class="p-6 flex space-x-4" x-data="{ commentsOpen: false }">
    <img src="{{ $share->user->profile_picture ? Storage::url($share->user->profile_picture) : 'https://via.placeholder.com/150' }}"
         alt="avatar"
         class="h-14 w-14 rounded-full object-cover">

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('profile.show', $share->user->name) }}" class="font-bold text-gray-900">{{ $share->user->name }}</a>
                <span class="text-gray-500 text-sm"> &middot; {{ $share->created_at->diffForHumans() }}</span>
            </div>
            @if ($share->user->is(auth()->user()))
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-10" style="display: none;">
                        <form @submit.prevent="
                            if (!confirm('Are you sure you want to delete this share?')) return;
                            fetch('{{ route('shares.destroy', $share) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            })
                            .then(response => {
                                if (response.ok) {
                                    $el.closest('.p-6.flex.space-x-4').remove();
                                }
                            })
                        ">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Delete Share
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <p class="mt-2 text-gray-800">
            {{ $share->caption }}
        </p>

        <div class="mt-3 border rounded-lg overflow-hidden hover:bg-gray-50 transition">
            <div class="flex items-center space-x-4 p-4">
                <img src="{{ $share->album_art_url }}" alt="Album Art" class="w-16 h-16 rounded shadow">
                <div class="flex-1 min-w-0">
                    <p class="text-lg font-bold text-gray-900 truncate">{{ $share->track_name }}</p>
                    <p class="text-sm text-gray-600 truncate">{{ $share->artist_name }}</p>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ $share->spotify_url }}" target="_blank" title="Listen on Spotify" class="hover:opacity-75">
                        <img src="{{ asset('icons/spotify_icon.png') }}" alt="Spotify Logo" class="w-8 h-8">
                    </a>

                    @if ($share->youtube_url)
                        <a href="{{ $share->youtube_url }}" target="_blank" title="Watch on YouTube" class="hover:opacity-75">
                            <img src="{{ asset('icons/youtube_icon.png') }}" alt="YouTube Logo" class="w-8 h-8">
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center space-x-6">
            <form @submit.prevent="
                fetch('{{ route('shares.like', $share) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    liked = data.liked;
                    likesCount = data.likesCount;
                })
            " x-data="{ liked: {{ auth()->check() && auth()->user()->likes->contains($share) ? 'true' : 'false' }}, likesCount: {{ $share->likes->count() }} }">
                @csrf
                <button type="submit" class="flex items-center text-gray-500 hover:text-custom-mid-blue">
                    <template x-if="liked">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-custom-mid-blue"><path d="M11.645 20.91a.75.75 0 0 1-1.29 0C8.343 16.63 3.75 12.55 3.75 8.25 3.75 5.399 5.399 3.75 8.25 3.75c1.74 0 3.333.92 4.25 2.336C13.417 4.67 15.01 3.75 16.75 3.75c2.851 0 4.5 1.649 4.5 4.5 0 4.3-4.593 8.38-6.605 10.369a.75.75 0 0 1-1.29-.012Z" /></svg>
                    </template>
                    <template x-if="!liked">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </template>
                    <span x-text="likesCount" class="ml-1 text-sm"></span>
                </button>
            </form>

            <button @click="commentsOpen = !commentsOpen" class="flex items-center text-gray-500 hover:text-custom-mid-blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.056 3 11.625c0 4.291 3.52 7.846 8.25 8.142.026.002.051.002.076.002Z" />
                </svg>
                <span class="ml-1 text-sm">{{ $share->comments->count() }}</span>
            </button>
        </div>

        <div x-show="commentsOpen" x-transition class="mt-4" style="display: none;" x-data="{ newComment: '' }">
            <form @submit.prevent="
                fetch('{{ route('shares.comments.store', $share) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        body: newComment
                    })
                })
                .then(response => response.text())
                .then(html => {
                    let commentSection = $el.nextElementSibling;
                    commentSection.insertAdjacentHTML('beforeend', html);
                    newComment = '';
                })
            " class="flex items-center space-x-2">
                @csrf
                <img src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150' }}" alt="your avatar" class="h-8 w-8 rounded-full">
                <x-text-input x-model="newComment" name="body" class="block w-full" placeholder="Write a comment..." required />
                <x-primary-button type="submit" class="bg-custom-mid-blue">Post</x-primary-button>
            </form>

            <div class="mt-4 space-y-4">
                @forelse ($share->comments as $comment)
                    <x-comment :comment="$comment" />
                @empty
                    <p class="text-sm text-gray-500 text-center">No comments yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>