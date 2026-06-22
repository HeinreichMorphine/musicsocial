<x-app-layout :pageTitle="$playlist->name">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <!-- Left Sidebar -->
            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                    <div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-4 border border-white/40 dark:border-white/10 shadow-xl transition-colors duration-300">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-6">
                
                <a href="{{ route('playlists.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors mb-2 group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Playlists
                </a>

                <!-- Header Card -->
                <div class="bg-white dark:bg-black rounded-3xl p-6 md:p-8 border border-gray-100 dark:border-white/10 shadow-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/5 to-transparent pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col md:flex-row gap-6 md:gap-8 items-start md:items-center">
                        <div class="relative group">
                            <div class="w-24 h-24 md:w-40 md:h-40 rounded-3xl bg-gradient-to-br from-indigo-600 to-indigo-900 flex items-center justify-center shadow-xl flex-shrink-0 border border-white/10 overflow-hidden relative">
                                @if($playlist->cover_image_url)
                                    <img src="{{ $playlist->cover_image_url }}" class="w-full h-full object-cover" alt="{{ $playlist->name }}">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center z-10">
                                        <div class="px-4 text-center w-full">
                                            <h4 class="text-white text-xl md:text-2xl font-black opacity-30 m-0 leading-tight line-clamp-2">
                                                {{ $playlist->name }}
                                            </h4>
                                        </div>
                                    </div>
                                @endif

                                @if($collab->role === 'owner')
                                    <button x-data @click="$dispatch('open-modal', 'edit-playlist-cover')" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <div class="flex items-center gap-2 mb-2">
                                @if(str_contains($playlist->description, 'Imported'))
                                    <span class="text-emerald-500 font-bold tracking-wider text-[10px] uppercase flex items-center gap-2 bg-emerald-500/10 w-fit px-2 py-0.5 rounded-full">
                                        Imported
                                    </span>
                                @else
                                    <span class="text-indigo-500 font-bold tracking-wider text-[10px] uppercase flex items-center gap-2 bg-indigo-500/10 w-fit px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        Playlist
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">{{ $playlist->name }}</h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm max-w-xl">{{ $playlist->description ?? 'Collaborate and build the perfect vibe together.' }}</p>
                            
                            <div class="mt-6 flex flex-wrap items-center gap-4">
                                <div class="flex -space-x-2">
                                    @foreach($playlist->collaborators->where('status', 'accepted') as $collaborator)
                                        <x-user-avatar :user="$collaborator->user" 
                                            class="w-9 h-9 border-2 border-white dark:border-black shadow-sm hover:scale-110 transition-transform cursor-help" 
                                            title="{{ $collaborator->user->name }} ({{ ucfirst($collaborator->role) }})" />
                                    @endforeach
                                </div>
                                <button x-data @click="$dispatch('open-modal', 'invite-collaborator')" class="text-xs bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-bold px-4 py-2 rounded-full transition border border-gray-200 dark:border-gray-700 flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Invite
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Song Search Bar -->
                <div x-data="playlistApp({{ $playlist->id }}, {{ $playlist->songs->sortByDesc('created_at')->values()->toJson() }})">
                    <div class="relative max-w-xl">
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch"
                               class="w-full bg-white dark:bg-black border border-gray-100 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl px-5 py-4 pl-12 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition placeholder-gray-500"
                               placeholder="Search for a song to add...">
                        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="searchResults.length > 0" @click.away="searchResults = []" x-transition class="mt-2 bg-white dark:bg-black border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden shadow-2xl max-h-80 overflow-y-auto z-50 absolute w-full custom-scrollbar">
                            <template x-for="track in searchResults" :key="track.id">
                                <div class="flex items-center justify-between p-3 border-b border-gray-50 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <div class="flex items-center gap-4">
                                        <img :src="track.album.images[0]?.url || '/images/default-album.png'" class="w-11 h-11 rounded-lg object-cover shadow-sm">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-gray-900 dark:text-white font-bold truncate text-sm" x-text="track.name"></h4>
                                            <p class="text-gray-500 text-xs truncate" x-text="track.artists.map(a => a.name).join(', ')"></p>
                                        </div>
                                    </div>
                                    <button @click="addSong(track)" :disabled="isAdding" class="ml-4 text-xs font-bold px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow transition shrink-0 flex items-center justify-center min-w-[64px]">
                                         <span x-show="!isAdding">Add</span>
                                         <svg x-show="isAdding" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                         </svg>
                                     </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Playlist Songs -->
                    <div class="mt-8 bg-white dark:bg-black rounded-3xl p-4 sm:p-6 border border-gray-100 dark:border-white/10 shadow-sm relative overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tracklist</h3>
                            <span class="text-xs text-gray-500 font-bold uppercase tracking-widest" x-text="playlistSongs.length + ' Tracks'"></span>
                        </div>

                        <div x-show="playlistSongs.length === 0" class="text-center py-16">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                            </div>
                            <p class="text-gray-400 text-sm">Playlist is empty. Add some tracks above!</p>
                        </div>

                        <div class="space-y-4" x-show="playlistSongs.length > 0">
                            <template x-for="ps in playlistSongs" :key="ps.id + '-' + ps.song_id">
                                <div x-data="trackRow(ps.song_id)" class="flex flex-col group p-2 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                                    <div class="flex items-center gap-4 w-full">
                                        <div class="flex-1 flex items-center gap-4 min-w-0">
                                            <div x-show="track" class="flex-1 flex items-center gap-4 min-w-0" style="display: none;">
                                                <img :src="track?.album_art_url || track?.album?.images?.[0]?.url" class="w-12 h-12 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <div class="text-gray-900 dark:text-white font-bold truncate text-sm" x-text="track?.track_name || track?.name"></div>
                                                        <template x-if="track?.spotify_track_id || track?.id">
                                                            <button type="button" 
                                                                @click.prevent.stop="window.toggleSpotifyPreview('ply-' + ps.song_id, (track?.spotify_track_id || track?.id))"
                                                                class="text-green-500 hover:scale-110 transition-transform">
                                                                <svg :id="'spe-icon-play-ply-' + ps.song_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                                                    <path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd" />
                                                                </svg>
                                                                <svg :id="'spe-icon-stop-ply-' + ps.song_id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4" style="display:none;">
                                                                    <path fill-rule="evenodd" d="M4.5 7.5a3 3 0 0 1 3-3h9a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9Z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <div class="text-gray-500 text-xs truncate" x-text="track?.artist_name || track?.artists?.[0]?.name"></div>
                                                </div>
                                            </div>
                                            <div x-show="!track" class="flex-1 flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-900 animate-pulse"></div>
                                                <div class="space-y-2 flex-1">
                                                    <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded animate-pulse w-1/2"></div>
                                                    <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded animate-pulse w-1/3"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-white/5 rounded-full border border-gray-100 dark:border-white/5 group-hover:bg-white dark:group-hover:bg-black transition-colors">
                                            <img :src="ps.added_by?.profile_picture_url || ps.added_by_user?.profile_picture_url || '{{ asset('icons/reso.png') }}'" class="h-5 w-5 rounded-full object-cover">
                                            <span class="text-gray-500 dark:text-gray-400 text-[10px] font-bold truncate max-w-[80px]" x-text="ps.added_by?.name || ps.added_by_user?.name"></span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter whitespace-nowrap w-12 text-right" x-text="formatDate(ps.created_at)"></div>
                                            <template x-if="canRemove(ps)">
                                                <button @click="removeSong(ps.song_id)" class="p-2 text-gray-400 hover:text-red-500 focus:text-red-500 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-all transform hover:scale-110" title="Remove song">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Spotify Embed Container for Playlist row --}}
                                    <div :id="'spe-container-ply-' + ps.song_id" 
                                         x-show="track"
                                         style="display:none; margin-top: 0.5rem; margin-bottom: 0.5rem; margin-left: 4rem; margin-right: 1rem;"
                                         x-on:click.stop>
                                        <iframe :id="'spe-frame-ply-' + ps.song_id"
                                            src=""
                                            width="100%" height="80"
                                            frameborder="0"
                                            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                            loading="lazy"
                                            style="border-radius:12px; display:block;">
                                        </iframe>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24 pt-4">
                    <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                    <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Modal -->
    <x-modal name="invite-collaborator" focusable>
        <form method="post" action="{{ route('playlists.invite', $playlist) }}" class="relative p-8 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-[2rem] border border-gray-100 dark:border-white/10 shadow-2xl transition-all overflow-hidden">
            @csrf
            <button type="button" x-on:click="$dispatch('close')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-all transform hover:scale-110 p-2">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Invite Collaborator</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Add a mutual follower to curate this playlist together.</p>
            </div>
            
            @if($following->isEmpty())
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 text-center">
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No mutual followers found.</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">To collaborate, you must follow each other. Ask your friends to follow you back so you can curate together!</p>
                </div>
            @else
                <div class="space-y-4" x-data="{
                    isOpen: false,
                    selectedId: '',
                    selectedName: 'Select a mutual follower...',
                    selectedAvatar: '',
                    users: [
                        @foreach($following as $user)
                            { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', avatar: '{{ $user->profile_picture_url }}' }{{ $loop->last ? '' : ',' }}
                        @endforeach
                    ],
                    selectUser(user) {
                        this.selectedId = user.id;
                        this.selectedName = user.name;
                        this.selectedAvatar = user.avatar;
                        this.isOpen = false;
                    }
                }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select a mutual friend</label>
                    
                    <input type="hidden" name="user_id" x-model="selectedId" required>

                    <div class="relative">
                        <button type="button" @click="isOpen = !isOpen" @click.away="isOpen = false"
                            class="flex items-center justify-between w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-xl py-3 px-4 shadow-sm transition focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <div class="flex items-center gap-3">
                                <img x-show="selectedAvatar" :src="selectedAvatar" 
                                     x-on:error="$el.src = '{{ asset('icons/reso.png') }}'"
                                     class="w-6 h-6 rounded-full object-cover">
                                <span x-text="selectedName" :class="{'text-gray-500': !selectedId}"></span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="isOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto custom-scrollbar" style="display: none;">
                            <template x-for="user in users" :key="user.id">
                                <div @click="selectUser(user)" 
                                     class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                     :class="{'bg-indigo-50 dark:bg-indigo-900/30': selectedId === user.id}">
                                    <img :src="user.avatar" 
                                         x-on:error="$el.src = '{{ asset('icons/reso.png') }}'"
                                         class="w-8 h-8 rounded-full object-cover">
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="user.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end items-center gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 transition transform hover:-translate-y-0.5">Send Invite</button>
                </div>
            @endif
        </form>
    </x-modal>

    <!-- Cover Modal -->
    <x-modal name="edit-playlist-cover" focusable>
        <form method="post" action="{{ route('playlists.update-cover', $playlist) }}" enctype="multipart/form-data" class="relative p-8 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-[2rem] border border-gray-100 dark:border-white/10 shadow-2xl transition-all overflow-hidden">
            @csrf
            <button type="button" x-on:click="$dispatch('close')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-all transform hover:scale-110 p-2">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Edit Cover Picture</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Upload a unique cover for your playlist.</p>
            </div>
            
            <div class="space-y-6">
                <div x-data="{ fileName: '' }" class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Cover Image</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl cursor-pointer bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-bold">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-400">PNG, JPG or WebP (max. 2MB)</p>
                                <p x-show="fileName" x-text="fileName" class="mt-4 text-sm text-indigo-500 font-bold"></p>
                            </div>
                            <input type="file" name="cover_image" class="hidden" @change="fileName = $event.target.files[0].name" accept="image/*" required />
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-end items-center gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 transition transform hover:-translate-y-0.5">Upload Cover</button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- Playlist Toast -->
    <div x-data="{ errorMessage: '' }" 
         @playlist-error.window="errorMessage = $event.detail; setTimeout(() => errorMessage = '', 4000)"
         x-show="errorMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm"
         style="display: none;">
        <div class="bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-red-400">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span x-text="errorMessage" class="font-bold"></span>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(79, 70, 229, 0.5); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(79, 70, 229, 0.8); }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('trackRow', (songId) => ({
                track: null,
                init() {
                    fetch('/search/tracks/' + songId)
                        .then(r => r.json())
                        .then(d => {
                            this.track = d.song || d;
                        })
                        .catch(e => console.error(e));
                }
            }));

            Alpine.data('playlistApp', (playlistId, initialSongs) => ({
                searchQuery: '',
                searchResults: [],
                isAdding: false,
                playlistSongs: initialSongs,
                currentUserId: {{ auth()->id() }},
                isOwner: '{{ $collab->role }}' === 'owner',

                async performSearch() {
                    if (this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }
                    try {
                        const response = await fetch(`/search/tracks?query=${encodeURIComponent(this.searchQuery)}`);
                        if (response.ok) {
                            this.searchResults = await response.json();
                        }
                    } catch (error) {
                        console.error('Search failed:', error);
                    }
                },

                async addSong(track) {
                    this.isAdding = true;
                    try {
                        const response = await fetch(`/playlists/${playlistId}/songs`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ spotify_track_id: track.id })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            // Prepend the new song to the list
                            this.playlistSongs.unshift(data.playlist_song);
                            this.searchQuery = '';
                            this.searchResults = [];
                        } else {
                            const data = await response.json();
                            this.$dispatch('playlist-error', data.error || 'Failed to add song.');
                        }
                    } catch (error) {
                        this.$dispatch('playlist-error', 'An error occurred.');
                    } finally {
                        this.isAdding = false;
                    }
                },

                async removeSong(songId) {
                    if (!confirm('Remove this song from the playlist?')) return;

                    try {
                        const response = await fetch(`/playlists/${playlistId}/songs/${songId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.playlistSongs = this.playlistSongs.filter(s => s.song_id !== songId);
                        } else {
                            alert('Failed to remove song.');
                        }
                    } catch (error) {
                        console.error('Removal failed:', error);
                    }
                },

                canRemove(ps) {
                    return this.isOwner || ps.added_by_user_id === this.currentUserId;
                },

                formatDate(dateStr) {
                    const date = new Date(dateStr);
                    const now = new Date();
                    const diff = Math.floor((now - date) / 1000);

                    if (diff < 60) return 'Just now';
                    if (diff < 3600) return Math.floor(diff / 60) + 'm';
                    if (diff < 86400) return Math.floor(diff / 3600) + 'h';
                    return Math.floor(diff / 86400) + 'd';
                }
            }));
        });
    </script>
</x-app-layout>
