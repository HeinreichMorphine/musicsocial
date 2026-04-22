<div id="comment-{{ $comment->id }}" class="flex space-x-3 scroll-mt-20 group/comment" x-data="{ 
    openReply: false, 
    openEdit: false, 
    bodyText: @js($comment->getCleanBody()),
    isDeleted: {{ $comment->body === '[deleted]' ? 'true' : 'false' }},
    upvoted: {{ $comment->hasUpvoted(auth()->id()) ? 'true' : 'false' }},
    upvoteCount: {{ $comment->getUpvoteCount() }},
    songId: '{{ $comment->getEmbeddedSongId() }}',
    songData: null,
    
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
}">
    {{-- Always show User Avatar --}}
    <x-user-avatar :user="$comment->user" class="h-10 w-10" />
    
    <div class="flex-1">
        <div>
            <a href="{{ route('profile.show', $comment->user->name) }}" class="font-bold text-gray-900 dark:text-gray-200">{{ $comment->user->name }}</a>
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
                    <div class="mt-3 max-w-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-sm flex items-center p-2 group/card hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors">
                        <img :src="songData.album_art_url || '/images/default-album-art.png'" alt="Album Art" class="w-12 h-12 rounded object-cover shadow-sm shrink-0">
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="songData.track_name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="songData.artist_name"></p>
                        </div>
                        <a :href="songData.spotify_url" target="_blank" class="ml-2 pr-2 shrink-0 text-white hover:scale-110 transition-transform">
                            <div class="bg-[#1DB954] text-white rounded-full p-1.5 shadow-md">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.54.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                            </div>
                        </a>
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
                <button @click="toggleUpvote()" class="flex items-center space-x-1 transition-colors group/upvote" :class="upvoted ? 'text-custom-mid-blue font-bold' : 'text-gray-500 hover:text-custom-mid-blue'">
                    <svg xmlns="http://www.w3.org/2000/svg" :fill="upvoted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover/upvote:-translate-y-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
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