@props(['share'])

<div class="bg-white/60 backdrop-blur-md rounded-3xl shadow-sm border border-white/50 p-6 mb-6 hover:shadow-md transition-shadow duration-300" x-data="{ commentsOpen: false }">
    <div class="flex space-x-4">
        <img src="{{ $share->user->profile_picture ? Storage::url($share->user->profile_picture) : 'https://via.placeholder.com/150' }}"
             alt="avatar"
             class="h-14 w-14 rounded-full object-cover border-2 border-white shadow-sm">

        <div class="flex-1">
            <div class="flex justify-between items-center">
                <div>
                    <a href="{{ route('profile.show', $share->user->name) }}" class="font-bold text-gray-900 hover:text-custom-mid-blue transition-colors">{{ $share->user->name }}</a>
                    <span class="text-gray-500 text-sm"> &middot; {{ $share->created_at->diffForHumans() }}</span>
                </div>
                @if ($share->user->is(auth()->user()))
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg z-10 py-1 ring-1 ring-black/5" style="display: none;">
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
                                        $el.closest('.bg-white\\/60').remove();
                                    }
                                })
                            ">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    Delete Share
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <p class="mt-2 text-gray-800 text-lg leading-relaxed">
                {{ $share->caption }}
            </p>

            <div class="mt-4 relative overflow-hidden rounded-3xl p-6 group">
                 <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-60 transform scale-110 transition-transform duration-700 group-hover:scale-125" style="background-image: url('{{ $share->song->album_art_url }}');"></div>
                 <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                <div class="relative flex items-center space-x-6">
                    <img src="{{ $share->song->album_art_url }}" alt="Album Art" class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl shadow-2xl transition-transform duration-300 group-hover:scale-105 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-2xl font-bold text-white truncate drop-shadow-md">{{ $share->song->track_name }}</p>
                        <p class="text-lg text-gray-200 truncate drop-shadow-sm">{{ $share->song->artist_name }}</p>
                        
                        <div class="flex items-center space-x-3 mt-3">
                            <a href="{{ $share->song->spotify_url }}" target="_blank" title="Listen on Spotify" class="hover:scale-110 transition-transform">
                                <img src="{{ asset('icons/spotify_icon.png') }}" alt="Spotify Logo" class="w-8 h-8 drop-shadow-lg">
                            </a>

                            @if ($share->song->youtube_url)
                                <a href="{{ $share->song->youtube_url }}" target="_blank" title="Watch on YouTube" class="hover:scale-110 transition-transform">
                                    <img src="{{ asset('icons/youtube_icon.png') }}" alt="YouTube Logo" class="w-8 h-8 drop-shadow-lg">
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-t border-gray-100/50 pt-3"
                x-data="{
                    liked: {{ auth()->check() && auth()->user()->likes->contains($share) ? 'true' : 'false' }},
                    likesCount: {{ $share->likes->count() }},
                    disliked: {{ auth()->check() && auth()->user()->dislikes->contains($share) ? 'true' : 'false' }},
                    dislikesCount: {{ $share->dislikes->count() }}
                }">
                
                <div class="grid grid-cols-3 gap-2 w-full mt-2">
                    <!-- Like Zone -->
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
                            if (data.disliked !== undefined) {
                                disliked = data.disliked;
                                dislikesCount = data.dislikesCount;
                            }
                        })
                    " class="flex justify-center">
                        @csrf
                        <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-pink-50 transition-colors group w-full justify-center" title="Like">
                            <div class="relative">
                                <template x-if="liked">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-pink-500 animate-bounce"><path d="M11.645 20.91a.75.75 0 0 1-1.29 0C8.343 16.63 3.75 12.55 3.75 8.25 3.75 5.399 5.399 3.75 8.25 3.75c1.74 0 3.333.92 4.25 2.336C13.417 4.67 15.01 3.75 16.75 3.75c2.851 0 4.5 1.649 4.5 4.5 0 4.3-4.593 8.38-6.605 10.369a.75.75 0 0 1-1.29-.012Z" /></svg>
                                </template>
                                <template x-if="!liked">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500 group-hover:text-pink-500 transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                </template>
                            </div>
                            <span x-text="likesCount" class="text-sm font-bold text-gray-600 group-hover:text-pink-500 transition-colors"></span>
                        </button>
                    </form>

                    <!-- Dislike Zone -->
                    @if ($share->user->isNot(auth()->user()))
                    <form @submit.prevent="
                        fetch('{{ route('shares.dislike', $share) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            disliked = data.disliked;
                            dislikesCount = data.dislikesCount;
                            if (data.liked !== undefined) {
                                liked = data.liked;
                                likesCount = data.likesCount;
                            }
                        })
                    " class="flex justify-center">
                        @csrf
                        <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-gray-100 transition-colors group w-full justify-center" title="Dislike">
                            <template x-if="disliked">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500">
                                    <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v16.19l2.47-2.47a.75.75 0 1 1 1.06 1.06l-3.75 3.75a.75.75 0 0 1-1.06 0l-3.75-3.75a.75.75 0 1 1 1.06-1.06l2.47 2.47V3a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                                </svg>
                            </template>
                            <template x-if="!disliked">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500 group-hover:text-red-500 transition-colors">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </template>
                            <span x-text="dislikesCount" class="text-sm font-bold text-gray-500 group-hover:text-gray-700 transition-colors"></span>
                        </button>
                    </form>
                    @else
                    <div class="flex items-center justify-center text-gray-300 cursor-not-allowed py-2" title="You cannot dislike your own post">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                        </svg>
                    </div>
                    @endif

                    <!-- Comment Zone -->
                    <div class="flex justify-center">
                        <button @click="commentsOpen = !commentsOpen" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-blue-50 transition-colors group w-full justify-center" title="Comments">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500 group-hover:text-custom-mid-blue transition-colors">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.056 3 11.625c0 4.291 3.52 7.846 8.25 8.142.026.002.051.002.076.002Z" />
                            </svg>
                            <span class="text-sm font-bold text-gray-600 group-hover:text-custom-mid-blue transition-colors">{{ $share->comments->count() }}</span>
                        </button>
                    </div>
                </div>
            </div>

            @if ($share->comments->isNotEmpty())
                <div class="mt-4 flex items-start space-x-3 cursor-pointer hover:bg-white/60 p-3 rounded-2xl transition shadow-sm border border-gray-100/50" x-show="!commentsOpen" @click="commentsOpen = true">
                     <img src="{{ $share->comments->first()->user->profile_picture ? Storage::url($share->comments->first()->user->profile_picture) : 'https://via.placeholder.com/150' }}"
                         alt="{{ $share->comments->first()->user->name }}"
                         class="h-10 w-10 rounded-full object-cover mt-0.5 border-2 border-white shadow-sm">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $share->comments->first()->user->name }}</p>
                        <p class="text-gray-700 text-base leading-snug mt-1">{{ Str::limit($share->comments->first()->body, 120) }}</p>
                    </div>
                </div>
            @endif

            <div x-show="commentsOpen" x-transition class="mt-4 px-4 pl-6 border-l-2 border-gray-100" style="display: none;" x-data="{ newComment: '' }">
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
                " class="flex items-center space-x-3 mb-6">
                    @csrf
                    <img src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150' }}" alt="your avatar" class="h-8 w-8 rounded-full border border-gray-200">
                    <x-text-input x-model="newComment" name="body" class="block w-full text-sm rounded-full bg-gray-50 border-gray-200 focus:bg-white transition-colors" placeholder="Write a comment..." required />
                    <button type="submit" class="bg-custom-mid-blue text-white rounded-full p-2 hover:bg-custom-dark-blue transition-colors shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 004.836 9.25h4.288a.75.75 0 010 1.5H4.836a1.5 1.5 0 00-1.144 1.086l-1.414 4.925a.75.75 0 00.826.95 28.89 28.89 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z" />
                        </svg>
                    </button>
                </form>

                <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse ($share->comments as $comment)
                        <x-comment :comment="$comment" />
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No comments yet. Be the first!</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>