<div id="comment-{{ $comment->id }}" class="flex space-x-3 scroll-mt-20" x-data="{ openReply: false, openEdit: false }">
    {{-- Always show User Avatar (Request: Name and Avatar Maintain) --}}
    <x-user-avatar :user="$comment->user" class="h-10 w-10" />
    
    <div class="flex-1">
        <div>
            <a href="{{ route('profile.show', $comment->user->name) }}" class="font-bold text-gray-900 dark:text-gray-200">{{ $comment->user->name }}</a>
            <span class="text-gray-500 dark:text-gray-400 text-sm"> &middot; {{ $comment->created_at->diffForHumans() }}</span>
            @if ($comment->created_at != $comment->updated_at)
                <span class="text-gray-400 dark:text-gray-500 text-xs italic ml-1">(edited)</span>
            @endif
        </div>

        @if($comment->body === '[deleted]')
            {{-- Deleted Body --}}
            <div class="mt-1 text-gray-500 italic">
                [deleted]
            </div>
            
            {{-- Deleted Actions: Only Reply --}}
            <div class="mt-2 flex items-center space-x-4 text-sm">
                <button @click="openReply = !openReply" class="text-gray-500 hover:text-gray-900">Reply</button>
            </div>
        @else
            {{-- Normal Body --}}
            <div x-show="!openEdit">
                <p class="mt-1 text-gray-800 dark:text-white">
                    {{ $comment->body }}
                </p>
            </div>

            {{-- Edit Form --}}
            <div x-show="openEdit" x-transition class="mt-2" style="display: none;" x-data="{ editedBody: '{{ $comment->body }}' }">
                <form @submit.prevent="
                    fetch('{{ route('shares.comments.update', ['share' => $comment->share, 'comment' => $comment]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ body: editedBody, _method: 'PATCH' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.body) {
                            window.reloadWithScroll();
                        }
                    })
                ">
                    @csrf
                    @method('PATCH')
                    <textarea x-model="editedBody" name="body" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <div class="mt-2 space-x-2">
                        <x-primary-button type="submit" class="bg-custom-mid-blue">Save</x-primary-button>
                        <button type="button" @click="openEdit = false" class="text-gray-500">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Normal Actions --}}
            <div class="mt-2 flex items-center space-x-4 text-sm">
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
                        .then(response => {
                            if (response.ok) {
                                // If hard deleted (removed from DOM)
                                if (response.status === 200) {
                                    return response.json();
                                }
                            }
                        })
                        .then(data => {
                           window.reloadWithScroll();
                        })
                    ">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                    </form>
                @endif
            </div>
        @endif

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