<x-app-layout pageTitle="Import your Spotify Playlist">
    <div class="py-4 sm:py-12 min-h-screen" x-data="{ 
        selectedTracks: [], 
        maxSelection: 15,
        trackSearch: '',
        toggleTrack(id) {
            if (this.selectedTracks.includes(id)) {
                this.selectedTracks = this.selectedTracks.filter(t => t !== id);
            } else if (this.selectedTracks.length < this.maxSelection) {
                this.selectedTracks.push(id);
            }
        },
        selectAll(ids) {
            this.selectedTracks = [...ids];
        },
        deselectAll() {
            this.selectedTracks = [];
        },
        isImporting: false
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">
            
            <!-- Left Sidebar -->
            <div class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2">
                <div class="sticky top-24 pt-4">
                    <div class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md border border-white/40 dark:border-zinc-800/50 rounded-3xl p-6 shadow-2xl flex flex-col gap-4">
                        @include('layouts.navigation-social')
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 space-y-6">
                
                <div class="flex items-center justify-between">
                    <a href="{{ route('playlists.import.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors group">
                        <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Change Playlist
                    </a>
                    
                    <div class="bg-indigo-600 text-white px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-500/20">
                        Selected: <span x-text="selectedTracks.length">0</span> / 15
                    </div>
                </div>

                <!-- Header -->
                <div class="bg-white dark:bg-black rounded-3xl p-6 border border-gray-100 dark:border-white/10 shadow-sm flex flex-col md:flex-row items-center gap-6 relative">
                    @if($playlist_image)
                        <img src="{{ $playlist_image }}" alt="Playlist Cover" class="w-20 h-20 md:w-32 md:h-32 rounded-2xl object-cover shadow-xl border border-white/10">
                    @endif
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">Playlist: {{ $playlist_name }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Select your absolute favorites to import.</p>
                    </div>
                    
                    <div class="hidden md:block">
                        <button type="button" @click="document.getElementById('importForm').submit()"
                                :disabled="selectedTracks.length === 0"
                                :class="selectedTracks.length > 0 ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/20' : 'bg-gray-300 dark:bg-gray-700 cursor-not-allowed opacity-50'"
                                class="px-8 py-3 text-white font-black rounded-2xl shadow-xl transition transform hover:-translate-y-1 disabled:transform-none">
                            Import Now
                        </button>
                    </div>
                </div>

                @if(session('error') || $errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl font-bold">
                        @if(session('error'))
                            <p>{{ session('error') }}</p>
                        @endif
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('playlists.import.process') }}" method="POST" id="importForm" class="space-y-6 pb-24">
                    @csrf
                    <input type="hidden" name="playlist_image" value="{{ $playlist_image }}">
                    
                    <!-- New Playlist Name Input -->
                    <div class="bg-white dark:bg-black rounded-3xl p-6 border border-gray-100 dark:border-white/10 shadow-sm flex flex-col sm:flex-row gap-4 sm:items-end">
                        <div class="flex-1">
                            <label for="playlist_name" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">New Playlist Name</label>
                            <input type="text" name="playlist_name" id="playlist_name" 
                                   class="w-full bg-gray-50 dark:bg-black border border-gray-100 dark:border-white/5 text-gray-900 dark:text-white rounded-2xl px-5 py-3 focus:ring-indigo-500 font-bold" 
                                   value="{{ $playlist_name }}" required>
                        </div>
                        <div class="sm:w-64 relative group">
                             <input type="text" x-model="trackSearch" 
                                   class="w-full bg-gray-100 dark:bg-white/5 border-none text-xs font-bold rounded-xl px-10 py-3 focus:ring-2 focus:ring-indigo-500 transition" 
                                   placeholder="Search tracks...">
                            <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        @if(count($tracks) < 15)
                        <div class="flex items-center">
                            <button type="button" 
                                    @click="selectedTracks.length === {{ count($tracks) }} ? deselectAll() : selectAll({{ json_encode(array_column($tracks, 'id')) }})"
                                    class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 px-4 py-3 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path x-show="selectedTracks.length < {{ count($tracks) }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    <path x-show="selectedTracks.length === {{ count($tracks) }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span x-text="selectedTracks.length === {{ count($tracks) }} ? 'Deselect All' : 'Select All'"></span>
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Tracklist -->
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($tracks as $index => $track)
                            <div @click="toggleTrack('{{ $track['id'] }}')" 
                                 x-show="('{{ addslashes($track['name']) }}'.toLowerCase().includes(trackSearch.toLowerCase()) || '{{ addslashes($track['artist']) }}'.toLowerCase().includes(trackSearch.toLowerCase())) && !(!selectedTracks.includes('{{ $track['id'] }}') && selectedTracks.length >= maxSelection)"
                                 :class="selectedTracks.includes('{{ $track['id'] }}') ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-500/30' : 'bg-white dark:bg-black border-gray-100 dark:border-white/5 opacity-100'"
                                 class="group relative flex items-center gap-4 p-3 rounded-2xl border-2 cursor-pointer transition-all duration-200 hover:shadow-md"
                                 x-cloak>
                                
                                <div class="relative shrink-0">
                                    @if($track['album_art'])
                                        <img src="{{ $track['album_art'] }}" alt="Album Art" class="w-12 h-12 md:w-14 md:h-14 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform">
                                    @endif
                                    <div x-show="selectedTracks.includes('{{ $track['id'] }}')" class="absolute inset-0 bg-indigo-600/40 rounded-xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm md:text-base font-bold text-gray-900 dark:text-white truncate" :class="selectedTracks.includes('{{ $track['id'] }}') ? 'text-indigo-600 dark:text-indigo-400' : ''">{{ $track['name'] }}</h4>
                                    <p class="text-xs md:text-sm text-gray-500 truncate">{{ $track['artist'] }}</p>
                                </div>

                                <input type="checkbox" name="selected_tracks[]" value="{{ $track['id'] }}" class="hidden" :checked="selectedTracks.includes('{{ $track['id'] }}')">
                                
                                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-200 dark:border-white/10 flex items-center justify-center transition-colors"
                                         :class="selectedTracks.includes('{{ $track['id'] }}') ? 'bg-indigo-600 border-indigo-600' : ''">
                                        <svg x-show="selectedTracks.includes('{{ $track['id'] }}')" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Disabled State for when limit reached -->
                            <div x-show="('{{ addslashes($track['name']) }}'.toLowerCase().includes(trackSearch.toLowerCase()) || '{{ addslashes($track['artist']) }}'.toLowerCase().includes(trackSearch.toLowerCase())) && (!selectedTracks.includes('{{ $track['id'] }}') && selectedTracks.length >= maxSelection)" 
                                 class="flex items-center gap-4 p-3 rounded-2xl border-2 border-gray-50 dark:border-white/5 opacity-40 grayscale cursor-not-allowed bg-gray-50 dark:bg-white/5"
                                 x-cloak>
                                @if($track['album_art'])
                                    <img src="{{ $track['album_art'] }}" class="w-12 h-12 md:w-14 md:h-14 rounded-xl object-cover">
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm md:text-base font-bold text-gray-400 truncate">{{ $track['name'] }}</h4>
                                    <p class="text-xs md:text-sm text-gray-400 truncate">{{ $track['artist'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        <!-- Empty State for Search -->
                        <div x-show="trackSearch !== '' && !document.querySelector('#importForm .grid > div:not([style*=\'display: none\'])')" 
                             class="py-12 text-center text-gray-500 text-sm italic">
                            No tracks matching "<span x-text="trackSearch"></span>" in this playlist.
                        </div>
                    </div>

                    <!-- Floating Action Bar -->
                    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-full max-w-lg px-4 z-50">
                        <div class="bg-white/80 dark:bg-black/80 backdrop-blur-xl border border-white/20 dark:border-white/10 p-4 rounded-[2rem] shadow-2xl flex items-center justify-between gap-4">
                            <div class="hidden sm:block pl-4">
                                <p class="text-[10px] font-black uppercase tracking-tighter text-gray-400">Limit reached at 15</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white"><span x-text="selectedTracks.length">0</span> tracks chosen</p>
                            </div>
                            <div class="flex-1 flex gap-2">
                                <a href="{{ route('playlists.import.index') }}" class="flex-1 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 text-gray-900 dark:text-white font-bold py-3 rounded-2xl text-center transition">Cancel</a>
                                <button type="submit" 
                                        :disabled="selectedTracks.length === 0 || isImporting"
                                        @click="isImporting = true"
                                        :class="selectedTracks.length > 0 ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/20' : 'bg-gray-300 dark:bg-gray-700 cursor-not-allowed opacity-50'"
                                        class="flex-[2] text-white font-black py-3 rounded-2xl shadow-xl transition transform hover:-translate-y-1 disabled:transform-none flex items-center justify-center gap-3">
                                    <template x-if="!isImporting">
                                        <span>Import Selected</span>
                                    </template>
                                    <template x-if="isImporting">
                                        <span class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Importing...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>

