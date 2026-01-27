@props(['share', 'paginatedComments' => null, 'totalCount' => null, 'previewComments' => null])

<a href="{{ route('shares.show', $share) }}" class="block text-current no-underline">
    <div id="share-{{ $share->id }}" class="bg-white/60 dark:bg-black backdrop-blur-md rounded-3xl shadow-sm border border-white/50 dark:border-white/10 p-6 mb-6 hover:shadow-md transition-shadow duration-300 scroll-mt-20" 
         x-data="{ 
            commentsOpen: false, 
            editing: false, 
            editCaption: @js($share->caption),
            originalCaption: @js($share->caption)
         }">
        <div class="flex space-x-3">
            <x-user-avatar :user="$share->user" class="h-14 w-14 border-2 border-white dark:border-gray-700 shadow-sm shrink-0" />

            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <div class="text-left">
                        <a href="{{ route('profile.show', $share->user->name) }}" x-on:click.stop class="font-bold text-gray-900 dark:text-white hover:text-custom-mid-blue transition-colors">{{ $share->user->name }}</a>
                        <span class="text-gray-500 dark:text-gray-400 text-sm"> &middot; {{ $share->created_at->diffForHumans() }}</span>
                    </div>
                    @if ($share->user->is(auth()->user()))
                        <div x-data="{ open: false }" class="relative ml-auto" x-on:click.stop>
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg z-10 py-1 ring-1 ring-black/5" style="display: none;" x-transition>
                                <!-- Edit Button -->
                                <button @click="editing = true; open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    Edit Caption
                                </button>
                                
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
                                            window.reloadWithScroll();
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

                <!-- Display Mode -->
                <p class="mt-2 text-gray-800 dark:text-gray-100 text-lg leading-relaxed break-words" x-show="!editing" x-text="editCaption"></p>

                <!-- Edit Mode -->
                <div x-show="editing" class="mt-2" style="display: none;" x-on:click.stop>
                    <textarea x-model="editCaption" class="w-full rounded-xl border-gray-200 focus:border-custom-mid-blue focus:ring focus:ring-custom-mid-blue/20 transition-shadow" rows="2"></textarea>
                    <div class="flex justify-end space-x-2 mt-2">
                        <button @click="editing = false; editCaption = originalCaption" class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                        <button @click="
                            fetch('{{ route('shares.update', $share) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ 
                                    _method: 'PATCH',
                                    caption: editCaption
                                })
                            })
                            .then(response => {
                                if (!response.ok) throw new Error('Failed to update');
                                return response.json();
                            })
                            .then(data => {
                                window.reloadWithScroll();
                            })
                            .catch(error => {
                                alert('Error updating share');
                                console.error(error);
                            })
                        " class="px-3 py-1 text-sm bg-custom-mid-blue text-white rounded-lg hover:bg-custom-dark-blue">Save</button>
                    </div>
                </div>

                <div class="mt-4 relative overflow-hidden rounded-3xl p-6 group">
                     <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110 transition-transform duration-700 group-hover:scale-125" style="background-image: url('{{ $share->song->album_art_url }}');"></div>
                     <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="relative flex items-center space-x-6">
                        <img src="{{ $share->song->album_art_url }}" alt="Album Art" class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl shadow-2xl transition-transform duration-300 group-hover:scale-105 shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-2xl font-bold text-white truncate drop-shadow-md">{{ $share->song->track_name }}</p>
                            <p class="text-lg text-gray-200 truncate drop-shadow-sm">{{ $share->song->artist_name }}</p>
                            
                            <div class="flex items-center space-x-3 mt-3">
                                <a href="{{ $share->song->spotify_url }}" x-on:click.stop target="_blank" title="Listen on Spotify" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)]">
                                    <img src="{{ asset('icons/spotify_icon.png') }}" alt="Spotify Logo" class="w-8 h-8 drop-shadow-lg">
                                </a>

                                <!-- Add to Playlist Button -->
                                <button type="button" 
                                    @if(auth()->check() && auth()->user()->spotify_id)
                                        x-on:click.prevent.stop="$dispatch('open-spotify-playlist-modal', { trackUri: '{{ 'spotify:track:' . $share->song->spotify_track_id }}', trackName: '{{ addslashes($share->song->track_name) }}' })"
                                    @else
                                        x-on:click.prevent.stop="$dispatch('open-spotify-link-modal')"
                                    @endif
                                    title="Add to Spotify Playlist" 
                                    class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)] bg-white/20 rounded-full p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>

                                @if ($share->song->youtube_url)
                                    <a href="{{ $share->song->youtube_url }}" x-on:click.stop target="_blank" title="Watch on YouTube" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,0,0,0.6)]">
                                        <img src="{{ asset('icons/youtube_icon.png') }}" alt="YouTube Logo" class="w-8 h-8 drop-shadow-lg">
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100/50 pt-3" x-on:click.stop
                    x-data="{
                        liked: {{ auth()->check() && auth()->user()->likes->contains($share) ? 'true' : 'false' }},
                        likesCount: {{ $share->likes->count() }},
                        disliked: {{ auth()->check() && auth()->user()->dislikes->contains($share) ? 'true' : 'false' }},
                        dislikesCount: {{ $share->dislikes->count() }},
                        bookmarked: {{ auth()->check() && auth()->user()->bookmarks->contains($share) ? 'true' : 'false' }}
                    }">
                    
                    <div class="grid grid-cols-4 gap-2 w-full mt-2">
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
                                window.reloadWithScroll();
                            })
                        " class="flex justify-center">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-pink-50 transition-colors group w-full justify-center" title="Like">
                                <div class="relative">
                                    <template x-if="liked">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-pink-500"><path d="M11.645 20.91a.75.75 0 0 1-1.29 0C8.343 16.63 3.75 12.55 3.75 8.25 3.75 5.399 5.399 3.75 8.25 3.75c1.74 0 3.333.92 4.25 2.336C13.417 4.67 15.01 3.75 16.75 3.75c2.851 0 4.5 1.649 4.5 4.5 0 4.3-4.593 8.38-6.605 10.369a.75.75 0 0 1-1.29-.012Z" /></svg>
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
                                window.reloadWithScroll();
                            })
                        " class="flex justify-center">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-gray-100 transition-colors group w-full justify-center" title="Dislike">
                                <template x-if="disliked">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-red-500">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        <path d="M12 13l-1-1 2-2-3-3 2-2" />
                                    </svg>
                                </template>
                                <template x-if="!disliked">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-gray-500 group-hover:text-red-500 transition-colors">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        <path d="M12 13l-1-1 2-2-3-3 2-2" />
                                    </svg>
                                </template>
                            </button>
                        </form>
                        @else
                        <div class="flex items-center justify-center text-gray-300 cursor-not-allowed py-2" title="You cannot dislike your own post">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                <path d="M12 13l-1-1 2-2-3-3 2-2" />
                            </svg>
                        </div>
                        @endif

                        <!-- Comment Zone -->
                        <div class="flex justify-center">
                            <button @click="commentsOpen = !commentsOpen" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-blue-50 transition-colors group w-full justify-center" title="Comments">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500 group-hover:text-custom-mid-blue transition-colors">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.056 3 11.625c0 4.291 3.52 7.846 8.25 8.142.026.002.051.002.076.002Z" />
                                </svg>
                                <span class="text-sm font-bold text-gray-600 group-hover:text-custom-mid-blue transition-colors">{{ $totalCount ?? $share->comments->count() }}</span>
                            </button>
                        </div>

                        <!-- Bookmark Zone -->
                        <div class="flex justify-center">
                            <form @submit.prevent="
                                fetch('{{ route('shares.bookmark', $share) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                })
                                .then(response => response.json())
                                .then(data => {
                                    window.reloadWithScroll();
                                })
                            " class="flex justify-center w-full">
                                @csrf
                                <button type="submit" class="flex items-center space-x-2 py-2 px-4 rounded-xl hover:bg-yellow-50 transition-colors group w-full justify-center" title="Bookmark">
                                    <template x-if="bookmarked">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-yellow-500">
                                            <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0111.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 01-1.085.67L12 18.089l-7.165 3.583A.75.75 0 013.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="!bookmarked">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500 group-hover:text-yellow-500 transition-colors">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0111.186 0z" />
                                        </svg>
                                    </template>
                                    <span x-text="bookmarked ? 'Saved' : 'Save'" class="text-sm font-bold text-gray-600 group-hover:text-yellow-500 transition-colors"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-on:click.stop>
                    @php
                        $hasComments = $totalCount ? $totalCount > 0 : $share->comments->isNotEmpty();
                        $previews = $previewComments ?? ($hasComments ? $share->comments->sortByDesc('created_at')->take(3) : collect());
                    @endphp

                    @if ($previews->isNotEmpty())
                        <div class="mt-4 space-y-2" x-show="!commentsOpen">
                            @foreach ($previews as $preview)
                                <div class="flex items-start space-x-3 cursor-pointer hover:bg-white/60 dark:hover:bg-white/10 p-3 rounded-2xl transition shadow-sm border border-gray-100/50 dark:border-white/5" @click="commentsOpen = true">
                                     <x-user-avatar :user="$preview->user" class="h-10 w-10 mt-0.5 border-2 border-white dark:border-gray-700 shadow-sm" />
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $preview->user->name }}</p>
                                        <p class="text-gray-700 dark:text-gray-300 text-base leading-snug mt-1">{{ Str::limit($preview->body, 120) }}</p>
                                    </div>
                                </div>
                            @endforeach
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
                                let wrapper = $el.closest('[x-data="{ newComment: \'\' }"]');
                                let commentSection = wrapper.querySelector('.space-y-4');
                                commentSection.insertAdjacentHTML('beforeend', html);
                                newComment = '';
                                // Update comment count
                                let countEl = $el.closest('.flex-1').querySelector('.text-gray-600.group-hover\:text-custom-mid-blue');
                                countEl.textContent = parseInt(countEl.textContent) + 1;
                            })
                        " class="flex items-center space-x-3 mb-6">
                            @csrf
                            <x-user-avatar :user="auth()->user()" class="h-8 w-8 border border-gray-200 dark:border-gray-700" />
                            <x-text-input x-model="newComment" name="body" class="block w-full text-sm rounded-full bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 transition-colors dark:text-white" placeholder="Write a comment..." required />
                            <button type="submit" class="bg-custom-mid-blue text-white rounded-full p-2 hover:bg-custom-dark-blue transition-colors shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 004.836 9.25h4.288a.75.75 0 010 1.5H4.836a1.5 1.5 0 00-1.144 1.086l-1.414 4.925a.75.75 0 00.826.95 28.89 28.89 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z" />
                                </svg>
                            </button>
                        </form>

                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                            @php
                                $commentsToDisplay = $paginatedComments ?? $share->comments->reject(fn($c) => $c->parent->isNotEmpty())->sortByDesc('created_at');
                            @endphp
                            @forelse ($commentsToDisplay as $comment)
                                <x-comment :comment="$comment" />
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No comments yet. Be the first!</p>
                            @endforelse

                            @if($paginatedComments)
                                <div class="mt-4">
                                    {{ $paginatedComments->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</a>
