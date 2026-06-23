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

        @keyframes popIn {
            0%   { opacity: 0; transform: scale(0.6); }
            65%  { transform: scale(1.08); }
            100% { opacity: 1; transform: scale(1); }
        }
        .slot-pop { animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    {{-- Single top strip — one sentence only --}}
    <div class="sticky top-0 z-50 bg-indigo-50/90 backdrop-blur-sm border-b border-indigo-100 py-2 px-4 text-center text-xs font-medium text-indigo-600 tracking-wide">
        Songs you pick here train your taste profile — better matches, better people to follow.
    </div>

    <div class="min-h-screen flex flex-col items-center pt-10 pb-28 px-4 sm:px-6"
         x-data="onboardingApp()">

        <div class="w-full max-w-lg space-y-6">

            {{-- Headline: clear chapter heading, unmistakably first read --}}
            <div class="text-center pt-8 pb-1">
                {{-- 32px font-black — wins the page, nothing else comes close --}}
                <h1 class="text-[2rem] leading-snug font-black text-slate-900 tracking-tight">
                    Curate Your Song Shelf
                </h1>
                {{-- Subtext does double duty: guidance for first-timers, no extra block needed --}}
                <p class="mt-4 text-[13px] text-slate-500 font-normal leading-relaxed max-w-sm mx-auto">
                    New here? Tap a trending track, or search for your own
                    — pick 5 to 10 to get started.
                </p>
            </div>

            {{-- Search bar (above unified card, separate) --}}
            <div class="relative z-30">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-300 transition-colors"
                             :class="searchQuery.length > 0 && 'text-indigo-400'"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.400ms="performSearch"
                           :disabled="isSubmitting"
                           x-ref="searchInput"
                           x-init="$nextTick(() => $refs.searchInput.focus())"
                           class="block w-full pl-11 pr-4 py-3.5 rounded-2xl border-0 bg-white text-slate-900 placeholder-slate-300 text-base shadow-md focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all"
                           :placeholder="placeholders[placeholderIdx]">
                    {{-- Spinner inside input --}}
                    <div x-show="isSearching" x-cloak class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="animate-spin h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </div>
                </div>

                {{-- Search results: absolute overlay (only when actively searching) --}}
                <div x-show="searchQuery.length >= 3 && (searchResults.length > 0 || (!isSearching && searchQuery.length >= 3))"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden max-h-60 overflow-y-auto z-40">
                    {{-- No results --}}
                    <div x-show="!isSearching && searchResults.length === 0"
                         class="px-4 py-5 text-sm text-slate-400 text-center">
                        Nothing found for "<span x-text="searchQuery" class="text-indigo-500"></span>".
                    </div>
                    {{-- Result rows --}}
                    <ul x-show="searchResults.length > 0">
                        <template x-for="track in searchResults" :key="track.id">
                            <li @click="toggleTrack(track)"
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0"
                                :class="isSelected(track.id) && 'bg-indigo-50/60'">
                                <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                     class="w-9 h-9 rounded-lg object-cover flex-shrink-0 shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-800 truncate" x-text="track.name"></div>
                                    <div class="text-xs text-slate-400 truncate" x-text="track.artists?.map(a => a.name).join(', ')"></div>
                                </div>
                                <div class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                     :class="isSelected(track.id) ? 'bg-indigo-600 border-indigo-600' : 'border-slate-200'">
                                    <svg x-show="isSelected(track.id)" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- ONE unified card: Trending + Shelf, single container --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- ── SECTION 1: Trending ── --}}
                {{-- Fix 4: section label to text-xs 12px — same weight and size as "YOUR SHELF" --}}
                <div class="px-6 pt-5 pb-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Trending — tap to add
                    </p>
                    <ul class="divide-y divide-slate-50">
                        @forelse($suggestedTracks ?? [] as $st)
                            @php
                                $stId     = $st['id'] ?? '';
                                $stName   = $st['name'] ?? 'Unknown';
                                $stArt    = $st['album']['images'][0]['url'] ?? '';
                                $stArtist = collect($st['artists'] ?? [])->pluck('name')->join(', ');
                            @endphp
                            @if($stId)
                                <li x-data='{
                                        track: {
                                            id:      {{ json_encode($stId) }},
                                            name:    {{ json_encode($stName) }},
                                            artists: [{ name: {{ json_encode($stArtist) }} }],
                                            album:   { images: [{ url: {{ json_encode($stArt) }} }] }
                                        }
                                    }'
                                    @click="toggleTrack(track)"
                                    {{-- Fix 1: py-3 → py-3.5 for breathing room --}}
                                    class="flex items-center gap-3 py-3.5 cursor-pointer hover:bg-slate-50/80 transition-colors -mx-6 px-6 group"
                                    :class="isSelected({{ json_encode($stId) }}) && 'bg-indigo-50/50'">
                                    <img src="{{ $stArt }}"
                                         alt="{{ $stName }}"
                                         class="w-10 h-10 rounded-xl object-cover flex-shrink-0 shadow-sm">
                                    <div class="flex-1 min-w-0">
                                        {{-- Fix 2: title 15px semibold, artist 12px regular lighter --}}
                                        <div class="text-[15px] font-semibold text-slate-800 truncate">{{ $stName }}</div>
                                        <div class="text-[12px] font-normal text-slate-400 truncate mt-0.5">{{ $stArtist }}</div>
                                    </div>
                                    {{-- Fix 2: Circle 20→24px, solid purple fill + check on selected, hover ring --}}
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                         :class="isSelected({{ json_encode($stId) }})
                                             ? 'bg-indigo-600 border-indigo-600 scale-110'
                                             : 'border-slate-200 group-hover:border-indigo-300 group-hover:scale-105'">
                                        <svg x-show="isSelected({{ json_encode($stId) }})" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </li>
                            @endif
                        @empty
                            <li class="py-5 text-xs text-slate-300 text-center">No trending tracks available right now.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Thin divider --}}
                <div class="mx-6 my-3 border-t border-slate-100"></div>

                {{-- ── SECTION 2: Shelf ── --}}
                {{-- Fix 5: inner padding px-6 --}}
                <div class="px-6 pb-6">
                    {{-- Fix 4: "YOUR SHELF" matches trending label: text-xs 12px uppercase --}}
                    <div class="flex items-center mb-4">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Your Shelf</span>
                        <span class="mx-1.5 text-slate-300 text-xs leading-none">·</span>
                        {{-- 0/10 counter: slightly more prominent since it's the actionable info --}}
                        <span class="text-xs font-bold uppercase tracking-widest transition-colors duration-300"
                              :class="selectedTracks.length >= 5 ? 'text-emerald-500' : 'text-slate-500'"
                              x-text="selectedTracks.length + '/10'">
                        </span>
                        <span class="mx-1.5 text-slate-200 text-xs leading-none">·</span>
                        <span class="text-xs text-slate-400 transition-all duration-300"
                              x-show="selectedTracks.length < 5"
                              x-text="'pick ' + (5 - selectedTracks.length) + ' more to unlock'">
                        </span>
                        <span class="text-xs text-emerald-500 font-semibold"
                              x-show="selectedTracks.length >= 5">
                            ready to continue
                        </span>
                    </div>

                    {{-- Fix 3 (Option A): Dynamic shelf — only filled slots + one ghost "+" badge --}}
                    {{-- The row grows as the user picks tracks; no pre-rendered empty boxes --}}
                    <div class="flex items-center gap-2 flex-wrap">

                        {{-- Filled slots: appear as tracks are picked, animate in --}}
                        <template x-for="(track, idx) in selectedTracks" :key="track.id">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 group"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-50"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <img :src="track.album?.images[0]?.url"
                                     :alt="track.name"
                                     class="w-12 h-12 object-cover">
                                <button type="button"
                                        @click.stop="removeTrack(track.id)"
                                        class="absolute inset-0 bg-black/55 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-150">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        {{-- Fix 5: ghost badge = same 48px as album art slots, larger text --}}
                        <div x-show="selectedTracks.length < 10"
                             class="flex flex-col items-center justify-center w-12 h-12 rounded-xl flex-shrink-0 border-2 border-dashed transition-all duration-300"
                             :class="selectedTracks.length >= 5
                                 ? 'border-emerald-200 bg-emerald-50'
                                 : 'border-indigo-200 bg-indigo-50'">
                            <span class="text-sm font-black leading-none"
                                  :class="selectedTracks.length >= 5 ? 'text-emerald-400' : 'text-indigo-400'"
                                  x-text="selectedTracks.length < 5 ? '+' + (5 - selectedTracks.length) : '+'">
                            </span>
                        </div>

                    </div>

                    {{-- First-time shelf hint: inline below strip, localStorage-gated, dismissable --}}
                    <div x-show="showShelfTip && selectedTracks.length === 0"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-3 flex items-center gap-2 text-xs text-indigo-500/70">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span>Your picks will appear here — tap any track to add it.</span>
                        <button @click="dismissShelfTip()"
                                type="button"
                                class="ml-auto text-slate-300 hover:text-slate-500 transition-colors text-base leading-none shrink-0">
                            &times;
                        </button>
                    </div>

                </div>
            </div>{{-- end unified card --}}

            {{-- Fix 6: CTA gap pt-2 → pt-8 so it reads as a clear separate action --}}
            <div class="pt-8">
                <button @click="submitShelf"
                        :disabled="selectedTracks.length < 5 || isSubmitting"
                        type="button"
                        class="relative w-full py-5 rounded-2xl font-bold text-base tracking-wide overflow-hidden bg-slate-200 transition-all duration-500"
                        :class="selectedTracks.length >= 5
                            ? 'shadow-lg shadow-indigo-300/40 hover:-translate-y-0.5 hover:shadow-xl cursor-pointer'
                            : 'cursor-not-allowed'">

                    {{-- Indigo layer fills as tracks accumulate --}}
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[inherit] transition-opacity duration-500"
                          :style="'opacity:' + Math.min(1, Math.max(0, selectedTracks.length / 5))">
                    </span>

                    <span class="relative z-10 flex items-center justify-center gap-2 transition-colors duration-300"
                          :class="selectedTracks.length >= 5 ? 'text-white' : 'text-slate-400'">
                        <span x-show="!isSubmitting">Complete Onboarding</span>
                        <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Building your feed…
                        </span>
                    </span>
                </button>
            </div>

        </div>

        {{-- Error toast --}}
        <div x-show="errorMessage"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="fixed top-14 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm px-4"
             style="display:none;">
            <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-medium">
                <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </div>

        {{-- "Added" micro-toast --}}
        <div x-show="nicePickMsg"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100] pointer-events-none"
             style="display:none;">
            <div class="bg-slate-900 text-white px-5 py-2.5 rounded-2xl shadow-xl text-xs font-medium flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
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
            const hay = [track.name, ...(track.artists || []).map(a => a.name), track.album?.name || '']
                .join(' ').toLowerCase();
            for (const [kw, lbl] of Object.entries(GENRE_HINTS)) {
                if (hay.includes(kw)) return lbl;
            }
            return null;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('onboardingApp', () => ({
                searchQuery:    '',
                searchResults:  [],
                selectedTracks: [],
                isSearching:    false,
                isSubmitting:   false,
                errorMessage:   '',
                nicePickMsg:    '',
                _pickTimer:     null,

                // Rotating search placeholder
                placeholders: [
                    'Search artist, album, or track…',
                    "Try 'Taylor Swift' or 'Anti-Hero'…",
                    "Try 'The Weeknd' or 'Blinding Lights'…",
                    "Try 'Kendrick Lamar' or 'HUMBLE.'…",
                ],
                placeholderIdx:  0,
                _phTimer:        null,

                // First-time shelf hint
                showShelfTip: !localStorage.getItem('hasSeenShelfTip'),

                dismissShelfTip() {
                    this.showShelfTip = false;
                    localStorage.setItem('hasSeenShelfTip', 'true');
                },

                showError(msg) {
                    this.errorMessage = msg;
                    setTimeout(() => { this.errorMessage = ''; }, 4000);
                },

                showNicePick(track) {
                    const genre = guessGenre(track);
                    this.nicePickMsg = genre ? `Added — you're into ${genre}` : `Added to your shelf`;
                    clearTimeout(this._pickTimer);
                    this._pickTimer = setTimeout(() => { this.nicePickMsg = ''; }, 1800);
                },

                init() {
                    // Rotate placeholder every 3.5s when user isn't typing
                    this._phTimer = setInterval(() => {
                        if (this.searchQuery.length === 0) {
                            this.placeholderIdx = (this.placeholderIdx + 1) % this.placeholders.length;
                        }
                    }, 3500);
                },

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
                    } catch (e) {
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
                    if (!id) return;
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
                    } catch (e) {
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
