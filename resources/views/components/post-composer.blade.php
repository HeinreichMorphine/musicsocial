<div class="w-full" x-data="{
    postType: 'music',
    isSeekingRecommendations: false,
    searchQuery: '',
    searchResults: [],
    recentTracks: [],
    selectedTrack: null,
    showRecent: true,
    loading: false,

    init() {
        this.$watch('searchQuery', () => this.search());
        this.$root.addEventListener('switchToMusicShare', () => {
             // ... existing reset logic
            this.postType = 'music';
            this.searchQuery = '';
            this.searchResults = [];
            this.selectedTrack = null;
            this.fetchRecent(); // Call fetchRecent when switching to music share
        });
        // Watch for postType changes to fetch recent tracks if it becomes 'music'
        this.$watch('postType', (value) => {
            if (value === 'music') {
                this.fetchRecent();
            }
        });
        // Fetch recent tracks immediately if postType is already 'music'
        if (this.postType === 'music') {
            this.fetchRecent();
        }
    },
    fetchRecent() {
        // Only fetch recent tracks if user has spotify connected
        @if(auth()->user()->spotify_token)
            fetch(`{{ route('spotify.recently-played') }}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                this.recentTracks = data;
            })
            .catch(error => console.error('Error fetching recent tracks:', error));
        @endif
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
    getAlbumArt(track) {
        return track.album.images && track.album.images.length > 0 ? track.album.images[0].url : 'https://via.placeholder.com/40';
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
        formData.append('type', this.isSeekingRecommendations ? 'recommendation_request' : this.postType);
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
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || errorData.error || 'Network response was not ok');
            }
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
            alert('Failed to share post: ' + error.message);
        })
        .finally(() => {
            this.loading = false;
        });
    }
}">
    <form @submit.prevent="submitPost" x-ref="form" class="flex flex-col items-center">
        @csrf
        <input type="hidden" name="type" x-model="postType">

        <h3 class="text-3xl md:text-4xl font-display font-bold text-gray-900 dark:text-white mb-4 md:mb-8 text-center tracking-tight" x-text="isSeekingRecommendations ? 'What should I listen to next?' : 'What are you listening to?'">
            What are you listening to?
        </h3>

        <!-- Vibe Check Toggle -->
        <div class="flex items-center justify-center space-x-3 mb-6">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400" :class="!isSeekingRecommendations && 'text-gray-900 dark:text-white font-bold'">Just Sharing</span>
            <button type="button" 
                @click="isSeekingRecommendations = !isSeekingRecommendations"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-custom-mid-blue focus:ring-offset-2"
                :class="isSeekingRecommendations ? 'bg-custom-mid-blue' : 'bg-gray-200 dark:bg-gray-700'">
                <span class="sr-only">Toggle Asking for Recommendations</span>
                <span
                    aria-hidden="true"
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="isSeekingRecommendations ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5" :class="isSeekingRecommendations && 'text-custom-mid-blue font-bold'">
                Asking for Recommendations
            </span>
        </div>

        <div class="w-full relative max-w-2xl">
             <div class="relative group flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                    <x-user-avatar :user="auth()->user()" class="h-10 w-10 border-2 border-white dark:border-gray-800 shadow-sm rounded-full shrink-0" />
                </div>
                <input
                    type="text"
                    class="w-full rounded-[2rem] border-2 border-transparent bg-white dark:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500 text-gray-900 dark:text-white py-3 md:py-4 pl-16 pr-24 shadow-xl transition-all hover:bg-gray-50 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-800 focus:border-custom-mid-blue focus:ring-4 focus:ring-blue-500/10 text-lg"
                    :placeholder="isSeekingRecommendations ? 'Help me find similar tracks...' : 'Share a song you\'re loving...'"
                    x-model.debounce.300ms="searchQuery"
                    x-init="$watch('postType', (val) => { if (val === 'music') $el.focus() })"
                    @focus="fetchRecent(); showRecent = true"
                />
                <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                    <button type="button" @click="searchQuery ? search() : $el.previousElementSibling.focus()"
                            :class="searchQuery.length > 0 ? 'bg-custom-mid-blue text-white shadow-lg border-transparent shadow-blue-500/30' : 'bg-transparent text-gray-400 border border-gray-300 dark:border-gray-600 hover:text-gray-600 dark:hover:text-gray-300'"
                            class="rounded-full px-5 py-2 font-bold transition-all text-sm">Post</button>
                </div>
            </div>

            <!-- Recently Played Section -->
            <div x-show="searchQuery.length === 0 && recentTracks.length > 0 && !selectedTrack && showRecent" 
                 class="absolute top-full left-0 right-0 mt-4 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-white/10 overflow-hidden z-20"
                 @click.away="searchQuery = ''"> <!-- Close on click away (optional logic tweak may be needed depending on UX preference) -->
                 
                <div class="p-4 bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 flex justify-between items-center">
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Recently Played</h4>
                     <button @click.stop="showRecent = false" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-white/5">
                     <template x-for="(track, index) in recentTracks.slice(0, 3)" :key="index">
                        <li @click="selectTrack(track)" class="p-4 flex items-center space-x-4 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer transition">
                             <img :src="getAlbumArt(track)" alt="Album" class="w-12 h-12 rounded-lg object-cover shadow-sm bg-gray-200">
                             <div class="flex-1 min-w-0">
                                 <div class="font-bold text-gray-900 dark:text-white truncate" x-text="track.name"></div>
                                 <div class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="getArtistNames(track.artists)"></div>
                             </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Search Results -->
            <ul x-show="searchResults.length > 0 && !selectedTrack" 
                class="absolute top-full left-0 right-0 mt-4 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-white/10 overflow-hidden z-20 max-h-[400px] overflow-y-auto">
                <template x-for="track in searchResults" :key="track.id">
                    <li @click="selectTrack(track)" class="p-4 flex items-center space-x-4 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer border-b border-gray-100 dark:border-white/5 last:border-0 transition">
                        <img :src="getAlbumArt(track)" alt="Art" class="w-12 h-12 rounded-lg object-cover shadow-sm bg-gray-200 shrink-0">
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 dark:text-white truncate" x-text="track.name"></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="getArtistNames(track.artists)"></div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
        
        <div x-show="searchQuery.length >= 3 && searchResults.length === 0 && !loading" class="mt-4 p-3 font-semibold text-gray-700 dark:text-white bg-white/80 dark:bg-black/80 backdrop-blur-md rounded-full px-6 shadow-lg border border-gray-100 dark:border-white/10">
            No tracks found for "<span x-text="searchQuery" class="text-custom-mid-blue dark:text-blue-400"></span>".
        </div>

        <div x-show="loading && !selectedTrack" class="mt-4 text-custom-mid-blue dark:text-blue-400 animate-pulse font-bold tracking-widest text-sm uppercase">SEARCHING SPOTIFY...</div>

        <!-- Selected Track View -->
        <div x-show="selectedTrack" class="w-full max-w-2xl mt-8">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-xl border border-gray-200 dark:border-gray-700 relative overflow-hidden group">
                <!-- blurred background -->
                 <div class="absolute inset-0 bg-cover bg-center blur-3xl opacity-20 dark:opacity-40 transition-transform duration-700 group-hover:scale-110" :style="`background-image: url('${selectedTrack?.album.images[0]?.url}');`"></div>
                 
                 <div class="relative flex flex-col sm:flex-row items-center gap-6">
                    <img :src="selectedTrack?.album.images[0]?.url" alt="Album Art" class="w-32 h-32 rounded-2xl shadow-lg transition-transform duration-300">
                    
                    <div class="flex-1 text-center sm:text-left min-w-0 w-full">
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white truncate leading-tight mb-1" x-text="selectedTrack?.name"></h4>
                        <p class="text-lg text-gray-600 dark:text-gray-300 truncate mb-4" x-text="getArtistNames(selectedTrack?.artists || [])"></p>
                        
                        <div class="relative">
                            <textarea id="caption" name="caption" x-ref="captionInput" 
                                placeholder="Your caption goes here...." 
                                class="w-full border-0 bg-gray-100 dark:bg-white/10 rounded-xl p-4 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-custom-mid-blue transition resize-none pb-12"></textarea>
                            
                            <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                <button type="button" @click="selectedTrack = null; searchQuery=''" class="text-gray-400 hover:text-red-500 transition text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="bg-black dark:bg-white text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gray-200 font-bold py-1.5 px-4 rounded-lg uppercase tracking-wide text-xs shadow-lg transform hover:-translate-y-0.5 transition-all" 
                                        x-bind:disabled="loading" 
                                        x-text="loading ? 'Posting...' : 'Share'">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>