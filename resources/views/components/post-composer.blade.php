<div class="bg-white p-4 shadow-md rounded-lg mb-6" x-data="{
    postType: 'music',
    searchQuery: '',
    searchResults: [],
    recentTracks: [],
    selectedTrack: null,
    loading: false,

    init() {
        this.$watch('searchQuery', () => this.search());
        this.$root.addEventListener('switchToMusicShare', () => {
             // ... existing reset logic
            this.postType = 'music';
            this.searchQuery = '';
            this.searchResults = [];
            this.selectedTrack = null;
        });
        // Fetch recent tracks immediately so they are ready
        this.fetchRecent();
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
    },
    resetComposer() {
        this.postType = 'music';
        this.searchQuery = '';
        this.searchResults = [];
        this.selectedTrack = null;
        this.$refs.captionInput.value = '';
        this.loading = false;
    },
    submitPost() {
        if (!this.selectedTrack) return;
        this.loading = true;

        const formData = new FormData();
        formData.append('type', this.postType);
        formData.append('spotify_track_id', this.selectedTrack.id);
        formData.append('caption', this.$refs.captionInput.value);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('shares.store') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.html) {
                const feedContainer = document.getElementById('feed-container');
                if (feedContainer) {
                    feedContainer.insertAdjacentHTML('afterbegin', data.html);
                }
                this.resetComposer();
            } else {
                 console.error('No HTML returned');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to share post. Please try again.');
        })
        .finally(() => {
            this.loading = false;
        });
    }
}">
    <form @submit.prevent="submitPost" x-ref="form">
        @csrf
        <input type="hidden" name="type" x-model="postType">

        <div>
            <h3 class="font-semibold mb-2">Share a Song</h3>
            <div class="flex items-start space-x-3">
                 <img src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150' }}"
                     alt="{{ auth()->user()->name }}"
                     class="w-10 h-10 rounded-full object-cover">
                <div class="w-full">
                    <x-text-input
                        type="text"
                        class="w-full rounded-2xl bg-gray-50 border-gray-200 focus:bg-white transition"
                        placeholder="What are you listening to right now?"
                        x-model.debounce.500ms="searchQuery"
                        x-init="$watch('postType', (val) => { if (val === 'music') $el.focus() })"
                        @focus="fetchRecent()"
                    />
                </div>
            </div>

            <!-- Recently Played Section -->
            <div x-show="searchQuery.length === 0 && recentTracks.length > 0 && !selectedTrack" class="mt-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Recently Played</h4>
                <ul class="divide-y border rounded-lg">
                     <template x-for="(track, index) in recentTracks.slice(0, 3)" :key="index">
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

            <div x-show="selectedTrack" class="mt-4 relative overflow-hidden rounded-xl border border-gray-100 shadow-sm" style="display: none;">
                 <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-30" :style="`background-image: url('${selectedTrack?.album.images[0].url}');`"></div>
                 <div class="relative p-4 flex items-center space-x-4 bg-white/60 backdrop-blur-sm">
                    <img :src="selectedTrack?.album.images[0].url" alt="Album Art" class="w-16 h-16 rounded shadow-sm">
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-lg text-gray-900 truncate" x-text="selectedTrack?.name"></div>
                        <div class="text-gray-600 truncate" x-text="getArtistNames(selectedTrack?.artists || [])"></div>
                    </div>
                    <button type="button" @click="selectedTrack = null; searchQuery=''" class="text-gray-400 hover:text-red-500 transition p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <input type="hidden" name="spotify_track_id" x-model="selectedTrack?.id">

            <div class="mt-4" x-show="selectedTrack" x-transition>
                <x-input-label for="caption" :value="__('Caption')" />
                <textarea id="caption" name="caption" x-ref="captionInput" placeholder="Write a caption..." class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
        </div>

        <div class="mt-3 flex justify-end items-center">
            <x-primary-button class="bg-custom-mid-blue hover:bg-custom-dark-blue" x-bind:disabled="!selectedTrack" x-bind:class="{ 'opacity-50 cursor-not-allowed': loading }" x-text="loading ? 'Sharing...' : 'Share Song'">
                Share Song
            </x-primary-button>
        </div>
    </form>
</div>