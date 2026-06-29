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
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                     </span>
                                     <span class="font-medium group-hover/collapsed:text-gray-900 dark:group-hover/collapsed:text-white transition-colors">How your Signal Feed works — taste vectors, diversity injection &amp; social trust</span>
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
                                     <div class="flex items-start justify-between gap-4 mb-5">
                                         <div>
                                             <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Your Signal Feed</h3>
                                             <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xl">Powered by taste vectors, social trust scoring &amp; diversity injection — engineered to break filter bubbles, not reinforce them.</p>
                                         </div>
                                         {{-- Anti-bias badge row --}}
                                         <div class="hidden sm:flex flex-col gap-1.5 shrink-0">
                                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50">Anti-Filter Bubble</span>
                                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700/50">Social Trust Layer</span>
                                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-700/50">Niche Artist Boost</span>
                                         </div>
                                     </div>

                                     <!-- Algorithm Pillars -->
                                     <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                                         <!-- Pillar 1: Taste Vector -->
                                         <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200/60 dark:border-gray-800 rounded-2xl p-5 hover:border-indigo-400/40 dark:hover:border-indigo-500/30 hover:bg-gray-100 dark:hover:bg-gray-800/60 transition-all duration-300 flex flex-col items-start">
                                             <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 p-2.5 rounded-xl mb-4">
                                                 {{-- Target / cosine icon --}}
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                             </div>
                                             <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Taste Vector Engine</h4>
                                             <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Your listening history is compressed into a multi-dimensional taste centroid using TF-IDF weighting. Cosine similarity then scores every track in the catalog against that fingerprint — not just your top artists.</p>
                                             <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700/50 w-full">
                                                 <span class="text-[10px] font-mono font-semibold text-indigo-500 dark:text-indigo-400">TF-IDF · Cosine Similarity · SVD</span>
                                             </div>
                                         </div>

                                         <!-- Pillar 2: Diversity Injection -->
                                         <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200/60 dark:border-gray-800 rounded-2xl p-5 hover:border-teal-400/40 dark:hover:border-teal-500/30 hover:bg-gray-100 dark:hover:bg-gray-800/60 transition-all duration-300 flex flex-col items-start">
                                             <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-500/20 text-teal-600 dark:text-teal-400 p-2.5 rounded-xl mb-4">
                                                 {{-- Compass / exploration icon --}}
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                                             </div>
                                             <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Diversity Injection</h4>
                                             <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">When your taste profile alone would create a narrow echo chamber, the tiered fallback pipeline intentionally injects genre-adjacent and serendipitous tracks — giving independent and niche artists a genuine path to your feed.</p>
                                             <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700/50 w-full">
                                                 <span class="text-[10px] font-mono font-semibold text-teal-500 dark:text-teal-400">Genre Fallback · Serendipity Tier · Anti-Bias</span>
                                             </div>
                                         </div>

                                         <!-- Pillar 3: Social Trust -->
                                         <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200/60 dark:border-gray-800 rounded-2xl p-5 hover:border-purple-400/40 dark:hover:border-purple-500/30 hover:bg-gray-100 dark:hover:bg-gray-800/60 transition-all duration-300 flex flex-col items-start">
                                             <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-500/20 text-purple-600 dark:text-purple-400 p-2.5 rounded-xl mb-4">
                                                 {{-- People / network icon --}}
                                                 <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                             </div>
                                             <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Social Trust Scoring</h4>
                                             <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">Songs shared or liked by people you follow receive a logarithmic trust boost proportional to your social authority — surfacing tracks championed by real curators in your network, not just platform popularity metrics.</p>
                                             <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700/50 w-full">
                                                 <span class="text-[10px] font-mono font-semibold text-purple-500 dark:text-purple-400">Log Trust · Network Authority · Social Signals</span>
                                             </div>
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
                        <div class="hidden lg:flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-2.5 mb-1">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Your Signal Feed</h3>
                                    {{-- Live pulse indicator --}}
                                    <span class="relative flex h-2 w-2 mt-0.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Taste vectors · Social trust · Diversity injection — not popularity rank</p>
                            </div>
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
                                <div x-data="{ 
                                    activeIndexes: Array.from({length: Math.min(12, {{ $recommendedSongs->count() }})}, (_, i) => i),
                                    maxRendered: Math.min(12, {{ $recommendedSongs->count() }}),
                                    handleInteraction(index) {
                                        this.activeIndexes = this.activeIndexes.filter(i => i !== index);
                                        if (this.maxRendered < {{ $recommendedSongs->count() }}) {
                                            this.activeIndexes.push(this.maxRendered);
                                            this.maxRendered++;
                                        }
                                    }
                                }">
                                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                                        @foreach ($recommendedSongs as $song)
                                            <div x-show="activeIndexes.includes({{ $loop->index }})" 
                                                 @song-interacted.stop="handleInteraction({{ $loop->index }})"
                                                 x-transition:enter="transition ease-out duration-500"
                                                 x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
                                                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                                 class="h-full"
                                                 style="{{ $loop->index >= 12 ? 'display: none;' : '' }}">
                                                <x-discovery-card :song="$song" />
                                            </div>
                                        @endforeach
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
</x-app-layout>
