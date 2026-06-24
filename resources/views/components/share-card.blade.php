@props(['share', 'paginatedComments' => null, 'totalCount' => null, 'previewComments' => null])

@php
    $isLinked = auth()->check() && auth()->user()->spotify_token;
    $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
@endphp

<div id="share-{{ $share->id }}" class="bg-white/60 dark:bg-black backdrop-blur-md rounded-3xl shadow-sm border border-white/50 dark:border-white/10 p-4 shrink md:p-6 mb-4 md:mb-6 hover:shadow-md transition-shadow duration-300 scroll-mt-20"
    @click="Livewire.navigate('{{ route('shares.show', $share) }}')" style="cursor:pointer;"
    
         x-data="{ 
            commentsOpen: {{ $share->is_deleted ? 'true' : 'false' }}, 
            editing: false, 
            playerOpen: false,
            isPlayingPreview: false,
            isReady: window.isSpotifyReady || false,
            isPremium: {{ $isPremium ? 'true' : 'false' }},
            isSupported: window.isSpotifySupported !== false,
            editCaption: @js($share->caption),
            originalCaption: @js($share->caption),
            liked: {{ auth()->check() && auth()->user()->likes->contains($share) ? 'true' : 'false' }},
            likesCount: {{ $share->likes->count() }},
            disliked: {{ auth()->check() && auth()->user()->dislikes->contains($share) ? 'true' : 'false' }},
            bookmarked: {{ auth()->check() && auth()->user()->bookmarks->contains($share) ? 'true' : 'false' }},
            type: '{{ $share->type }}',
            commentSong: null,
            commentSearch: '',
            commentResults: [],
            isSearching: false
         }"
         @spotify-ready.window="isReady = true"
         @spotify-not-ready.window="isReady = false"
         @spotify-unsupported.window="isSupported = false"
         :class="type === 'recommendation_request' ? 'border-custom-mid-blue ring-2 ring-custom-mid-blue/10 shadow-[0_0_40px_-10px_rgba(59,130,246,0.2)] dark:shadow-[0_0_40px_-10px_rgba(59,130,246,0.1)] transition-all' : 'border-white/50 dark:border-white/10'">
        <div class="flex flex-col md:flex-row md:space-x-3">
            
            <!-- Mobile Header Row (Visible Only on Mobile) -->
            <div class="flex md:hidden items-center mb-3 space-x-3 w-full">
                @if($share->is_deleted)
                    <div class="shrink-0">
                        <img src="{{ asset('icons/reso.png') }}" class="h-10 w-10 border-2 border-white dark:border-gray-700 shadow-sm rounded-full" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-gray-400 dark:text-gray-500 text-base">[deleted]</span>
                        <span class="text-gray-500 dark:text-gray-400 text-xs block"> {{ $share->created_at->diffForHumans() }}</span>
                    </div>
                @else
                    <a href="{{ route('profile.show', $share->user->name) }}" wire:navigate x-on:click.stop class="shrink-0">
                        <x-user-avatar :user="$share->user" class="h-10 w-10 border-2 border-white dark:border-gray-700 shadow-sm" />
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('profile.show', $share->user->name) }}" wire:navigate x-on:click.stop class="font-bold text-gray-900 dark:text-white hover:text-custom-mid-blue transition-colors text-base">{{ $share->user->name }}</a>
                        <span class="text-gray-500 dark:text-gray-400 text-xs block"> {{ $share->created_at->diffForHumans() }}</span>
                    </div>
                    <div x-data="{ open: false }" class="relative" x-on:click.stop>
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                        </button>
                    <!-- Copy Dropdown from below for mobile -->
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg z-50 py-1 ring-1 ring-black/5 dark:ring-white/10" style="display: none;" x-transition>
                        @if ($share->user->is(auth()->user()))
                            <!-- Edit Button -->
                            <button @click="editing = true; open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Edit Caption
                            </button>
                            
                            <form @submit.prevent="
                                if (!confirm('Are you sure you want to delete this share?')) return;
                                fetch('{{ route('shares.destroy', $share) }}', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                                .then(response => {
                                    if (response.ok) {
                                        window.reloadWithScroll();
                                    }
                                })
                            ">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    Delete Share
                                </button>
                            </form>
                        @else
                            <!-- Not for me / Dislike -->
                            <button @click="
                                open = false;
                                disliked = !disliked;
                                if (disliked) {
                                    if (typeof liked !== 'undefined' && liked) {
                                        liked = false;
                                        likesCount--;
                                    }
                                }
                                
                                fetch('{{ route('shares.dislike', $share) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) throw new Error('Failed');
                                    return response.json();
                                })
                                .then(data => {
                                    disliked = data.disliked;
                                    liked = data.liked;
                                    likesCount = data.likesCount;
                                })
                                .catch(err => {
                                    window.location.reload(); 
                                });
                            " class="w-full text-left px-4 py-2 text-sm flex items-center space-x-2 transition-colors"
                            :class="disliked ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/10' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span x-text="disliked ? 'Undo Not For Me' : 'Not for me'"></span>
                            </button>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Desktop Avatar (Hidden on Mobile) -->
            @if($share->is_deleted)
                <div class="hidden md:block h-14 w-14 border-2 border-white dark:border-gray-700 shadow-sm rounded-full overflow-hidden shrink-0">
                    <img src="{{ asset('icons/reso.png') }}" class="w-full h-full object-cover" />
                </div>
            @else
                <a href="{{ route('profile.show', $share->user->name) }}" wire:navigate x-on:click.stop class="shrink-0">
                    <x-user-avatar :user="$share->user" class="hidden md:block h-14 w-14 border-2 border-white dark:border-gray-700 shadow-sm" />
                </a>
            @endif

            <!-- Main Content Area (Stretches full width on mobile) -->
            <div class="flex-1 min-w-0 w-full">
                <!-- Desktop Header Row (Hidden on Mobile) -->
                <div class="hidden md:flex items-center">
                    @if($share->is_deleted)
                        <div class="text-left">
                            <span class="font-bold text-gray-400 dark:text-gray-500">[deleted]</span>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"> &middot; {{ $share->created_at->diffForHumans() }}</span>
                        </div>
                    @else
                        <div class="text-left">
                            <a href="{{ route('profile.show', $share->user->name) }}" wire:navigate x-on:click.stop class="font-bold text-gray-900 dark:text-white hover:text-custom-mid-blue transition-colors">{{ $share->user->name }}</a>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"> &middot; {{ $share->created_at->diffForHumans() }}</span>
                        @if($share->type === 'recommendation_request')
                            <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/50 uppercase tracking-wider">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4.02-5.53Z" clip-rule="evenodd" />
                                </svg>
                                SEEKING RECOMMENDATIONS
                            </span>
                            @endif
                        </div>
                        <div x-data="{ open: false }" class="relative ml-auto" x-on:click.stop>
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg z-50 py-1 ring-1 ring-black/5 dark:ring-white/10" style="display: none;" x-transition>
                                @if ($share->user->is(auth()->user()))
                                    <!-- Edit Button -->
                                    <button @click="editing = true; open = false;" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        Edit Caption
                                    </button>
                                    
                                    <form @submit.prevent="
                                        if (!confirm('Are you sure you want to delete this share?')) return;
                                        fetch('{{ route('shares.destroy', $share) }}', {
                                            method: 'DELETE',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        })
                                        .then(response => {
                                            if (response.ok) {
                                                window.reloadWithScroll();
                                            }
                                        })
                                    ">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            Delete Share
                                        </button>
                                    </form>
                                @else
                                    <!-- Not for me / Dislike -->
                                    <button @click="
                                        open = false;
                                        disliked = !disliked;
                                        if (disliked) {
                                            if (typeof liked !== 'undefined' && liked) {
                                                liked = false;
                                                likesCount--;
                                            }
                                        }
                                        
                                        fetch('{{ route('shares.dislike', $share) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        })
                                        .then(response => {
                                            if (!response.ok) throw new Error('Failed');
                                            return response.json();
                                        })
                                        .then(data => {
                                            disliked = data.disliked;
                                            liked = data.liked;
                                            likesCount = data.likesCount;
                                        })
                                        .catch(err => {
                                            window.location.reload(); 
                                        });
                                    " class="w-full text-left px-4 py-2 text-sm flex items-center space-x-2 transition-colors"
                                    :class="disliked ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/10' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span x-text="disliked ? 'Undo Not For Me' : 'Not for me'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Display Mode -->
                <p class="mt-2 text-gray-800 dark:text-gray-100 text-base md:text-lg leading-snug md:leading-relaxed break-words" x-show="!editing" x-text="editCaption"></p>

                <!-- Edit Mode -->
                <div x-show="editing" class="mt-2" style="display: none;" x-on:click.stop>
                    <textarea x-model="editCaption" class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-black text-gray-900 dark:text-white focus:border-custom-mid-blue focus:ring focus:ring-custom-mid-blue/20 transition-shadow" rows="2"></textarea>
                    <div class="flex justify-end space-x-2 mt-2">
                        <button @click="editing = false; editCaption = originalCaption" class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                        <button @click="
                            fetch('{{ route('shares.update', $share) }}', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ 
                                    caption: editCaption
                                })
                            })
                            .then(async response => {
                                if (!response.ok) {
                                    const contentType = response.headers.get('content-type');
                                    if (contentType && contentType.includes('application/json')) {
                                        const data = await response.json();
                                        throw new Error(data.message || data.error || 'Server Error (' + response.status + ')');
                                    } else {
                                        throw new Error('Request failed (' + response.status + ')');
                                    }
                                }
                                return response.json();
                            })
                            .then(data => {
                                window.reloadWithScroll();
                            })
                            .catch(error => {
                                alert(error.message);
                                console.error(error);
                            })
                        " class="px-3 py-1 text-sm bg-custom-mid-blue text-white rounded-lg hover:bg-custom-dark-blue">Save</button>
                    </div>
                </div>

                @if(!$share->is_deleted)
                <div class="mt-3 md:mt-4 relative rounded-2xl md:rounded-3xl p-4 md:p-6 group">
                     {{-- Background blur/gradient wrapper to allow child elements (like the playlist dropdown) to overflow --}}
                     <div class="absolute inset-0 rounded-2xl md:rounded-3xl overflow-hidden pointer-events-none">
                         <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110 transition-transform duration-700 group-hover:scale-125" style="background-image: url('{{ $share->song->album_art_url }}');"></div>
                         <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                     </div>
                     <div class="relative flex items-center space-x-4 md:space-x-6 z-10">
                        <a href="{{ $share->song->spotify_url && $share->song->spotify_url !== '#' ? $share->song->spotify_url : 'https://open.spotify.com/track/'.$share->song->spotify_track_id }}" target="_blank" rel="noopener noreferrer" class="shrink-0 hover:opacity-90 transition-opacity" @click.stop>
                            <img src="{{ $share->song->album_art_url }}" alt="Album Art" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl md:rounded-2xl shadow-xl transition-transform duration-300 group-hover:scale-105">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ $share->song->spotify_url && $share->song->spotify_url !== '#' ? $share->song->spotify_url : 'https://open.spotify.com/track/'.$share->song->spotify_track_id }}" target="_blank" rel="noopener noreferrer" class="hover:underline hover:opacity-90 transition-opacity block" @click.stop>
                                <p class="text-xl md:text-2xl font-bold text-white truncate drop-shadow-md">{{ $share->song->track_name }}</p>
                                <p class="text-base md:text-lg text-gray-200 truncate drop-shadow-sm">{{ $share->song->artist_name }}</p>
                            </a>
                            
                            <div class="flex items-center flex-wrap gap-3 mt-2 md:mt-3">
                                {{-- Inline player toggle button instead of external link --}}
                                @php
                                    $isLinked = auth()->check() && auth()->user()->spotify_token;
                                    $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
                                @endphp
                                
                                <button type="button"
                                    x-on:click.stop.prevent="
                                        playerOpen = !playerOpen;
                                        console.log('isPremium:', isPremium, 'isSupported:', isSupported);
                                        if (isPremium && isSupported) {
                                            if (playerOpen) {
                                                if(isReady && window.toggleSpotifyPlayer) {
                                                    const meta = { 
                                                        name: '{{ addslashes($share->song->track_name) }}', 
                                                        artist: '{{ addslashes($share->song->artist_name) }}', 
                                                        art: '{{ $share->song->album_art_url }}',
                                                        previewUrl: '{{ $share->song->preview_url }}' 
                                                    };
                                                    window.toggleSpotifyPlayer('spotify:track:{{ $share->song->spotify_track_id }}', meta);
                                                }
                                            } else {
                                                if(window.toggleSpotifyPlayer) {
                                                    window.toggleSpotifyPlayer('spotify:track:{{ $share->song->spotify_track_id }}', null);
                                                }
                                            }
                                        }
                                    "
                                    :title="isPremium && isSupported ? (isReady ? 'Play track' : 'Connecting to Spotify...') : 'Play 30s preview'"
                                    :class="isPremium && isSupported && !isReady ? 'opacity-40 grayscale cursor-not-allowed' : 'hover:scale-110 hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)] cursor-pointer'"
                                    class="shrink-0 flex items-center justify-center transition-all duration-300 relative">
                                     {{-- Spotify logo --}}
                                     <svg class="w-8 h-8 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                 </button>

                                <!-- Add to Playlist Button with Dropdown -->
                                <div class="relative inline-block shrink-0" x-data="{ isDropdownOpen: false }">
                                    <button type="button" 
                                        @click.prevent.stop="isDropdownOpen = !isDropdownOpen"
                                        title="Add to Playlist" 
                                        class="hover:scale-110 flex items-center justify-center transition-transform hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.4)] bg-white/20 rounded-full p-1 w-8 h-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div x-show="isDropdownOpen"
                                         @click.away="isDropdownOpen = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-48 bg-white dark:bg-[#141414] rounded-2xl shadow-xl border border-gray-100 dark:border-white/10 overflow-hidden z-50 py-1"
                                         style="display: none;">
                                         
                                         <!-- Add to Reso Playlist -->
                                         <button type="button"
                                                 @click.prevent.stop="
                                                     $dispatch('open-reso-playlist-modal', { songId: '{{ $share->song->spotify_track_id }}', trackName: '{{ addslashes($share->song->track_name) }}' });
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
                                                         $dispatch('open-spotify-playlist-modal', { trackUri: '{{ 'spotify:track:' . $share->song->spotify_track_id }}', trackName: '{{ addslashes($share->song->track_name) }}' })
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

                                @if ($share->song->youtube_url)
                                    <a href="{{ $share->song->youtube_url }}" x-on:click.stop target="_blank" title="Watch on YouTube" class="shrink-0 flex items-center justify-center hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,0,0,0.6)]">
                                        <svg class="w-8 h-8 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inline Spotify Embed Player — expands below song card on the Feed --}}
                <div x-show="playerOpen && (!isPremium || !isSupported)"
                     x-transition
                     class="mt-3 rounded-2xl overflow-hidden bg-black/40 border border-white/10"
                     style="display:none;">
                    <iframe class="share-spotify-frame"
                        x-bind:src="(playerOpen && (!isPremium || !isSupported)) ? 'https://open.spotify.com/embed/track/{{ $share->song->spotify_track_id }}?utm_source=generator&theme=0' : ''"
                        width="100%"
                        height="80"
                        frameborder="0"
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy"
                        style="border-radius:12px; display:block;">
                    </iframe>
                </div>
                @endif

                @if(!$share->is_deleted)
                <div class="mt-3 md:mt-5 border-t border-gray-100/50 pt-2 md:pt-3" x-on:click.stop>
                    
                    <div class="grid grid-cols-3 gap-1 md:gap-2 w-full mt-1 md:mt-2">
                        <!-- Like Zone -->
                        <form @submit.prevent="
                            liked = !liked;
                            liked ? likesCount++ : likesCount--;
                            if (liked) {
                                if (typeof disliked !== 'undefined' && disliked) {
                                    disliked = false;
                                    // dislikesCount--; // Optional: depending on if we show dislike count
                                }
                            }
                            
                            fetch('{{ route('shares.like', $share) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    // Revert on error
                                    liked = !liked;
                                    liked ? likesCount++ : likesCount--;
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.disliked !== undefined) disliked = data.disliked;
                                // dislikesCount = data.dislikesCount;
                            })
                            .catch(err => {
                                console.error(err);
                                // Revert on error
                                liked = !liked;
                                liked ? likesCount++ : likesCount--;
                            });
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
                                bookmarked = !bookmarked;
                                fetch('{{ route('shares.bookmark', $share) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                })
                                .then(response => {
                                    if (!response.ok) bookmarked = !bookmarked; // Revert
                                    return response.json();
                                })
                                .catch(err => {
                                    bookmarked = !bookmarked; // Revert
                                });
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
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <div x-on:click.stop>
                    @php
                        $hasComments = $totalCount ? $totalCount > 0 : $share->comments->isNotEmpty();
                        $previews = $previewComments ?? ($hasComments ? $share->comments->sortByDesc('created_at')->take(3) : collect());
                    @endphp

                    @if ($previews->isNotEmpty())
                        <div class="mt-4 space-y-2" x-show="!commentsOpen">
                            @foreach ($previews as $preview)
                                <div class="flex items-start space-x-3 cursor-pointer hover:bg-white/60 dark:hover:bg-white/10 p-3 rounded-2xl transition shadow-sm border border-gray-100/50 dark:border-white/5" @click="commentsOpen = true">
                                     <a href="{{ route('profile.show', $preview->user->name) }}" wire:navigate x-on:click.stop class="shrink-0">
                                         <x-user-avatar :user="$preview->user" class="h-10 w-10 mt-0.5 border-2 border-white dark:border-gray-700 shadow-sm" />
                                     </a>
                                    <div class="flex-1" x-data="{
                                        songId: '{{ $preview->getEmbeddedSongId() }}',
                                        songData: null,
                                        playerOpen: false,
                                        isPlayingPreview: false,
                                        init() {
                                            if (this.songId) {
                                                fetch(`/search/tracks/${this.songId}`)
                                                    .then(r => r.json())
                                                    .then(data => { if (data.song) this.songData = data.song; })
                                                    .catch(err => console.error('Failed to fetch comment preview song:', err));
                                            }
                                        }
                                    }">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $preview->user->name }}</p>
                                        <p class="text-gray-700 dark:text-gray-300 text-base leading-snug mt-1">{{ Str::limit($preview->getCleanBody(), 120) }}</p>
                                        
                                        {{-- Mini Song Card for Recommendations --}}
                                        <template x-if="songData">
                                            <div class="mt-3 relative rounded-2xl p-4 group/card">
                                                {{-- Background blur/gradient wrapper to allow child elements (like the playlist dropdown) to overflow --}}
                                                <div class="absolute inset-0 rounded-2xl overflow-hidden pointer-events-none">
                                                    <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-90 transform scale-110 transition-transform duration-700 group-hover/card:scale-125" :style="`background-image: url('${songData.album_art_url || '/images/default-album-art.png'}');`"></div>
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                                                </div>
                                                
                                                <div class="relative flex items-center space-x-4 z-10">
                                                    <img :src="songData.album_art_url || '/images/default-album-art.png'" alt="Album Art" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl shadow-xl transition-transform duration-300 group-hover/card:scale-105 shrink-0">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-lg font-bold text-white truncate drop-shadow-md" x-text="songData.track_name"></p>
                                                        <p class="text-sm text-gray-200 truncate drop-shadow-sm" x-text="songData.artist_name"></p>
                                                        
                                                        <div class="flex items-center space-x-3 mt-2">
                                                            @php
                                                                $isLinked = auth()->check() && auth()->user()->spotify_token;
                                                                $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
                                                            @endphp
                                                            <button type="button"
                                                                x-data="{ isReady: window.isSpotifyReady || false, isPremium: {{ $isPremium ? 'true' : 'false' }} }"
                                                                @spotify-ready.window="isReady = true"
                                                                @spotify-not-ready.window="isReady = false"
                                                                x-on:click.stop.prevent="
                                                                    const meta = { 
                                                                        name: songData.track_name, 
                                                                        artist: songData.artist_name, 
                                                                        art: songData.album_art_url, 
                                                                        previewUrl: songData.preview_url || '' 
                                                                    };

                                                                    if (isPremium) {
                                                                        // Premium: Only play if ready
                                                                        if(isReady && window.toggleSpotifyPlayer) {
                                                                            window.toggleSpotifyPlayer('spotify:track:' + songData.spotify_track_id, meta);
                                                                        }
                                                                    } else {
                                                                        // Free/Unlinked: Always allowed to toggle preview via persistent web player
                                                                        if(window.toggleSpotifyPlayer) {
                                                                            window.toggleSpotifyPlayer('spotify:track:' + songData.spotify_track_id, meta);
                                                                        }
                                                                    }
                                                                "
                                                                :title="isPremium ? (isReady ? 'Play on Spotify' : 'Connecting to Spotify...') : 'Play 30s preview'"
                                                                :class="isPremium && !isReady ? 'opacity-40 grayscale cursor-not-allowed' : 'hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(30,215,96,0.6)] cursor-pointer'"
                                                                class="shrink-0 flex items-center justify-center transition-all duration-300 relative">
                                                                <svg class="w-7 h-7 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                                            </button>
                                        
                                                            <!-- Add to Playlist Button -->
                                                            <div class="relative inline-block" x-data="{ isDropdownOpen: false }">
                                                                <button type="button" 
                                                                    @click.prevent.stop="isDropdownOpen = !isDropdownOpen"
                                                                    title="Add to Playlist" 
                                                                    class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.4)] bg-white/20 rounded-full p-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
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
                                        
                                                            <!-- YouTube Link (if available) -->
                                                            <template x-if="songData.youtube_url">
                                                                <a :href="songData.youtube_url" x-on:click.stop target="_blank" title="Watch on YouTube" class="hover:scale-110 transition-transform hover:drop-shadow-[0_0_10px_rgba(255,0,0,0.6)]">
                                                                    <svg class="w-7 h-7 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                                                </a>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        {{-- 30s Preview player panel --}}
                                        <div x-show="playerOpen" x-on:click.stop x-transition class="mt-2 bg-black/60 backdrop-blur-md border border-white/10 rounded-xl p-3 flex flex-col gap-2" style="display:none;">
                                            <template x-if="songData?.preview_url">
                                                <div class="flex items-center gap-3 w-full">
                                                    <button @click="const a = $refs.miniAudio; if(a.paused){a.play(); isPlayingPreview=true;}else{a.pause(); isPlayingPreview=false;}"
                                                        class="w-8 h-8 shrink-0 rounded-full bg-[#1DB954] flex items-center justify-center text-black hover:scale-105 transition-transform">
                                                        <svg x-show="!isPlayingPreview" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 ml-0.5"><path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd"/></svg>
                                                        <svg x-show="isPlayingPreview" style="display:none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25Zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25Z" clip-rule="evenodd"/></svg>
                                                    </button>
                                                    <span class="text-xs text-gray-300">30s Preview</span>
                                                    <audio x-ref="miniAudio" :src="songData.preview_url" @play="isPlayingPreview = true" @pause="isPlayingPreview = false" @ended="isPlayingPreview = false"></audio>
                                                </div>
                                            </template>
                                            <template x-if="!songData?.preview_url">
                                                <span class="text-xs text-gray-400">No preview available for this track.</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div x-show="commentsOpen" x-transition class="mt-4 px-4 pl-6 border-l-2 border-gray-100" style="display: none;" x-data="{ newComment: '' }">
                        @if(!$share->is_deleted)
                        <form @submit.prevent="
                            fetch('{{ route('shares.comments.store', $share) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    body: commentSong ? newComment + ' [SONG:' + commentSong.id + ']' : newComment
                                })
                            })
                            .then(response => response.text())
                            .then(html => {
                                let wrapper = $el.closest('[x-data]');
                                let commentSection = wrapper.querySelector('.space-y-4');
                                commentSection.insertAdjacentHTML('afterbegin', html); // Add to top
                                newComment = '';
                                commentSong = null;
                                
                                // Remove placeholder if it exists
                                let placeholder = commentSection.querySelector('.text-sm.text-gray-500.text-center.py-4');
                                if (placeholder && placeholder.textContent.includes('No comments yet')) {
                                    placeholder.remove();
                                }
                                
                                // Update comment count
                                let countEl = wrapper.querySelector('span.text-sm.font-bold.text-gray-600');
                                if (countEl && !isNaN(parseInt(countEl.textContent))) {
                                    countEl.textContent = parseInt(countEl.textContent) + 1;
                                }
                            })
                        " class="space-y-4 mb-6">
                            @csrf
                            
                            <!-- Search Overlay (Mini) -->
                            <div x-show="isSearching" class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-3 border border-gray-200 dark:border-gray-700 shadow-inner" x-transition>
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" x-model="commentSearch" 
                                        @input.debounce.300ms="if(commentSearch.length > 2) { 
                                            fetch('{{ route('spotify.search') }}?query=' + encodeURIComponent(commentSearch))
                                            .then(r => r.json()).then(d => commentResults = d)
                                        }"
                                        class="flex-1 text-sm rounded-lg border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-white" placeholder="Search track to recommend...">
                                    <button type="button" @click="isSearching = false; commentSearch = ''; commentResults = []" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 uppercase text-[10px] font-bold">Close</button>
                                </div>
                                <div class="max-h-40 overflow-y-auto space-y-1">
                                    <template x-for="track in commentResults" :key="track.id">
                                        <div @click="commentSong = track; isSearching = false; commentSearch = ''; commentResults = []" 
                                            class="p-2 flex items-center gap-3 hover:bg-white dark:hover:bg-gray-700 rounded-lg cursor-pointer transition">
                                            <img :src="track.album.images[0]?.url" class="w-8 h-8 rounded shadow-sm">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="track.name"></div>
                                                <div class="text-[10px] text-gray-500 truncate" x-text="track.artists[0].name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Attached Track Preview -->
                            <div x-show="commentSong" class="flex items-center gap-3 bg-blue-50 dark:bg-blue-900/20 p-2 rounded-xl border border-blue-100 dark:border-blue-800/50" x-transition>
                                <img :src="commentSong?.album.images[0]?.url" class="w-10 h-10 rounded-lg shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-blue-900 dark:text-blue-100 truncate" x-text="commentSong?.name"></div>
                                    <div class="text-[10px] text-blue-700 dark:text-blue-300" x-text="commentSong?.artists[0].name"></div>
                                </div>
                                <button type="button" @click="commentSong = null" class="text-blue-400 hover:text-red-500 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                                </button>
                            </div>

                            <div class="flex items-center space-x-3">
                                <x-user-avatar :user="auth()->user()" class="h-8 w-8 border border-gray-200 dark:border-gray-700" />
                                <div class="relative flex-1">
                                    <x-text-input x-model="newComment" name="body" class="block w-full text-sm rounded-full bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 transition-colors dark:text-white pr-10" placeholder="Write a comment..." required />
                                    <button type="button" @click="isSearching = true" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-custom-mid-blue transition-colors" 
                                            title="Search for songs">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                            <path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A2.48 2.48 0 0 0 4.5 14a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 2.5-2.5V6.236l9-1.8V12.114A2.48 2.48 0 0 0 14.5 12a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 2.5-2.5V3z" />
                                        </svg>
                                    </button>
                                </div>
                                <button type="submit" class="bg-custom-mid-blue text-white rounded-full p-2 hover:bg-custom-dark-blue transition-colors shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 004.836 9.25h4.288a.75.75 0 010 1.5H4.836a1.5 1.5 0 00-1.144 1.086l-1.414 4.925a.75.75 0 00.826.95 28.89 28.89 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z" />
                                    </svg>
                                </button>
                            </div>
                        @endif

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
