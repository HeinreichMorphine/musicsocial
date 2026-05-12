<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Curate Your Shelf - {{ config('app.name', 'Reso') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(156, 163, 175, 0.1); border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(79, 70, 229, 0.4); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(79, 70, 229, 0.8); }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-scale-in { animation: scaleIn 0.2s ease-out forwards; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white antialiased transition-colors duration-300">
    <div class="min-h-screen flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50/50 via-white to-white dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950" x-data="onboardingApp()">
        <div class="w-full max-w-5xl space-y-8 animate-fade-in-up">
            <div class="text-center">
                <div class="inline-block p-4 rounded-full bg-indigo-500/10 mb-4 border border-indigo-500/20">
                    <svg class="w-12 h-12 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 15v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 18v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 21v-1.122" />
                    </svg>
                </div>
                <h2 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                    Curate Your Song Shelf
                </h2>
                <p class="mt-4 text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Pick <strong>3 to 10</strong> of your all-time favorite tracks. This builds your unique fingerprint and helps us recommend the best music and peers to you.
                </p>
            </div>

            <!-- Search Area -->
            <div class="relative max-w-2xl mx-auto z-20">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="performSearch" :disabled="isSubmitting"
                           class="block w-full pl-12 pr-4 py-4 border border-gray-200 dark:border-gray-700/50 rounded-2xl leading-5 bg-white dark:bg-gray-800/80 backdrop-blur-md text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg shadow-xl transition-all disabled:opacity-50"
                           placeholder="Search for an artist, album, or track...">
                </div>

                <!-- Search Results Dropdown/Area -->
                <div x-show="isSearching" x-cloak class="mt-4 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div x-show="searchResults.length > 0 && searchQuery.length > 2" x-transition class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="track in searchResults" :key="track.id">
                        <div @click="toggleTrack(track)" 
                             class="flex items-center p-3 rounded-xl cursor-pointer transition-all duration-200 border shadow-sm hover:shadow-md"
                             :class="isSelected(track.id) ? 'bg-indigo-50 dark:bg-indigo-900/60 border-indigo-200 dark:border-indigo-500 shadow-indigo-500/10' : 'bg-white dark:bg-gray-800/50 border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-200 dark:hover:border-gray-600'">
                            <img :src="track.album?.images[0]?.url || '/images/default-album.png'" class="w-14 h-14 rounded-lg shadow-sm object-cover" alt="Album Art">
                            <div class="ml-4 flex-1 truncate">
                                <h4 class="text-gray-900 dark:text-white font-semibold truncate" x-text="track.name"></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="track.artists?.map(a => a.name).join(', ')"></p>
                            </div>
                            <div class="ml-2">
                                <svg x-show="isSelected(track.id)" class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <svg x-show="!isSelected(track.id)" class="w-6 h-6 text-gray-300 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Your Shelf Area -->
            <div class="mt-12 bg-white dark:bg-gray-800/40 rounded-3xl p-6 md:p-8 border border-gray-100 dark:border-gray-700/50 backdrop-blur-xl shadow-2xl relative z-10">
                <div class="flex justify-between items-end mb-8 border-b border-gray-100 dark:border-gray-700/50 pb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            Your Shelf
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-show="selectedTracks.length < 3">Select <span x-text="3 - selectedTracks.length" class="font-bold text-indigo-600 dark:text-indigo-400"></span> more to continue</p>
                        <p class="text-sm text-green-600 dark:text-green-400 mt-1 flex items-center gap-1" x-show="selectedTracks.length >= 3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Looking good! You can save now or add more.
                        </p>
                    </div>
                    <div class="text-xl font-bold font-mono" :class="selectedTracks.length >= 3 ? 'text-green-600 dark:text-green-400' : 'text-indigo-600 dark:text-indigo-400'">
                        <span x-text="selectedTracks.length"></span> / 10
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                    <template x-for="track in selectedTracks" :key="track.id">
                        <div class="relative group rounded-2xl overflow-hidden shadow-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 transition transform hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/20">
                            <img :src="track.album?.images[0]?.url || '/images/default-album.png'" class="w-full aspect-square object-cover" alt="Album Art">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent opacity-90 group-hover:opacity-100 transition-opacity"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                <h4 class="text-white font-bold text-sm leading-tight line-clamp-2" x-text="track.name"></h4>
                                <p class="text-xs text-indigo-200 dark:text-indigo-300 mt-1 truncate" x-text="track.artists?.[0]?.name || 'Unknown Artist'"></p>
                            </div>
                            <button @click="removeTrack(track.id)" class="absolute top-3 right-3 bg-red-500/90 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-all scale-75 group-hover:scale-100 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Empty placeholders -->
                    <template x-for="i in Math.max(0, 3 - selectedTracks.length)">
                        <div class="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700/50 aspect-square flex items-center justify-center bg-gray-50/50 dark:bg-gray-800/20 text-gray-400 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-500 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                            <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-12 flex justify-center pb-20">
                <button @click="submitShelf" 
                        :disabled="selectedTracks.length < 3 || isSubmitting"
                        class="w-full sm:w-auto px-12 py-5 rounded-2xl font-bold text-lg text-white shadow-lg transition-all duration-300 transform flex items-center justify-center gap-3"
                        :class="selectedTracks.length >= 3 ? 'bg-indigo-600 hover:bg-indigo-500 hover:-translate-y-1 hover:shadow-indigo-500/30' : 'bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-700 cursor-not-allowed'">
                    
                    <span x-show="!isSubmitting">Complete Onboarding</span>
                    <svg x-show="!isSubmitting" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    
                    <span x-show="isSubmitting" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Optimizing your feed...
                    </span>
                </button>
            </div>
        </div>



        <!-- Toast Notification (Top Center) -->
        <div x-show="errorMessage" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm"
             style="display: none;">
            <div class="bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-red-400">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="errorMessage" class="font-bold"></span>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('onboardingApp', () => ({
                    searchQuery: '',
                    searchResults: [],
                    selectedTracks: [],
                    isSearching: false,
                    isSubmitting: false,
                    showSkipModal: false,
                    errorMessage: '',

                    showError(msg) {
                        this.errorMessage = msg;
                        setTimeout(() => { this.errorMessage = ''; }, 4000);
                    },

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

                isSelected(trackId) {
                    return this.selectedTracks.some(t => t.id === trackId);
                },

                toggleTrack(track) {
                    if (this.isSelected(track.id)) {
                        this.removeTrack(track.id);
                    } else {
                        if (this.selectedTracks.length < 10) {
                            this.selectedTracks.push(track);
                        } else {
                            this.showError("You can only select up to 10 tracks.");
                        }
                    }
                },

                removeTrack(trackId) {
                    this.selectedTracks = this.selectedTracks.filter(t => t.id !== trackId);
                },

                async submitShelf() {
                    if (this.selectedTracks.length < 3) return;
                    
                    this.isSubmitting = true;
                    const songIds = this.selectedTracks.map(t => t.id);

                    try {
                        const response = await fetch('{{ route('onboarding.genres.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ song_ids: songIds })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            window.location.href = data.redirect;
                        } else {
                            this.showError('Failed to save your shelf. Please try again.');
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        this.showError('An error occurred. Please try again.');
                        this.isSubmitting = false;
                    }
                }
            }));
        });
    </script>
        @livewireScripts
    </body>
</html>
