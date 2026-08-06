<x-app-layout pageTitle="Discovery">
    <div x-data="{ isMusicShareModalOpen: false, activeTab: 'songs' }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <!-- Left Navigation -->
                <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                    <div class="sticky top-24 pt-4">
                        <div
                            class="bg-white/60 dark:bg-black border border-white/40 dark:border-white/5 rounded-3xl p-4 shadow-2xl flex flex-col gap-4">
                            @include('layouts.navigation-social')
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7">
                    <div class="p-4 md:p-6 text-gray-900 dark:text-gray-100">
                        <!-- Onboarding Explainer Banner Container -->
                        <div x-data="{ isCollapsed: localStorage.getItem('discoveryOnboardingCollapsed') === 'true' }" class="mb-8 space-y-4">
                            <!-- Collapsed State Header -->
                            <div x-show="isCollapsed" 
                                 @click="isCollapsed = false; localStorage.setItem('discoveryOnboardingCollapsed', 'false')"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="relative bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-3.5 cursor-pointer hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition-all duration-300 flex items-center justify-between shadow-md group/collapsed"
                            >
                                <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300 text-sm">
                                    <span class="text-indigo-600 dark:text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </span>
                                    <span class="font-medium group-hover/collapsed:text-gray-900 dark:group-hover/collapsed:text-white transition-colors">How Discovery Works: Recommendations, Social Sharing & Spotify Syncing</span>
                                </div>
                                <span class="text-gray-400 dark:text-gray-500 group-hover/collapsed:text-gray-600 dark:group-hover/collapsed:text-zinc-300 transition-colors flex items-center gap-1.5 text-xs font-semibold">
                                    Expand
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Expanded State (Full Banner) -->
                            <div x-show="!isCollapsed"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
                                 class="relative bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-xl overflow-hidden group/banner"
                            >
                                <!-- Background ambient glow -->
                                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none transition-all duration-700 group-hover/banner:bg-indigo-500/10 dark:group-hover/banner:bg-indigo-500/20"></div>
                                <div class="absolute -left-12 -top-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none transition-all duration-700 group-hover/banner:bg-emerald-500/10"></div>

                                <!-- Collapse button in top right -->
                                <button @click="isCollapsed = true; localStorage.setItem('discoveryOnboardingCollapsed', 'true')" 
                                        class="absolute top-4 right-4 text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/50 p-1.5 rounded-lg transition-all duration-200 z-10 flex items-center gap-1.5 text-xs font-semibold"
                                        title="Collapse Overview"
                                >
                                    Collapse
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7-7 7 7" />
                                    </svg>
                                </button>

                                <div class="relative z-10">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Get the most out of Discovery</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-2xl">Explore custom recommendations, interact with tracks, and take your synced discoveries with you on Spotify.</p>

                                    <!-- Pillars Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <!-- Pillar 1: Algorithm Matches -->
                                        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-fuchsia-500/30 dark:hover:border-fuchsia-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                            <div class="bg-fuchsia-50 dark:bg-fuchsia-900/20 border border-fuchsia-100 dark:border-fuchsia-500/20 text-fuchsia-600 dark:text-fuchsia-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path>
                                                    <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5 5 3Z"></path>
                                                    <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1 1-2.5Z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Algorithm Matches</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Explore tracks hand-picked by our recommender engine, calculated based on your likes, recently played tracks, and community tastes.</p>
                                        </div>

                                        <!-- Pillar 2: Listen & Share Vibes -->
                                        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-indigo-500/30 dark:hover:border-indigo-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <path d="M9 18V5l12-2v13"></path>
                                                    <circle cx="6" cy="18" r="3"></circle>
                                                    <circle cx="18" cy="16" r="3"></circle>
                                                </svg>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Listen & Share</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Preview audio snippets of any track directly, add them to your user shelf, bookmark them for later, or post them to the activity feed.</p>
                                        </div>

                                        <!-- Pillar 3: Spotify Sync -->
                                        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800 rounded-2xl p-5 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 hover:bg-gray-200 dark:hover:bg-gray-800 transition-all duration-300 flex flex-col items-start">
                                            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-2.5 rounded-xl mb-4 transition-transform duration-300 hover:scale-110">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm5.508 17.302c-.216.354-.675.465-1.028.249-2.815-1.722-6.36-2.112-10.537-1.157-.403.093-.811-.158-.905-.562-.093-.404.159-.812.562-.905 4.577-1.047 8.508-.602 11.659 1.326.354.216.465.675.249 1.028zm1.474-3.264c-.273.443-.852.583-1.295.31-3.222-1.98-8.136-2.557-11.947-1.4c-.5.152-1.025-.13-1.177-.63-.153-.5.13-1.025.63-1.177 4.357-1.322 9.774-.678 13.482 1.6 0 .001.442.274.707.697zm.128-3.413C15.111 8.217 8.513 7.994 4.697 9.151c-.604.183-1.246-.164-1.428-.767-.183-.604.164-1.246.767-1.428 4.38-1.328 11.666-1.066 16.326 1.7 0 .001 1.107.657.828 1.488-.28.831-1.08 1.141-1.08 1.141z"/>
                                                </svg>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white mb-2">Spotify Sync</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Save your entire recommended list directly to a dedicated playlist in your linked Spotify account to play on any device.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Tabs (Hidden on Desktop) -->
                        <div class="block lg:hidden border-b border-gray-100 dark:border-gray-700 mb-6 -mt-2">
                            <nav class="-mb-px flex space-x-8 px-2" aria-label="Tabs">
                                <button @click="activeTab = 'songs'"
                                    :class="activeTab === 'songs' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center focus:outline-none">
                                    Discover Songs
                                </button>

                                <button @click="activeTab = 'people'"
                                    :class="activeTab === 'people' ? 'border-custom-mid-blue text-custom-mid-blue dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center focus:outline-none">
                                    Who to Follow
                                </button>
                            </nav>
                        </div>

                        <!-- Desktop Header -->
                        <div class="hidden lg:flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Discover New Songs</h3>
                            @if(auth()->user()->spotify_token)
                                @if($recommendedSongs->count() > 0)
                                    <form action="{{ route('export-playlist') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="source" value="discovery">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full font-bold shadow text-sm transition-colors flex items-center space-x-2">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.54.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                            <span>Export to Spotify</span>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <button type="button" @click.prevent.stop="$dispatch('open-spotify-link-modal')" class="bg-[#1DB954] hover:bg-[#1ed760] text-white px-5 py-2.5 rounded-full font-bold shadow-lg hover:shadow-green-500/20 text-sm transition-all flex items-center space-x-2 transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.54.659.301 1.02zm1.44-3.3c-.301.42-.84.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.84.241 1.2zM20.04 9.72c-3.96-2.34-10.44-2.58-14.22-1.44-.6.18-1.2-.12-1.38-.72-.18-.6.12-1.2.72-1.38 4.26-1.26 11.28-1.02 15.721 1.62.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.26.36z"/></svg>
                                    <span>Sync to Spotify</span>
                                </button>
                            @endif
                        </div>

                        <!-- Songs Content -->
                        <div :class="activeTab === 'songs' ? 'block' : 'hidden lg:block'">
                            @if($recommendedSongs->isEmpty())
                                <p class="text-center text-gray-500 dark:text-gray-400">No recommendations available at the moment.</p>
                            @else
                                @php
                                    $allSongChips = $recommendedSongs->map(fn($s) => $s->chip_label ?? \App\Http\Controllers\DiscoveryController::determineChipLabel($s->reason))->values()->all();
                                @endphp
                                <div x-data="discoveryFeed(@js($availableChips), {{ $recommendedSongs->count() }}, @js($allSongChips))">

                                    <!-- Spotify-Style Pill Filter Bar -->
                                    <div class="mb-5 overflow-x-auto no-scrollbar py-1">
                                        <div class="flex items-center space-x-2 min-w-max">
                                            <button @click="selectedChip = 'All'"
                                                    :class="selectedChip === 'All' 
                                                        ? 'bg-custom-mid-blue text-white shadow-md shadow-blue-500/20 font-bold scale-105' 
                                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium'"
                                                    class="px-4 py-1.5 rounded-full text-xs transition-all duration-200 cursor-pointer">
                                                All
                                            </button>

                                            <template x-for="chip in availableChips" :key="chip">
                                                <button @click="selectedChip = chip"
                                                        :class="selectedChip === chip 
                                                            ? 'bg-custom-mid-blue text-white shadow-md shadow-blue-500/20 font-bold scale-105' 
                                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium'"
                                                        class="px-4 py-1.5 rounded-full text-xs transition-all duration-200 cursor-pointer">
                                                    <span x-text="chip"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Grid of Recommended Cards -->
                                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4" x-show="hasMatchingSongs()">
                                        @foreach ($recommendedSongs as $song)
                                            @php $cardChip = $song->chip_label ?? \App\Http\Controllers\DiscoveryController::determineChipLabel($song->reason); @endphp
                                            <div data-chip="{{ $cardChip }}"
                                                 x-show="isChipMatch('{{ addslashes($cardChip) }}', {{ $loop->index }})" 
                                                 @song-interacted.stop="handleInteraction({{ $loop->index }})"
                                                 x-transition:enter="transition ease-out duration-500"
                                                 x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
                                                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                                 class="h-full">
                                                <x-discovery-card :song="$song" />
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Empty State Placeholder for Pills with 0 Songs -->
                                    <div x-show="!hasMatchingSongs()" x-cloak class="my-8 text-center py-12 px-4 bg-gray-50/50 dark:bg-gray-900/40 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800 flex flex-col items-center justify-center space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-2xl">
                                            🎵
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">No songs for this section</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                                            No recommendations currently match "<span x-text="selectedChip" class="font-bold text-indigo-600 dark:text-indigo-400"></span>". Explore other filter pills or view all songs!
                                        </p>
                                        <button @click="selectedChip = 'All'" class="mt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                                            ← Back to All Songs
                                        </button>
                                    </div>

                                    <!-- Load More Songs Button -->
                                    <div class="mt-8 text-center flex flex-col items-center justify-center space-y-3" x-show="selectedChip === 'All' && maxRendered < {{ $recommendedSongs->count() }}">
                                        <button @click="loadMore()" 
                                                class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold px-8 py-3.5 rounded-full shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105 active:scale-95 transition-all duration-300 flex items-center space-x-2.5 text-sm cursor-pointer group">
                                            <svg class="w-5 h-5 text-white/90 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <span>Discover More Songs</span>
                                        </button>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Showing <span x-text="Math.min(maxRendered, {{ $recommendedSongs->count() }})"></span> of {{ $recommendedSongs->count() }} personalized recommendations</p>
                                    </div>

                                    <!-- End of recommendations message (only shows when user has loaded through all 12+ songs in 'All' view) -->
                                    <div class="mt-8 text-center py-6 bg-gray-50/50 dark:bg-gray-900/40 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800" 
                                         x-show="selectedChip === 'All' && maxRendered >= {{ $recommendedSongs->count() }} && {{ $recommendedSongs->count() }} > 12" x-cloak>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">✨ You've explored all current recommendations! Like or share more tracks to discover new music.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Who to Follow Content (Mobile Only) -->
                        <div :class="activeTab === 'people' ? 'block lg:hidden' : 'hidden'" x-cloak>
                            <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-0 pt-4">
                        <x-who-to-follow :usersToSuggest="$usersToSuggest" />
                        <x-sidebar-right :recommendedSongs="$recommendedSongs" />
                    </div>
                </div>
            </div>
            <x-music-share-modal />
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('discoveryFeed', (chips, totalCount, allSongChips) => ({
                selectedChip: 'All',
                maxRendered: Math.min(12, totalCount),
                activeIndexes: Array.from({length: Math.min(12, totalCount)}, (_, i) => i),
                availableChips: chips || [],
                allSongChips: allSongChips || [],

                init() {
                    console.log('[Discovery] availableChips:', JSON.parse(JSON.stringify(this.availableChips)));
                    console.log('[Discovery] allSongChips sample (first 10):', JSON.parse(JSON.stringify(this.allSongChips)).slice(0, 10));
                    console.log('[Discovery] totalCount:', totalCount);
                },

                isChipMatch(cardChip, index) {
                    if (this.selectedChip === 'All') {
                        return this.activeIndexes.includes(index);
                    }
                    const sel = (this.selectedChip || '').toLowerCase().trim();
                    const card = (cardChip || '').toLowerCase().trim();
                    return sel === card;
                },

                hasMatchingSongs() {
                    if (this.selectedChip === 'All') {
                        return this.activeIndexes.length > 0;
                    }
                    const sel = (this.selectedChip || '').toLowerCase().trim();
                    return (this.availableChips || []).some(chip => (chip || '').toLowerCase().trim() === sel);
                },

                handleInteraction(index) {
                    this.activeIndexes = this.activeIndexes.filter(i => i !== index);
                    if (this.maxRendered < totalCount) {
                        this.activeIndexes.push(this.maxRendered);
                        this.maxRendered++;
                    }
                },

                loadMore() {
                    const nextLimit = Math.min(this.maxRendered + 12, totalCount);
                    this.maxRendered = nextLimit;
                    this.activeIndexes = Array.from({length: nextLimit}, (_, i) => i);
                }
            }));
        });
    </script>
</x-app-layout>

