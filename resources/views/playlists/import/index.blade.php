<x-app-layout pageTitle="Import Your Spotify Playlist">
    <div class="py-4 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">
            
            <!-- Left Sidebar -->
            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                    <div class="bg-white/60 dark:bg-black border border-white/40 dark:border-white/5 rounded-3xl p-4 shadow-2xl flex flex-col gap-4">
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

                <div class="bg-white dark:bg-black rounded-[2.5rem] p-8 border border-gray-100 dark:border-white/10 shadow-sm relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-indigo-500/5 pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-4 bg-emerald-500/10 rounded-2xl">
                                <svg class="w-8 h-8 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm5.508 17.302c-.216.354-.675.465-1.028.249-2.815-1.722-6.36-2.112-10.537-1.157-.403.093-.811-.158-.905-.562-.093-.404.159-.812.562-.905 4.577-1.047 8.508-.602 11.659 1.326.354.216.465.675.249 1.028zm1.474-3.264c-.273.443-.852.583-1.295.31-3.222-1.98-8.136-2.557-11.947-1.4c-.5.152-1.025-.13-1.177-.63-.153-.5.13-1.025.63-1.177 4.357-1.322 9.774-.678 13.482 1.6 0 .001.442.274.707.697zm.128-3.413C15.111 8.217 8.513 7.994 4.697 9.151c-.604.183-1.246-.164-1.428-.767-.183-.604.164-1.246.767-1.428 4.38-1.328 11.666-1.066 16.326 1.7 0 .001 1.107.657.828 1.488-.28.831-1.08 1.141-1.08 1.141z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Import Your Spotify Playlist</h2>
                                <p class="text-gray-500 dark:text-gray-400">Bring your favorite vibes to Reso.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-white/5 rounded-3xl p-6 mb-8 border border-gray-100 dark:border-white/5">
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Paste the link to your Spotify playlist below. We'll fetch the tracks and let you curate <strong>up to 15</strong> of your absolute favorites to create a new playlist here.
                            </p>
                        </div>

                        @if(Auth::user()->spotify_token === null)
                            <div class="mb-8 p-6 bg-amber-500/10 border border-amber-500/20 rounded-3xl flex items-start gap-4 relative overflow-hidden group">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
                                <div class="p-3 bg-amber-500/20 rounded-2xl shrink-0">
                                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-amber-700 dark:text-amber-400 mb-1">Spotify Not Connected</h4>
                                    <p class="text-xs text-amber-600/80 dark:text-amber-400/60 leading-relaxed mb-4">
                                        You haven't linked your Spotify account yet. While you can still import public playlists by URL, connecting your account allows you to browse and import directly from <strong>your own library</strong>.
                                    </p>
                                    <button type="button" @click.prevent.stop="$dispatch('open-spotify-link-modal')" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-white px-5 py-2 rounded-xl text-xs font-black transition shadow-lg shadow-amber-500/20">
                                        Connect Spotify Library
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl font-bold flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('playlists.import.preview') }}" method="POST" id="import-form" class="space-y-6">
                            @csrf
                            <div class="space-y-2">
                                <label for="spotify_url" class="block text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Spotify Playlist URL</label>
                                <input type="url" name="spotify_url" id="spotify_url" 
                                       class="w-full bg-gray-50 dark:bg-black border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl px-5 py-4 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-sm placeholder-gray-500" 
                                       placeholder="https://open.spotify.com/playlist/..." required>
                                <p class="text-[10px] text-gray-400 ml-1 font-medium italic">Example: https://open.spotify.com/playlist/37i9dQZF1DXcBWIGoYBM5M</p>
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-emerald-500/20 transition transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Fetch Playlist
                            </button>
                        </form>

                        @if(!empty($spotifyPlaylists))
                            <div class="mt-12" x-data="{ 
                                search: '', 
                                localPlaylists: {{ json_encode($spotifyPlaylists) }},
                                globalResults: [],
                                isSearching: false,
                                async searchGlobal() {
                                    if (this.search.length < 2) return;
                                    this.isSearching = true;
                                    try {
                                        const response = await fetch('{{ route('spotify.search-playlists') }}?q=' + encodeURIComponent(this.search));
                                        this.globalResults = await response.json();
                                    } catch (e) {
                                        console.error('Search failed', e);
                                    } finally {
                                        this.isSearching = false;
                                    }
                                },
                                get filteredLocal() {
                                    if (!this.search) return this.localPlaylists;
                                    return this.localPlaylists.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                                }
                            }">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 border-b border-gray-100 dark:border-white/5 pb-6">
                                    <div>
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm0 18c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>
                                            Your Spotify Playlists
                                        </h3>
                                        <p class="text-[10px] text-gray-400 mt-1 font-bold uppercase tracking-widest">Showing up to 200 of your playlists</p>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <div class="relative group flex-1 sm:w-64">
                                            <input type="text" x-model="search" @input.debounce.300ms="if(search.length > 2) searchGlobal()"
                                                   class="w-full bg-gray-100 dark:bg-white/5 border-none text-xs font-bold rounded-xl px-10 py-3 focus:ring-2 focus:ring-emerald-500 transition" 
                                                   placeholder="Search your library & Spotify...">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            <div x-show="isSearching" class="absolute right-4 top-1/2 -translate-y-1/2">
                                                <svg class="animate-spin h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Local Results (Server-side rendered for instant visibility) -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" x-show="filteredLocal.length > 0">
                                    @foreach($spotifyPlaylists as $playlist)
                                        <div class="group cursor-pointer text-center" 
                                             x-show="'{{ addslashes($playlist['name']) }}'.toLowerCase().includes(search.toLowerCase())"
                                             x-transition.opacity
                                             onclick="document.getElementById('spotify_url').value = '{{ $playlist['external_urls']['spotify'] }}'; document.getElementById('import-form').submit();">
                                            <div class="aspect-square rounded-2xl overflow-hidden mb-2 relative">
                                                <img src="{{ $playlist['images'][0]['url'] ?? asset('images/default-playlist.png') }}" 
                                                     alt="{{ $playlist['name'] }}" 
                                                     class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <div class="bg-emerald-500 p-3 rounded-full transform scale-0 group-hover:scale-100 transition-transform duration-300">
                                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-emerald-500 transition-colors">{{ $playlist['name'] }}</h4>
                                            <p class="text-[10px] text-gray-500">{{ $playlist['tracks']['total'] }} tracks</p>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Global Results Divider -->
                                <div x-show="globalResults.length > 0" class="mt-12 mb-6 flex items-center gap-4" x-cloak>
                                    <div class="h-px bg-gray-100 dark:bg-white/10 flex-1"></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Global Spotify Results</span>
                                    <div class="h-px bg-gray-100 dark:bg-white/10 flex-1"></div>
                                </div>

                                <!-- Global Results -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" x-show="globalResults.length > 0" x-cloak>
                                    <template x-for="playlist in globalResults" :key="'global-'+playlist.id">
                                        <div class="group cursor-pointer text-center" @click="document.getElementById('spotify_url').value = playlist.external_urls.spotify; document.getElementById('import-form').submit();">
                                            <div class="aspect-square rounded-2xl overflow-hidden mb-2 relative">
                                                <img :src="playlist.images?.[0]?.url || '{{ asset('images/default-playlist.png') }}'" 
                                                     :alt="playlist.name" 
                                                     class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500 opacity-80 group-hover:opacity-100">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <div class="bg-indigo-600 p-3 rounded-full transform scale-0 group-hover:scale-100 transition-transform duration-300">
                                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate group-hover:text-indigo-400 transition-colors" x-text="playlist.name"></h4>
                                            <p class="text-[10px] text-gray-500" x-text="playlist.owner.display_name"></p>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Empty State for Search -->
                                <div x-show="search !== '' && filteredLocal.length === 0 && globalResults.length === 0 && !isSearching" 
                                     class="py-12 text-center text-gray-500 text-xs italic">
                                    No playlists matching "<span x-text="search"></span>" found in your library or on Spotify.
                                </div>
                            </div>
                        @else
                            @if(Auth::user()->spotify_token === null)
                                <div class="mt-12 p-6 bg-indigo-500/5 rounded-3xl border border-indigo-500/10 flex flex-col items-center text-center">
                                    <div class="w-12 h-12 bg-indigo-500/10 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    </div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1">Link your Spotify</h4>
                                    <p class="text-xs text-gray-500 mb-4">Connect your account to see your playlists here.</p>
                                    <button type="button" @click.prevent.stop="$dispatch('open-spotify-link-modal')" class="bg-black dark:bg-white dark:text-black text-white px-6 py-2 rounded-xl text-xs font-black hover:opacity-80 transition">Connect Now</button>
                                </div>
                            @endif
                        @endif
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
</x-app-layout>

