<div id="comment-{{ $comment->id }}" class="flex space-x-3 scroll-mt-20 group/comment" x-data="{ 
    openReply: false, 
    openEdit: false, 
    bodyText: @js($comment->getCleanBody()),
    isDeleted: {{ $comment->body === '[deleted]' ? 'true' : 'false' }},
    upvoted: {{ $comment->hasUpvoted(auth()->id()) ? 'true' : 'false' }},
    upvoteCount: {{ $comment->getUpvoteCount() }},
    songId: '{{ $comment->getEmbeddedSongId() }}',
    songData: null,
    playerOpen: false,
    isPlayingPreview: false,
    isReady: window.isSpotifyReady || false,
    isPremium: {{ (auth()->check() && auth()->user()->isSpotifyPremium()) ? 'true' : 'false' }},
    isSupported: window.isSpotifySupported !== false,
    
    init() {
        if (this.songId) {
            fetch(`/search/tracks/${this.songId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.song) {
                        this.songData = data.song;
                    }
                })
                .catch(err => console.error('Failed to fetch comment song:', err));
        }
    },
    
    toggleUpvote() {
        fetch('{{ route('shares.comments.upvote', ['share' => $comment->share, 'comment' => $comment]) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            this.upvoted = data.upvoted;
            this.upvoteCount = data.count;
        });
    }
}"
     @spotify-ready.window="isReady = true"
     @spotify-not-ready.window="isReady = false"
     @spotify-unsupported.window="isSupported = false">
    {{-- Always show User Avatar --}}
    <a href="{{ route('profile.show', $comment->user->name) }}" wire:navigate class="shrink-0">
        <x-user-avatar :user="$comment->user" class="h-10 w-10" />
    </a>
    
    <div class="flex-1">
        <div>
            <a href="{{ route('profile.show', $comment->user->name) }}" wire:navigate class="font-bold text-gray-900 dark:text-gray-200">{{ $comment->user->name }}</a>
            <span class="text-gray-500 dark:text-gray-400 text-sm"> &middot; {{ $comment->created_at->diffForHumans() }}</span>
            @if ($comment->created_at != $comment->updated_at)
                <span class="text-gray-400 dark:text-gray-500 text-xs italic ml-1">(edited)</span>
            @endif
        </div>

        {{-- Deleted State Handles --}}
        <div x-show="isDeleted" style="display: {{ $comment->body === '[deleted]' ? 'block' : 'none' }};">
            <div class="mt-1 text-gray-500 italic">
                [deleted]
            </div>
            <div class="mt-2 flex items-center space-x-4 text-sm">
                <button @click="openReply = !openReply" class="text-gray-500 hover:text-gray-900">Reply</button>
            </div>
        </div>

        {{-- Normal State Handles --}}
        <div x-show="!isDeleted" style="display: {{ $comment->body !== '[deleted]' ? 'block' : 'none' }};">
            <div x-show="!openEdit">
                <p class="mt-1 text-gray-800 dark:text-white" x-text="bodyText"></p>
                
                {{-- Mini Song Card for Recommendations (Dynamic) --}}
                <template x-if="songData">
                    <div class="mt-3">
                        <div class="relative rounded-2xl p-4 group/card overflow-hidden">
                            {{-- Background blur/gradient --}}
                        <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110 transition-transform duration-700 group-hover/card:scale-125" :style="`background-image: url('${songData.album_art_url || '/images/default-album-art.png'}');`"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                        
                        <div class="relative flex items-center space-x-4 z-10">
                            <img :src="songData.album_art_url || '/images/default-album-art.png'" alt="Album Art" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl shadow-xl transition-transform duration-300 group-hover/card:scale-105 shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-lg font-bold text-white truncate drop-shadow-md" x-text="songData.track_name"></p>
                                <p class="text-sm text-gray-200 truncate drop-shadow-sm" x-text="songData.artist_name"></p>
                                
                                <div class="flex items-center space-x-3 mt-2">
                                    {{-- Spotify Link --}}
                                    @php
                                        $isLinked = auth()->check() && auth()->user()->spotify_token;
                                        $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
                                    @endphp
                                    <button type="button"
                                        @click.prevent.stop="
                                            playerOpen = !playerOpen;
                                            if (isPremium && isSupported) {
                                                if (playerOpen) {
                                                    if(isReady && window.toggleSpotifyPlayer) {
                                                        const meta = { 
                                                            name: songData.track_name, 
                                                            artist: songData.artist_name, 
                                                            art: songData.album_art_url, 
                                                            previewUrl: songData.preview_url || '' 
                                                        };
                                                        window.toggleSpotifyPlayer('spotify:track:' + songData.spotify_track_id, meta);
                                                    }
                                                } else {
                                                    if(window.toggleSpotifyPlayer) {
                                                        window.toggleSpotifyPlayer('spotify:track:' + songData.spotify_track_id, null);
                                                    }
                                                }
                                            }
                                        "
                                        :title="isPremium && isSupported ? (isReady ? 'Play on Spotify' : 'Connecting to Spotify...') : 'Play 30s preview'"
                                        :class="isPremium && isSupported && !isReady ? 'opacity-40 grayscale cursor-not-allowed' : 'hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)] cursor-pointer'"
                                        class="shrink-0 flex items-center justify-center transition-all duration-300 relative">
                                        <svg class="w-7 h-7 shrink-0 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                    </button>
                
                                    <!-- Add to Playlist Button -->
                                    <div class="relative inline-block shrink-0" x-data="{ isDropdownOpen: false }">
                                        <button type="button" 
                                            @click.prevent.stop="isDropdownOpen = !isDropdownOpen"
                                            title="Add to Playlist" 
                                            class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.4)] bg-white/20 rounded-full p-1 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div x-show="isDropdownOpen"
                                                @click.away="isDropdownOpen = false"
                                                x-transition
                                                class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-48 bg-white dark:bg-[#141414] rounded-2xl shadow-xl border border-gray-100 dark:border-white/10 overflow-hidden z-50 py-1"
                                                style="display: none;">
                                                
                                                <!-- Add to Reso Playlist -->
                                                <button type="button"
                                                        @click.prevent.stop="
                                                            $dispatch('open-reso-playlist-modal', { songId: songData.spotify_track_id, trackName: songData.track_name });
                                                            isDropdownOpen = false;
                                                        "
                                                        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-left font-semibold">
                                                    <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-indigo-500 text-white shrink-0">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/>
                                                        </svg>
                                                    </span>
                                                    <span>Reso Playlist</span>
                                                </button>
                
                                                <!-- Add to Spotify Playlist -->
                                                <button type="button"
                                                        @click.prevent.stop="
                                                            @if(auth()->check() && auth()->user()->spotify_id)
                                                                $dispatch('open-spotify-playlist-modal', { trackUri: 'spotify:track:' + songData.spotify_track_id, trackName: songData.track_name })
                                                            @else
                                                                $dispatch('open-spotify-link-modal')
                                                            @endif;
                                                            isDropdownOpen = false;
                                                        "
                                                        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-left font-semibold">
                                                    <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-[#1DB954] text-white shrink-0">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                                    </span>
                                                    <span>Spotify Playlist</span>
                                                </button>
                                        </div>
                                    </div>
                
                                    <!-- YouTube Link (Always visible, with fallback search) -->
                                    <a :href="songData.youtube_url || `https://www.youtube.com/results?search_query=${encodeURIComponent(songData.track_name + ' ' + songData.artist_name)}`" 
                                       x-on:click.stop 
                                       target="_blank" 
                                       title="Watch on YouTube" 
                                       class="shrink-0 hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,0,0,0.6)]">
                                        <svg class="w-7 h-7 shrink-0 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Inline Spotify Embed Player --}}
                        <div x-show="playerOpen && (!isPremium || !isSupported)"
                             x-transition
                             class="mt-3 rounded-2xl overflow-hidden bg-black/40 border border-white/10"
                             style="display:none;">
                            <iframe class="share-spotify-frame"
                                x-bind:src="playerOpen && songData && (!isPremium || !isSupported) ? 'https://open.spotify.com/embed/track/' + songData.spotify_track_id + '?utm_source=generator&theme=0' : ''"
                                width="100%"
                                height="80"
                                frameborder="0"
                                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                loading="lazy"
                                style="border-radius:12px; display:block;">
                            </iframe>
                        </div>

                    </div>
                </template>
            </div>

            {{-- Edit Form --}}
            <div x-show="openEdit" x-transition class="mt-2" style="display: none;">
                <form @submit.prevent="
                    fetch('{{ route('shares.comments.update', ['share' => $comment->share, 'comment' => $comment]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ body: bodyText, _method: 'PATCH' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.body) {
                            bodyText = data.body;
                            openEdit = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Failed to update comment');
                    })
                ">
                    @csrf
                    @method('PATCH')
                    {{-- Bind to bodyText --}}
                    <textarea x-model="bodyText" name="body" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <div class="mt-2 space-x-2">
                        <x-primary-button type="submit" class="bg-custom-mid-blue">Save</x-primary-button>
                        <button type="button" @click="openEdit = false" class="text-gray-500">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Normal Actions --}}
            <div class="mt-2 flex items-center space-x-4 text-sm">
                <!-- Upvote Button -->
                <button @click="toggleUpvote()" class="flex items-center space-x-1 transition-colors group/upvote" :class="upvoted ? 'text-red-500 font-bold' : 'text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400'">
                    <svg xmlns="http://www.w3.org/2000/svg" :fill="upvoted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover/upvote:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    <span x-text="upvoteCount"></span>
                </button>

                <button @click="openReply = !openReply" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">Reply</button>
                @if ($comment->user->is(auth()->user()))
                    <button @click="openEdit = !openEdit" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">Edit</button>
                    <form @submit.prevent="
                        if (!confirm('Are you sure you want to delete this comment?')) return;
                        fetch('{{ route('shares.comments.destroy', ['share' => $comment->share, 'comment' => $comment]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                        .then(response => response.json())
                        .then(data => {
                           if (data.message.includes('thread preserved')) {
                               isDeleted = true;
                           } else {
                               $el.closest('#comment-{{ $comment->id }}').remove();
                           }
                        })
                    ">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div x-show="openReply" x-transition class="mt-2" style="display: none;" x-data="{
            newReply: '',
            mentionSuggestions: [],
            showMentions: false,
            selectedIndex: 0,
            async fetchMentions(query) {
                if (!query) {
                    this.showMentions = false;
                    return;
                }
                const response = await fetch(`{{ route('mentions.suggestions') }}?query=${encodeURIComponent(query)}&parent_comment_id={{ $comment->id }}`);
                this.mentionSuggestions = await response.json();
                this.showMentions = this.mentionSuggestions.length > 0;
                this.selectedIndex = 0;
            },
            handleInput() {
                const textarea = $refs.replyInput;
                const cursorPos = textarea.selectionStart;
                const text = this.newReply.substring(0, cursorPos);
                const match = text.match(/@(\w*)$/);
                if (match) {
                    this.fetchMentions(match[1]);
                } else {
                    this.showMentions = false;
                }
            },
            selectMention(username) {
                const textarea = $refs.replyInput;
                const cursorPos = textarea.selectionStart;
                const textBefore = this.newReply.substring(0, cursorPos);
                const textAfter = this.newReply.substring(cursorPos);
                const newTextBefore = textBefore.replace(/@\w*$/, '@' + username + ' ');
                this.newReply = newTextBefore + textAfter;
                this.showMentions = false;
                setTimeout(() => textarea.focus(), 10);
            },
            handleKeydown(event) {
                if (!this.showMentions) return;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.mentionSuggestions.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                } else if (event.key === 'Enter' && this.mentionSuggestions[this.selectedIndex]) {
                    event.preventDefault();
                    this.selectMention(this.mentionSuggestions[this.selectedIndex].name);
                } else if (event.key === 'Escape') {
                    this.showMentions = false;
                }
            }
        }">
                <form @submit.prevent="
                    fetch('{{ route('shares.comments.store', $comment->share) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            body: newReply,
                            parent_id: {{ $comment->id }}
                        })
                    })
                    .then(response => response.text())
                    .then(html => {
                        let wrapper = $el.closest('[x-data]');
                        // The wrapper is the inner x-data div (reply form). The comments container is its sibling.
                        // We go to the parent (.flex-1) and search there.
                        let commentContainer = wrapper.parentElement.querySelector('.border-l-2.space-y-4');
                        commentContainer.insertAdjacentHTML('beforeend', html);
                        newReply = '';
                        openReply = false;
                    })
                    .catch(error => {
                        console.error('Error posting reply:', error);
                        alert('Failed to post reply. Please try again.');
                    })
                ">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="flex items-center space-x-2 relative">
                        <x-user-avatar :user="auth()->user()" class="h-8 w-8" />
                        <div class="flex-1 relative">
                            <x-text-input 
                                x-ref="replyInput"
                                x-model="newReply" 
                                @input="handleInput()"
                                @keydown="handleKeydown($event)"
                                name="body" 
                                class="block w-full" 
                                placeholder="Write a reply..." 
                                required 
                            />
                            <!-- Mention Autocomplete Dropdown -->
                            <div 
                                x-show="showMentions" 
                                x-transition
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto"
                            >
                                <template x-for="(user, index) in mentionSuggestions" :key="user.id">
                                    <div 
                                        @click="selectMention(user.name)"
                                        :class="{ 'bg-blue-100': index === selectedIndex }"
                                        class="px-4 py-2 cursor-pointer hover:bg-blue-50 flex items-center space-x-2"
                                    >
                                    <div class="h-6 w-6 relative inline-block shrink-0" x-data="{ avatarError: false }">
                                        <img x-show="user.profile_picture && !avatarError"
                                             :src="'{{ asset('storage') }}/' + user.profile_picture"
                                             class="h-6 w-6 rounded-full object-cover"
                                             x-on:error="avatarError = true">
                                        <div x-show="!user.profile_picture || avatarError"
                                             class="h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-[10px]"
                                             style="display: none;">
                                            <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                        <span x-text="user.name" class="text-sm font-medium text-gray-800"></span>
                                        <span x-show="user.is_parent_author" class="text-xs text-gray-500">(OP)</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <x-primary-button type="submit" class="bg-custom-mid-blue">Post</x-primary-button>
                    </div>
                </form>
        </div>

        <div class="mt-4 space-y-4 pl-8 border-l-2 border-gray-200">
            @foreach ($comment->replies as $reply)
                <x-comment :comment="$reply" />
            @endforeach
        </div>
    </div>
</div>