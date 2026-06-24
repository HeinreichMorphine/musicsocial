<div x-data="{
    searchQuery: '',
    searchResults: [],
    recentTracks: [],
    selectedTrack: null,
    showRecent: true,
    loading: false,

    init() {
        this.$watch('searchQuery', () => this.search());
        this.$watch('isMusicShareModalOpen', (val) => {
            if (val) {
                this.fetchRecent();
                this.showRecent = true;
                this.$nextTick(() => this.$refs.searchInput.focus());
            }
        });
    },
    fetchRecent() {
        fetch(`{{ route('spotify.recently-played') }}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            this.recentTracks = data;
        })
        .catch(error => console.error('Error fetching recent tracks:', error));
    },
    search() {
        if (this.searchQuery.length < 3) {
            this.searchResults = [];
            return;
        }
        this.loading = true;
        fetch(`{{ route('spotify.search') }}?query=${encodeURIComponent(this.searchQuery)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            this.searchResults = data;
            this.loading = false;
        })
        .catch(error => {
            console.error('Spotify search failed:', error);
            this.loading = false;
        });
    },
    selectTrack(track) {
        this.selectedTrack = track;
        this.searchQuery = ''; // Clear search field after selection
        this.searchResults = [];
    },
    getArtistNames(artists) {
        return artists.map(artist => artist.name).join(', ');
    }
}">

    <div x-show="isMusicShareModalOpen" @click.away="isMusicShareModalOpen = false" x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-start justify-center p-4 z-50" style="display: none;">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mt-16 p-6 relative">

            <button type="button" @click="isMusicShareModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            <h3 class="text-xl font-bold mb-4 text-gray-900">Share New Music</h3>

            <h3 class="font-semibold mb-2">Share a Song</h3>
            <x-text-input
                type="text"
                class="w-full"
                placeholder="Search for a track or artist..."
                x-model.debounce.500ms="searchQuery"
                x-ref="searchInput"
                required
            />

            <!-- Recently Played Section -->
            <div x-show="searchQuery.length === 0 && recentTracks.length > 0 && !selectedTrack && showRecent" class="mt-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Recently Played</h4>
                    <button @click="showRecent = false" type="button" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <ul class="max-h-56 overflow-y-auto divide-y border rounded-lg">
                    <template x-for="track in recentTracks" :key="track.id">
                        <li @click="selectTrack(track)" class="p-3 flex items-center space-x-4 hover:bg-gray-100 cursor-pointer rounded-lg transition">
                             <img :src="track.album.images[0]?.url" alt="Album" class="w-10 h-10 rounded">
                             <div class="flex-1 min-w-0">
                                 <div class="font-semibold text-sm truncate" x-text="track.name"></div>
                                 <div class="text-xs text-gray-600 truncate" x-text="getArtistNames(track.artists)"></div>
                             </div>
                             <div class="text-xs text-gray-400">Recent</div>
                        </li>
                    </template>
                </ul>
            </div>

            <div x-show="searchQuery.length >= 3 && searchResults.length === 0 && !loading" class="mt-4 p-3 bg-gray-50 rounded">
                No tracks found for "<span x-text="searchQuery"></span>".
            </div>

            <div x-show="loading" class="text-center p-4">Loading...</div>

            <ul x-show="searchResults.length > 0 && !selectedTrack" class="mt-4 max-h-56 overflow-y-auto divide-y border rounded-lg">
                <template x-for="track in searchResults" :key="track.id">
                    <li @click="selectTrack(track)" class="p-3 flex items-center space-x-4 hover:bg-gray-100 cursor-pointer rounded-lg">
                        <img :src="track.album.images[0]?.url" alt="Album" class="w-10 h-10 rounded">
                        <div>
                            <div class="font-semibold text-sm" x-text="track.name"></div>
                            <div class="text-xs text-gray-600" x-text="getArtistNames(track.artists)"></div>
                        </div>
                    </li>
                </template>
            </ul>

            <form method="POST" action="{{ route('shares.store') }}" class="mt-6 border-t pt-4" x-ref="shareForm">
                @csrf

                <div x-show="selectedTrack" class="mb-4 border border-green-300 bg-green-50/50 rounded-lg p-4 flex items-center space-x-4">
                    <img :src="selectedTrack?.album.images[0].url" alt="Album Art" class="w-16 h-16 rounded">
                    <div>
                        <div class="font-bold text-lg" x-text="selectedTrack?.name"></div>
                        <div class="text-gray-600" x-text="getArtistNames(selectedTrack?.artists || [])"></div>
                    </div>
                    <button type="button" @click="selectedTrack = null; searchQuery=''" class="text-red-500 hover:text-red-700 ml-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <input type="hidden" name="type" value="music">
                <input type="hidden" name="spotify_track_id" x-bind:value="selectedTrack ? selectedTrack.id : ''">

                <div class="mt-4">
                    <label for="caption" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Caption</label>
                    <textarea id="caption" name="caption" placeholder="Write a caption..." class="block w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-black text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                </div>

                <x-primary-button class="mt-4 bg-blue-500 hover:bg-blue-600 text-white shadow-lg shadow-blue-500/30 border-none" x-bind:disabled="!selectedTrack">
                    {{ __('Share Song') }}
                </x-primary-button>

            </form>

        </div>
    </div>
</div>