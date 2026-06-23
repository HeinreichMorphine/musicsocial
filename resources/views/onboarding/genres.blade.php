<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Curate Your Shelf - {{ config('app.name', 'Reso') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>document.documentElement.classList.remove('dark');</script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.3); border-radius: 3px; }

        @keyframes popIn {
            0%   { opacity: 0; transform: scale(0.75); }
            70%  { transform: scale(1.06); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-pop { animation: popIn 0.28s cubic-bezier(0.34,1.56,0.64,1) forwards; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    {{-- Single top message: keep banner, no competing subtext --}}
    <div class="sticky top-0 z-50 bg-indigo-50 border-b border-indigo-100 py-2 px-4 text-center text-xs font-medium text-indigo-600 tracking-wide">
        Songs you pick here train your taste profile — better matches, better people to follow.
    </div>

    <div class="min-h-screen flex flex-col items-center pt-12 pb-28 px-4 sm:px-6"
         x-data="onboardingApp()">

        <div class="w-full max-w-xl space-y-8">

            {{-- Header: one bold headline, one light tagline. Nothing else. --}}
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                    Curate Your Song Shelf
                </h1>
                <p class="mt-3 text-base text-slate-400 font-normal">
                    Tap a trending track or search for your own.
                </p>
            </div>

            {{-- Search + Trending: ONE unified component --}}
            <div class="relative" x-data="{ open: true }">

                {{-- Search input --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-300 transition-colors"
                             :class="searchQuery.length > 0 && 'text-indigo-400'"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.400ms="performSearch"
                           :disabled="isSubmitting"
                           x-ref="searchInput"
                           x-init="$nextTick(() => $refs.searchInput.focus())"
                           class="block w-full pl-11 pr-4 py-4 border-0 rounded-2xl bg-white text-slate-900 placeholder-slate-300 text-base shadow-md focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all disabled:opacity-40"
                           placeholder="Search artist, album, or track…">
                </div>

                {{-- Unified results panel: trending (default) OR search results --}}
                <div class="mt-2 bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden"
                     x-show="(searchResults.length > 0 && searchQuery.length >= 3) || (searchQuery.length < 3 && trendingTracks.length > 0)"
                     x-cloak>

                    {{-- Panel label --}}
                    <div class="px-4 pt-3 pb-1">
                        <span class="text-[10px] font-semibold text-slate-300 uppercase tracking-widest"
                              x-text="searchQuery.length >= 3 ? 'Results' : 'Trending'">
                        </span>
                    </div>

                    {{-- Loading --}}
                    <div x-show="isSearching" class="py-6 flex justify-center">
                        <svg class="animate-spin h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </div>

                    {{-- Rows: trending or search results share the same row markup --}}
                    <ul x-show="!isSearching" class="divide-y divide-slate-50 max-h-72 overflow-y-auto custom-scrollbar">
                        <template x-for="track in (searchQuery.length >= 3 ? searchResults : trendingTracks)" :key="track.id">
                            <li @click="toggleTrack(track)"
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors select-none"
                                :class="isSelected(track.id) && 'bg-indigo-50'">
                                <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0 shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-900 truncate" x-text="track.name"></div>
                                    <div class="text-xs text-slate-400 truncate"
                                         x-text="track.artists?.map(a => a.name).join(', ')"></div>
                                </div>
                                {{-- Check mark when selected --}}
                                <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center transition-all"
                                     :class="isSelected(track.id) ? 'bg-indigo-600' : 'border border-slate-200'">
                                    <svg x-show="isSelected(track.id)" class="w-3 h-3 text-white"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </li>
                        </template>
                    </ul>

                    {{-- No results copy --}}
                    <div x-show="!isSearching && searchQuery.length >= 3 && searchResults.length === 0"
                         class="px-4 py-5 text-sm text-slate-400 text-center">
                        Nothing found for "<span x-text="searchQuery" class="text-indigo-500"></span>". Try another name.
                    </div>
                </div>
            </div>

            {{-- Your Shelf: minimal label + horizontal strip --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-slate-500">Your Shelf</span>
                    {{-- Single progress text — replaces ALL other counters --}}
                    <span class="text-xs text-slate-400"
                          x-text="selectedTracks.length === 0
                              ? '0 of 10 added'
                              : selectedTracks.length < 5
                                  ? selectedTracks.length + ' of 10 — ' + (5 - selectedTracks.length) + ' more to unlock'
                                  : selectedTracks.length + ' of 10 added'">
                    </span>
                </div>

                {{-- Horizontal album art strip --}}
                <div class="flex items-center gap-2 p-3 rounded-2xl border-2 transition-all duration-300 overflow-x-auto"
                     :class="selectedTracks.length >= 5
                         ? 'bg-indigo-50/60 border-indigo-200'
                         : 'bg-slate-50 border-dashed border-slate-200'">

                    {{-- Empty state --}}
                    <div x-show="selectedTracks.length === 0"
                         class="flex-1 text-center text-sm text-slate-300 py-3 select-none">
                        Tap any track above to start filling your shelf
                    </div>

                    {{-- Album art thumbnails, left-to-right fill --}}
                    <template x-for="track in selectedTracks" :key="track.id">
                        <div class="relative group flex-shrink-0 animate-pop">
                            <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                 :alt="track.name"
                                 class="w-12 h-12 rounded-xl object-cover shadow-sm">
                            <button @click.stop="removeTrack(track.id)"
                                    type="button"
                                    class="absolute -top-1 -right-1 w-4 h-4 bg-slate-800 text-white rounded-full text-[9px] font-bold leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow"
                                    aria-label="Remove track">
                                ✕
                            </button>
                        </div>
                    </template>

                    {{-- Emerald dot when shelf is complete --}}
                    <div x-show="selectedTracks.length >= 5"
                         class="ml-auto flex-shrink-0 flex items-center gap-1.5 pr-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs text-emerald-600 font-medium">Ready</span>
                    </div>
                </div>
            </div>

            {{-- CTA: progressive fill, single action --}}
            <button @click="submitShelf"
                    :disabled="selectedTracks.length < 5 || isSubmitting"
                    type="button"
                    class="relative w-full py-5 rounded-[1.5rem] font-bold text-lg tracking-wide transition-all duration-500 overflow-hidden bg-slate-200"
                    :class="selectedTracks.length >= 5
                        ? 'shadow-xl shadow-indigo-300/40 hover:-translate-y-0.5 hover:shadow-2xl cursor-pointer'
                        : 'cursor-not-allowed'">

                {{-- Indigo gradient layer, opacity grows 0→1 as tracks are added --}}
                <span class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 transition-opacity duration-500 rounded-[inherit]"
                      :style="'opacity:' + Math.min(1, Math.max(0, selectedTracks.length / 5))">
                </span>

                {{-- Label, z-above the gradient --}}
                <span class="relative z-10 flex items-center justify-center gap-3 transition-colors duration-300"
                      :class="selectedTracks.length >= 5 ? 'text-white' : 'text-slate-400'">
                    <span x-show="!isSubmitting">Complete Onboarding</span>
                    <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <span x-show="isSubmitting" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Building your feed…
                    </span>
                </span>
            </button>

        </div>

        {{-- Error toast --}}
        <div x-show="errorMessage"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="fixed top-16 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm"
             style="display:none;">
            <div class="bg-red-500 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-semibold">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </div>

        {{-- "Nice pick" micro-toast --}}
        <div x-show="nicePickMsg"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0 translate-y-3"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100] pointer-events-none"
             style="display:none;">
            <div class="bg-slate-900 text-white px-5 py-2.5 rounded-2xl shadow-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span x-text="nicePickMsg"></span>
            </div>
        </div>
    </div>



    <script>
        const GENRE_HINTS = {
            'pop':'pop','hip hop':'hip-hop','rap':'hip-hop','r&b':'R&B','soul':'soul',
            'jazz':'jazz','rock':'rock','indie':'indie','electronic':'electronic',
            'edm':'EDM','classical':'classical','country':'country','metal':'metal',
            'punk':'punk','latin':'latin','afrobeats':'afrobeats','kpop':'K-pop',
            'reggae':'reggae','blues':'blues','folk':'folk',
        };

        function guessGenre(track) {
            const hay = [track.name, ...(track.artists||[]).map(a=>a.name), track.album?.name||'']
                .join(' ').toLowerCase();
            for (const [kw, lbl] of Object.entries(GENRE_HINTS)) {
                if (hay.includes(kw)) return lbl;
            }
            return null;
        }

        // Server-supplied suggested/trending tracks (passed from OnboardingController)
        const TRENDING_TRACKS = @json($suggestedTracks ?? []);

        document.addEventListener('alpine:init', () => {
            Alpine.data('onboardingApp', () => ({
                searchQuery:    '',
                searchResults:  [],
                trendingTracks: TRENDING_TRACKS,
                selectedTracks: [],
                isSearching:    false,
                isSubmitting:   false,
                errorMessage:   '',
                nicePickMsg:    '',
                _pickTimer:     null,

                showError(msg) {
                    this.errorMessage = msg;
                    setTimeout(() => { this.errorMessage = ''; }, 4000);
                },

                showNicePick(track) {
                    const genre = guessGenre(track);
                    this.nicePickMsg = genre ? `Added — you're into ${genre}` : `Added to your shelf`;
                    clearTimeout(this._pickTimer);
                    this._pickTimer = setTimeout(() => { this.nicePickMsg = ''; }, 2000);
                },

                updateCtaFill() { /* opacity is declarative via :style — no-op */ },

                async performSearch() {
                    if (this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }
                    this.isSearching = true;
                    try {
                        const r = await fetch(`/search/tracks?query=${encodeURIComponent(this.searchQuery)}`);
                        if (r.ok) {
                            const data = await r.json();
                            this.searchResults = Array.isArray(data) ? data : [];
                        }
                    } catch(e) {
                        console.error('Search failed:', e);
                    } finally {
                        this.isSearching = false;
                    }
                },

                isSelected(id) {
                    return this.selectedTracks.some(t => t.id === id);
                },

                toggleTrack(track) {
                    if (this.isSelected(track.id)) {
                        this.removeTrack(track.id);
                    } else {
                        if (this.selectedTracks.length < 10) {
                            this.selectedTracks.push(track);
                            this.showNicePick(track);
                        } else {
                            this.showError('Maximum 10 tracks — remove one to swap.');
                        }
                    }
                },

                removeTrack(id) {
                    this.selectedTracks = this.selectedTracks.filter(t => t.id !== id);
                },

                async submitShelf() {
                    if (this.selectedTracks.length < 5) return;
                    this.isSubmitting = true;
                    try {
                        const r = await fetch('{{ route('onboarding.genres.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ song_ids: this.selectedTracks.map(t => t.id) })
                        });
                        if (r.ok) {
                            const data = await r.json();
                            window.location.href = data.redirect;
                        } else {
                            this.showError('Could not save your shelf. Please try again.');
                            this.isSubmitting = false;
                        }
                    } catch(e) {
                        console.error('Submission error:', e);
                        this.showError('Something went wrong. Please try again.');
                        this.isSubmitting = false;
                    }
                }
            }));
        });
    </script>
    @livewireScripts
</body>
</html>
