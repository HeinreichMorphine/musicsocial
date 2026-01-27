<div x-data="{ 
    open: false,
    trackUri: '',
    trackName: '',
    playlists: [],
    loading: false,
    creating: false,
    newPlaylistName: '',
    
    init() {
        window.addEventListener('open-spotify-playlist-modal', event => {
            this.open = true;
            this.trackUri = event.detail.trackUri;
            this.trackName = event.detail.trackName;
            this.fetchPlaylists();
        });
    },

    fetchPlaylists() {
        this.loading = true;
        fetch('{{ route('spotify.playlists.index') }}')
            .then(res => res.json())
            .then(data => {
                this.playlists = data;
                this.loading = false;
            })
            .catch(err => {
                console.error(err);
                this.loading = false;
            });
    },

    addToPlaylist(playlistId) {
        fetch('{{ route('spotify.playlists.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                playlist_id: playlistId,
                track_uri: this.trackUri
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) {
                alert(data.error);
            } else {
                alert('Track added successfully!');
                this.open = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to add track.');
        });
    },

    createAndAdd() {
        if (!this.newPlaylistName) return;
        
        fetch('{{ route('spotify.playlists.create') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: this.newPlaylistName
            })
        })
        .then(res => res.json())
        .then(playlist => {
            if(playlist.error) {
                alert(playlist.error);
            } else {
                this.addToPlaylist(playlist.id);
                this.creating = false;
                this.newPlaylistName = '';
            }
        });
    }

}" x-show="open" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500/75 dark:bg-black/80 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div @click.away="open = false" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <div class="bg-white dark:bg-[#121212] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 drop-shadow-md" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Add to Spotify Playlist</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Adding <span class="font-bold text-gray-900 dark:text-white" x-text="trackName"></span> to your library.</p>
                            </div>

                            <div class="mt-4 max-h-60 overflow-y-auto space-y-2 custom-scrollbar pr-2">
                                <template x-if="loading">
                                    <p class="text-sm text-center py-4 text-gray-500 dark:text-gray-400">Loading playlists...</p>
                                </template>

                                <template x-if="!loading">
                                    <template x-for="playlist in playlists" :key="playlist.id">
                                        <button @click="addToPlaylist(playlist.id)" class="flex items-center w-full p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition group text-left">
                                            <img :src="playlist.images[0]?.url || 'https://via.placeholder.com/40'" class="w-10 h-10 rounded object-cover mr-3 bg-gray-200 dark:bg-gray-800">
                                            <div>
                                                <p class="font-bold text-sm text-gray-800 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400" x-text="playlist.name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="playlist.tracks.total + ' tracks'"></p>
                                            </div>
                                        </button>
                                    </template>
                                </template>
                            </div>

                            <!-- Create New Playlist Section -->
                             <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                                <template x-if="!creating">
                                    <button @click="creating = true" class="text-sm text-green-600 dark:text-green-400 font-bold hover:text-green-700 dark:hover:text-green-300 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Create New Playlist
                                    </button>
                                </template>
                                <template x-if="creating">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" x-model="newPlaylistName" placeholder="Playlist Name" class="text-sm rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white w-full focus:border-green-500 focus:ring-green-500">
                                        <button @click="createAndAdd()" class="bg-green-600 text-white px-3 py-2 rounded-md text-sm font-bold hover:bg-green-700">Create</button>
                                        <button @click="creating = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm">Cancel</button>
                                    </div>
                                </template>
                             </div>

                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-white/5">
                    <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-full bg-white dark:bg-white/5 px-3 py-2 text-sm font-bold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 hover:bg-gray-50 dark:hover:bg-white/10 sm:mt-0 sm:w-auto transition-all">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
