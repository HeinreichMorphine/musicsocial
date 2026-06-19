<x-app-layout pageTitle="{{ $user->name }}'s Song Shelf">
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
            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                @include('profile.partials.header', ['user' => $user])

                <div class="bg-white dark:bg-black rounded-3xl p-6 md:p-8 border border-gray-100 dark:border-white/10 shadow-sm" x-data="shelfManager(@js($shelfTracks), @js(Auth::id() === $user->id))">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-indigo-500/10 rounded-2xl border border-indigo-500/20">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 15v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 18v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 21v-1.122" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Music Identity</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">A curated shelf of songs that define {{ $user->name }}'s taste.</p>
                            </div>
                        </div>
                        
                        <template x-if="isOwner">
                            <button @click="isEditing = !isEditing" 
                                    class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2"
                                    :class="isEditing ? 'bg-indigo-600 text-white shadow-indigo-500/20' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                                <svg x-show="!isEditing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg x-show="isEditing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span x-text="isEditing ? 'Done Editing' : 'Edit Shelf'"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Add Track Section -->
                    <div x-show="isEditing" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mb-10 p-6 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-3xl border border-indigo-100 dark:border-indigo-500/20">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-bold text-indigo-900 dark:text-indigo-300">Add to your shelf <span class="text-sm font-normal text-indigo-600 dark:text-indigo-400" x-text="'(' + tracks.length + '/10)'"></span></h4>
                        </div>
                        
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   @input.debounce.300ms="performSearch"
                                   placeholder="Search Spotify for a song to add..." 
                                   class="block w-full pl-11 pr-4 py-3 bg-white dark:bg-black border border-indigo-100 dark:border-indigo-500/20 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                        </div>

                        <!-- Search Results -->
                        <div x-show="searchResults.length > 0" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                            <template x-for="result in searchResults" :key="result.id">
                                <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 hover:border-indigo-300 transition-colors shadow-sm">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img :src="result.album?.images[result.album?.images.length-1]?.url || '{{ asset('images/default-album.png') }}'" class="w-10 h-10 rounded-lg object-cover">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="result.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="result.artists?.map(a => a.name).join(', ') || 'Unknown Artist'"></p>
                                        </div>
                                    </div>
                                    <button @click="addTrack(result)" 
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 rounded-lg transition-colors flex items-center justify-center min-w-[40px] min-h-[40px]"
                                            :disabled="tracks.some(t => t.id === result.id) || tracks.length >= 10 || addingTrackId === result.id">
                                        <!-- Loading Spinner -->
                                        <svg x-show="addingTrackId === result.id" class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <!-- Plus Icon (Initial State) -->
                                        <svg x-show="addingTrackId !== result.id && !tracks.some(t => t.id === result.id)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <!-- Check Icon (Added State) -->
                                        <svg x-show="addingTrackId !== result.id && tracks.some(t => t.id === result.id)" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Shelf Grid -->
                    <div x-show="tracks.length === 0" class="text-center py-16 bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800">
                        <p class="text-gray-500 dark:text-gray-400">This shelf is currently empty.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 md:gap-8">
                        <template x-for="track in tracks" :key="track.id">
                            <div class="group relative bg-white dark:bg-gray-900 rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                                <!-- Reorder & Remove Buttons (Editing Mode) -->
                                <template x-if="isEditing">
                                    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-4 bg-black/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="flex gap-2">
                                            <button @click="moveTrack(track.id, 'left')" 
                                                    class="p-2 bg-white/20 hover:bg-white/40 text-white rounded-full transition-all"
                                                    :class="tracks.indexOf(track) === 0 ? 'opacity-30 cursor-not-allowed' : ''"
                                                    :disabled="tracks.indexOf(track) === 0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <button @click="moveTrack(track.id, 'right')" 
                                                    class="p-2 bg-white/20 hover:bg-white/40 text-white rounded-full transition-all"
                                                    :class="tracks.indexOf(track) === tracks.length - 1 ? 'opacity-30 cursor-not-allowed' : ''"
                                                    :disabled="tracks.indexOf(track) === tracks.length - 1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </div>
                                        <button @click="removeTrack(track.id)" 
                                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold shadow-lg transform hover:scale-105 transition-all">
                                            Remove Track
                                        </button>
                                    </div>
                                </template>

                                <div class="aspect-square relative overflow-hidden">
                                    <img :src="track.album.images[0]?.url || '{{ asset('images/default-album.png') }}'" 
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    
                                    <div x-show="!isEditing" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                                         <a :href="track.external_urls.spotify" target="_blank" class="p-3.5 bg-white/20 backdrop-blur-md rounded-full text-white hover:bg-white/40 transition-all transform hover:scale-110 shadow-2xl">
                                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.5 17.5c-.2.3-.6.4-.9.2-2.3-1.4-5.1-1.7-8.5-.9-.3.1-.7-.1-.8-.4-.1-.3.1-.7.4-.8 3.7-.8 6.8-.5 9.4 1.1.3.1.4.5.2.8zm1.5-3.3c-.3.4-.8.6-1.2.3-2.6-1.6-6.6-2.1-9.7-1.1-.5.1-1-.2-1.1-.7-.1-.5.2-1 .7-1.1 3.6-1.1 8-0.5 11 1.4.4.2.5.8.3 1.2zm.1-3.4c-3.1-1.9-8.3-2-11.3-1.1-.5.1-1-.1-1.2-.6-.1-.5.1-1 .6-1.2 3.5-1.1 9.3-0.9 12.9 1.3.4.3.6.9.3 1.4-.3.4-.9.6-1.3.2z"/></svg>
                                         </a>
                                    </div>
                                </div>
                                <div class="p-5">
                                    <h4 class="font-bold text-gray-900 dark:text-white truncate text-lg" :title="track.name" x-text="track.name"></h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1" x-text="track.artists.map(a => a.name).join(', ')"></p>
                                </div>
                            </div>
                        </template>
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

    <script>
        function shelfManager(initialTracks, isOwner) {
            return {
                tracks: initialTracks,
                isOwner: isOwner,
                isEditing: false,
                searchQuery: '',
                searchResults: [],
                isSearching: false,
                addingTrackId: null,

                async performSearch() {
                    if (this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }
                    this.isSearching = true;
                    try {
                        const response = await fetch(`/search/tracks?query=${encodeURIComponent(this.searchQuery)}`);
                        if (response.ok) {
                            const data = await response.json();
                            this.searchResults = Array.isArray(data) ? data : [];
                        }
                    } catch (error) {
                        console.error('Search failed:', error);
                    } finally {
                        this.isSearching = false;
                    }
                },

                async addTrack(track) {
                    if (this.tracks.length >= 10) {
                        alert('Your shelf is full (max 10 songs).');
                        return;
                    }
                    this.addingTrackId = track.id;
                    try {
                        const response = await fetch('/shelf/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ song_id: track.id })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.tracks.push(track);
                            this.searchQuery = '';
                            this.searchResults = [];
                        } else {
                            const data = await response.json();
                            alert(data.error || 'Failed to add track.');
                        }
                    } catch (error) {
                        console.error('Add failed:', error);
                    } finally {
                        this.addingTrackId = null;
                    }
                },

                async removeTrack(trackId) {
                    if (!confirm('Remove this track from your shelf?')) return;
                    
                    try {
                        const response = await fetch(`/shelf/${trackId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            this.tracks = this.tracks.filter(t => t.id !== trackId);
                        } else {
                            alert('Failed to remove track.');
                        }
                    } catch (error) {
                        console.error('Remove failed:', error);
                    }
                },

                moveTrack(trackId, direction) {
                    const index = this.tracks.findIndex(t => t.id === trackId);
                    if (index === -1) return;

                    const newTracks = [...this.tracks];
                    if (direction === 'left' && index > 0) {
                        [newTracks[index], newTracks[index - 1]] = [newTracks[index - 1], newTracks[index]];
                    } else if (direction === 'right' && index < newTracks.length - 1) {
                        [newTracks[index], newTracks[index + 1]] = [newTracks[index + 1], newTracks[index]];
                    }

                    this.tracks = newTracks;
                    this.saveOrder();
                },

                async saveOrder() {
                    try {
                        const songIds = this.tracks.map(t => t.id);
                        await fetch('/shelf/reorder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ song_ids: songIds })
                        });
                    } catch (error) {
                        console.error('Reorder failed:', error);
                    }
                }
            }
        }
    </script>
</x-app-layout>
