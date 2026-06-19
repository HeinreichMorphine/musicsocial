<div x-data="{
    open: false,
    songId: '',
    trackName: '',
    playlists: [],
    loading: false,
    adding: null,
    message: '',

    init() {
        window.addEventListener('open-reso-playlist-modal', event => {
            this.open     = true;
            this.songId   = event.detail.songId;
            this.trackName = event.detail.trackName;
            this.message  = '';
            this.adding   = null;
            this.fetchPlaylists();
        });
    },

    fetchPlaylists() {
        this.loading = true;
        fetch('{{ route('api.playlists.mine') }}')
            .then(r => r.json())
            .then(data => {
                this.playlists = data;
                this.loading   = false;
            })
            .catch(() => { this.loading = false; });
    },

    addToPlaylist(playlistId) {
        this.adding = playlistId;
        fetch('/playlists/' + playlistId + '/songs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ spotify_track_id: this.songId })
        })
        .then(r => r.json())
        .then(data => {
            this.adding = null;
            if (data.error) {
                this.message = '⚠ ' + data.error;
            } else {
                this.message = '✓ Added to playlist!';
                setTimeout(() => { this.open = false; this.message = ''; }, 1200);
            }
        })
        .catch(() => {
            this.adding = null;
            this.message = '⚠ Something went wrong.';
        });
    }

}" x-show="open" style="display:none;" class="relative z-50" role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/70 backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-10 flex items-end sm:items-center justify-center p-4">
        <div @click.away="open = false"
             x-show="open"
             x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="relative w-full sm:max-w-md bg-white dark:bg-[#141414] rounded-3xl border border-gray-200 dark:border-white/10 shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center space-x-3 px-5 pt-5 pb-4 border-b border-gray-100 dark:border-white/10">
                <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">Add to Reso Playlist</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        Adding <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="trackName"></span>
                    </p>
                </div>
                <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Status message --}}
            <div x-show="message" x-text="message" class="px-5 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20"></div>

            {{-- Playlist List --}}
            <div class="max-h-72 overflow-y-auto px-2 py-2">
                <template x-if="loading">
                    <div class="py-8 text-center text-sm text-gray-400">Loading your playlists…</div>
                </template>

                <template x-if="!loading && playlists.length === 0">
                    <div class="py-8 text-center text-sm text-gray-400">
                        No playlists found. <a href="{{ route('playlists.index') }}" class="text-indigo-500 underline">Create one</a>
                    </div>
                </template>

                <template x-if="!loading && playlists.length > 0">
                    <div>
                        <template x-for="pl in playlists" :key="pl.id">
                            <button @click="addToPlaylist(pl.id)"
                                    class="flex items-center w-full px-3 py-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-white/8 transition group text-left space-x-3">
                                {{-- Thumbnail --}}
                                <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-800 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <template x-if="pl.cover_image_url">
                                        <img :src="pl.cover_image_url" class="w-full h-full object-cover" onerror="this.src='{{ asset('icons/reso.png') }}'; this.onerror=null;">
                                    </template>
                                    <template x-if="!pl.cover_image_url">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/>
                                        </svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="pl.name"></p>
                                    <p class="text-xs text-gray-400" x-text="(pl.songs_count ?? 0) + ' songs'"></p>
                                </div>
                                <template x-if="adding === pl.id">
                                    <svg class="w-4 h-4 text-indigo-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                </template>
                                <template x-if="adding !== pl.id">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-indigo-500 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                </template>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 dark:border-white/10 flex justify-between items-center">
                <a href="{{ route('playlists.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Create New Playlist</span>
                </a>
                <button @click="open = false" class="px-4 py-2 rounded-full text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">Cancel</button>
            </div>
        </div>
    </div>
</div>
