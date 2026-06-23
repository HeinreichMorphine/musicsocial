<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Curate Your Taste Profile - {{ config('app.name', 'Reso') }}</title>

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

        @keyframes subtlePulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(22, 42, 114, 0.3); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 8px rgba(22, 42, 114, 0); }
        }
        .pulse-glow {
            animation: subtlePulse 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-50/50 text-slate-900 antialiased selection:bg-custom-periwinkle selection:text-custom-dark-blue">

    {{-- Single top strip --}}
    <div class="sticky top-0 z-50 bg-custom-periwinkle/80 backdrop-blur-sm border-b border-custom-periwinkle/30 py-2.5 px-4 text-center text-xs md:text-sm font-bold text-custom-dark-blue tracking-wide">
        Builds your taste profile for better recommendations & matches.
    </div>

    {{-- Page container --}}
    <div class="min-h-screen w-full flex flex-col items-center pt-2 md:pt-10 pb-32 md:pb-20 px-4 md:px-6 bg-gradient-to-b from-slate-50 via-white to-slate-50/50 relative overflow-x-hidden"
         x-data="onboardingApp()">

        {{-- Subtle hero backdrop gradient fading out --}}
        <div class="absolute top-0 inset-x-0 h-[280px] bg-gradient-to-b from-custom-periwinkle/25 to-transparent pointer-events-none z-0"></div>

        <div class="w-full max-w-lg space-y-4 md:space-y-6 relative z-10">

            {{-- Headline --}}
            <div class="text-center pt-2 md:pt-8 pb-0 md:pb-1 mt-1 md:mt-4 mb-1 md:mb-4">
                <h1 class="text-[2rem] md:text-[2.5rem] leading-tight font-black text-slate-900 tracking-tight">
                    Let's build your <span class="text-custom-dark-blue">taste profile</span>
                </h1>
                <p class="mt-2.5 md:mt-3 text-sm md:text-base text-slate-600 font-medium leading-relaxed max-w-sm mx-auto">
                    Search for a few songs you love.
                </p>
            </div>

            {{-- Search bar container --}}
            <div class="relative z-30 space-y-3 px-4 md:px-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-300 transition-colors"
                             :class="searchQuery.length > 0 && 'text-custom-dark-blue'"
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
                           class="block w-full pl-11 pr-4 py-3 md:py-3.5 rounded-xl md:rounded-2xl border border-custom-periwinkle/30 hover:border-custom-periwinkle/60 bg-white text-slate-900 placeholder-slate-400 text-base shadow-[0_4px_20px_rgba(22,42,114,0.03)] focus:ring-4 focus:ring-custom-mid-blue/15 focus:border-custom-mid-blue focus:outline-none transition-all"
                           :placeholder="placeholders[placeholderIdx]">
                    {{-- Spinner inside input --}}
                    <div x-show="isSearching" x-cloak class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="animate-spin h-4 w-4 text-custom-dark-blue" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </div>
                </div>

                {{-- Two-Tier Assistive Genre tags --}}
                <div class="text-center space-y-1.5 md:space-y-3 pt-0.5 md:pt-2 pb-3 md:pb-0">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">Or tap a vibe to get started</span>
                    <div class="flex flex-wrap gap-1.5 md:gap-2 justify-center w-full max-w-md mx-auto items-center">
                        <!-- Broad genres -->
                        <template x-for="genre in broadGenres" :key="genre">
                            <button type="button"
                                    @click="selectTag(genre)"
                                    class="px-3 py-1.5 md:px-4 md:py-2.5 rounded-full text-[11px] md:text-sm font-bold transition-all duration-200 shadow-sm border focus:outline-none inline-flex items-center justify-center gap-1.5 md:gap-2 active:scale-95"
                                    :class="activeTag === genre 
                                        ? 'bg-custom-mid-blue border-custom-mid-blue text-white shadow-md scale-[1.03]' 
                                        : 'bg-custom-periwinkle/10 border-custom-periwinkle/25 hover:border-custom-periwinkle/45 hover:bg-custom-periwinkle/20 text-custom-dark-blue/80 hover:text-custom-dark-blue'">
                                <span class="w-1.5 h-1.5 rounded-full block shrink-0" 
                                      :class="activeTag === genre ? 'bg-white' : 'bg-custom-periwinkle'"></span>
                                <span x-text="genre"></span>
                            </button>
                        </template>
                        
                        <!-- Niche genres -->
                        <template x-for="genre in nicheGenres" :key="genre">
                            <button type="button"
                                    x-show="showAllGenres"
                                    x-transition:enter="transition ease-out duration-200 transform"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click="selectTag(genre)"
                                    class="px-3 py-1.5 md:px-4 md:py-2.5 rounded-full text-[11px] md:text-sm font-bold transition-all duration-200 shadow-sm border focus:outline-none inline-flex items-center justify-center gap-1.5 md:gap-2 active:scale-95"
                                    :class="activeTag === genre 
                                        ? 'bg-custom-mid-blue border-custom-mid-blue text-white shadow-md scale-[1.03]' 
                                        : 'bg-custom-periwinkle/10 border-custom-periwinkle/25 hover:border-custom-periwinkle/45 hover:bg-custom-periwinkle/20 text-custom-dark-blue/80 hover:text-custom-dark-blue'">
                                <span class="w-1.5 h-1.5 rounded-full block shrink-0" 
                                      :class="activeTag === genre ? 'bg-white' : 'bg-custom-periwinkle'"></span>
                                <span x-text="genre"></span>
                            </button>
                        </template>

                        <!-- Expander Button: styled matching regular tags for inline consistency -->
                        <button type="button"
                                @click="showAllGenres = !showAllGenres"
                                class="px-3 py-1.5 md:px-4 md:py-2.5 rounded-full text-[11px] md:text-sm font-bold transition-all duration-200 shadow-sm border border-custom-periwinkle/25 bg-custom-periwinkle/10 hover:border-custom-periwinkle/45 hover:bg-custom-periwinkle/20 text-custom-dark-blue/80 hover:text-custom-dark-blue focus:outline-none inline-flex items-center justify-center gap-1.5 active:scale-95">
                            <span x-text="showAllGenres ? 'Less genres' : 'More genres'"></span>
                            <svg class="w-3 h-3 md:w-4 md:h-4 text-custom-slate-blue transition-transform duration-200" :class="showAllGenres && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Unified card with colored top border strip --}}
            <div class="bg-white md:rounded-2xl border-t-4 border-t-custom-dark-blue border-0 md:border md:border-custom-periwinkle/25 shadow-none md:shadow-[0_15px_50px_rgba(22,42,114,0.04)] overflow-hidden">

                {{-- ── SECTION 1: Suggestions ── --}}
                <div class="px-4 pt-3 md:px-6 md:pt-5 pb-1">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[13px] font-bold text-custom-slate-blue uppercase tracking-widest"
                           x-text="suggestionsHeader">
                        </p>
                        <!-- Loading spinner for either search or genre fetch -->
                        <div x-show="isSearching || isLoadingGenre" x-cloak class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-custom-dark-blue" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <span class="text-[10px] font-semibold text-custom-dark-blue uppercase tracking-wider"
                                  x-text="isSearching ? 'Searching...' : 'Loading...'"></span>
                        </div>
                    </div>
                    
                    <!-- Suggestions list -->
                    <ul class="divide-y divide-slate-50" x-show="!(isSearching || isLoadingGenre)">
                        <!-- Search prompts when query length is 1 or 2 -->
                        <template x-if="searchQuery.length > 0 && searchQuery.length < 3">
                            <li class="py-5 text-sm text-slate-400 text-center">
                                Type at least 3 characters to search...
                            </li>
                        </template>

                        <!-- Live list (Curated, Genre, or Search Results) -->
                        <template x-for="track in displayedSuggestions" :key="track.id">
                            <li @click="toggleTrack(track)"
                                class="flex items-center gap-3 py-2.5 md:py-3.5 cursor-pointer hover:bg-custom-periwinkle/5 hover:bg-opacity-80 active:scale-[0.98] transition-all duration-75 -mx-4 px-4 md:-mx-6 md:px-6 group"
                                :class="isSelected(track.id) && 'bg-custom-periwinkle/15'">
                                <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                     :alt="track.name"
                                     class="w-10 h-10 rounded-xl object-cover flex-shrink-0 shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-base font-semibold text-slate-800 truncate" x-text="track.name"></div>
                                    <div class="text-sm font-normal text-slate-500 truncate mt-0.5" x-text="getArtistName(track)"></div>
                                </div>
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                     :class="isSelected(track.id)
                                         ? 'bg-custom-dark-blue border-custom-dark-blue scale-110'
                                         : 'border-custom-periwinkle/45 group-hover:border-custom-periwinkle group-hover:scale-105'">
                                    <svg x-show="isSelected(track.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </li>
                        </template>

                        <!-- Empty state when search returns nothing -->
                        <template x-if="searchQuery.length >= 3 && !isSearching && displayedSuggestions.length === 0">
                            <li class="py-5 text-sm text-slate-400 text-center">
                                Nothing found for "<span x-text="searchQuery" class="text-custom-dark-blue font-semibold"></span>".
                            </li>
                        </template>

                        <!-- Empty state when genre tag has no suggestions -->
                        <template x-if="searchQuery.length === 0 && activeTag && !isLoadingGenre && displayedSuggestions.length === 0">
                            <li class="py-5 text-sm text-slate-400 text-center">
                                No tracks available in this vibe right now.
                            </li>
                        </template>
                    </ul>
                    
                    <!-- Loading Skeletons -->
                    <div class="space-y-3 py-3" x-show="isSearching || isLoadingGenre" x-cloak>
                        <template x-for="i in [1, 2, 3]" :key="i">
                            <div class="flex items-center gap-3 py-1 px-6 -mx-6">
                                <div class="w-10 h-10 bg-slate-100 animate-pulse rounded-xl flex-shrink-0"></div>
                                <div class="flex-1 space-y-2 min-w-0">
                                    <div class="h-3.5 bg-slate-100 animate-pulse rounded-md w-3/4"></div>
                                    <div class="h-2.5 bg-slate-100 animate-pulse rounded-md w-1/2"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Thin divider --}}
                <div class="mx-0 md:mx-6 my-2 md:my-3 border-t border-slate-100"></div>

                {{-- ── SECTION 2: Shelf ── --}}
                <div class="px-4 pb-3 md:px-6 md:pb-6">
                    <div class="flex flex-col md:flex-row md:items-baseline md:justify-between mb-3 md:mb-4 gap-1 md:gap-0">
                        <div class="flex flex-col md:flex-row md:items-baseline gap-1 md:gap-2">
                            {{-- Counter dynamically pops out --}}
                            <span class="text-2xl font-black text-custom-dark-blue leading-none transition-colors duration-300"
                                  x-text="selectedTracks.length >= 5 ? selectedTracks.length + '/10' : selectedTracks.length + '/5'">
                            </span>
                            <span class="text-[11px] md:text-xs font-bold text-slate-400 uppercase tracking-widest leading-none"
                                  x-text="selectedTracks.length >= 5 ? 'Taste Profile: Ready' : 'Taste Profile: Building'">
                            </span>
                        </div>
                        <div class="text-[11px] md:text-xs font-semibold leading-none">
                            <span class="text-slate-500"
                                  x-show="selectedTracks.length < 5"
                                  x-text="'Pick ' + (5 - selectedTracks.length) + ' more to unlock'">
                            </span>
                            <span class="text-emerald-600 flex items-center gap-1.5"
                                  x-show="selectedTracks.length >= 5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                Ready to continue
                            </span>
                        </div>
                    </div>

                    {{-- Shelf row with snappy animation (<400ms) --}}
                    <div class="flex items-center gap-2 flex-wrap">

                        {{-- Filled slots --}}
                        <template x-for="(track, idx) in selectedTracks" :key="track.id">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 group"
                                 x-transition:enter="transition ease-out duration-200"
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

                        {{-- Ghost badge styled with custom-periwinkle / custom-dark-blue pairing --}}
                        <div x-show="selectedTracks.length < 10"
                             class="flex flex-col items-center justify-center w-12 h-12 rounded-xl flex-shrink-0 border-2 border-dashed transition-all duration-300"
                             :class="selectedTracks.length === 0
                                 ? 'pulse-glow border-custom-periwinkle bg-custom-periwinkle/20 text-custom-dark-blue shadow-sm shadow-custom-periwinkle/25'
                                 : (selectedTracks.length >= 5 
                                     ? 'border-emerald-300 bg-emerald-50/50 text-emerald-600' 
                                     : 'border-custom-periwinkle bg-custom-periwinkle/10 text-custom-dark-blue')">
                            <span class="text-sm font-black leading-none"
                                  x-text="selectedTracks.length < 5 ? '+' + (5 - selectedTracks.length) : '+'">
                            </span>
                        </div>

                    </div>

                    {{-- First-time shelf hint: softened background, borders dropped --}}
                    <div x-show="showShelfTip && selectedTracks.length === 0"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-3 md:mt-4 relative bg-custom-periwinkle/15 rounded-xl p-3 md:p-3.5 flex items-start gap-2.5 md:gap-3 border-none shadow-none">
                        <div class="absolute bg-custom-periwinkle/15 w-2.5 h-2.5 -top-[5px] left-6 rotate-45"></div>
                        <svg class="text-custom-dark-blue mt-0.5 animate-bounce flex-shrink-0 w-4 h-4 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-custom-dark-blue text-sm mb-0.5">Quick Tip</p>
                            <p class="text-custom-mid-blue/90 text-xs leading-relaxed">Your picks will appear here. Tap any trending track, search, or click on a vibe above to find your favorites!</p>
                        </div>
                        <button @click="dismissShelfTip()"
                                type="button"
                                class="text-custom-slate-blue hover:text-custom-dark-blue transition-colors text-lg leading-none shrink-0 -mt-1 -mr-1 p-1 cursor-pointer border-none bg-transparent">
                            &times;
                        </button>
                    </div>

                </div>
            </div>{{-- end unified card --}}

            {{-- Sticky bottom CTA bar on mobile --}}
            <div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-sm border-t border-slate-100 px-4 py-4 shadow-[0_-8px_30px_rgba(22,42,114,0.05)] md:relative md:bottom-auto md:inset-x-auto md:bg-transparent md:border-t-0 md:px-0 md:py-0 md:shadow-none z-40">
                <div class="w-full max-w-lg mx-auto">
                    <button @click="submitShelf"
                            :disabled="selectedTracks.length < 5 || isSubmitting"
                            type="button"
                            class="w-full py-4 md:py-5 rounded-2xl font-bold text-base md:text-lg tracking-wide transition-all duration-300 shadow-sm border focus:outline-none"
                            :class="selectedTracks.length >= 5
                                ? 'bg-gradient-to-r from-custom-dark-blue to-custom-mid-blue hover:from-custom-mid-blue hover:to-custom-dark-blue border-transparent text-white shadow-lg shadow-custom-dark-blue/25 hover:shadow-xl hover:shadow-custom-dark-blue/35 active:scale-[0.99] cursor-pointer'
                                : (selectedTracks.length > 0
                                    ? 'bg-custom-periwinkle/15 border-custom-periwinkle/25 text-custom-dark-blue/50 cursor-not-allowed'
                                    : 'bg-custom-periwinkle/10 border-custom-periwinkle/20 text-custom-slate-blue/60 cursor-not-allowed')">

                        <span class="flex items-center justify-center gap-2">
                            <span x-show="!isSubmitting">
                                <template x-if="selectedTracks.length === 0">
                                    <span>Pick 5 tracks to get started</span>
                                </template>
                                <template x-if="selectedTracks.length > 0 && selectedTracks.length < 5">
                                    <span x-text="'Pick ' + (5 - selectedTracks.length) + ' more ' + (5 - selectedTracks.length === 1 ? 'track' : 'tracks')"></span>
                                </template>
                                <template x-if="selectedTracks.length >= 5">
                                    <span>Complete Onboarding</span>
                                </template>
                            </span>
                            <svg x-show="!isSubmitting && selectedTracks.length >= 5" class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                Building your taste profile…
                            </span>
                        </span>
                    </button>

                    {{-- Actionable guidance helper text directly below --}}
                    <p class="mt-2 md:mt-3 text-center text-sm font-semibold transition-colors duration-300"
                       :class="selectedTracks.length >= 5 ? 'text-emerald-600 font-bold' : 'text-slate-500'"
                       x-text="selectedTracks.length >= 5 
                           ? 'Ready to unlock your personalized feed!' 
                           : (5 - selectedTracks.length) + ' more song' + (5 - selectedTracks.length === 1 ? '' : 's') + ' to unlock your personalized feed'">
                    </p>
                </div>
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

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('onboardingApp', () => ({
                searchQuery:    '',
                searchResults:  [],
                selectedTracks: [],
                isSearching:    false,
                isSubmitting:   false,
                errorMessage:   '',

                // Suggested Tracks
                defaultSuggestedTracks: @json($suggestedTracks),
                genreTracks: [],
                activeTag: null,
                showAllGenres: false,
                isLoadingGenre: false,

                broadGenres: ['Pop', 'Hip-hop', 'R&B', 'Rock', 'Latin', 'Electronic', 'Country'],
                nicheGenres: ['Jazz', 'Funk', 'Punk', 'Reggae', 'Metal', 'Afrobeats', 'Lo-Fi', 'Math-Rock'],

                // Rotating search placeholders
                placeholders: [
                    "Try: an artist from your local scene",
                    "Try: a track with less than 10k plays",
                    "Try: the weirdest genre you actually love",
                    "Try: a song you discovered completely by accident",
                    "Try: the best B-side track you know",
                    "Try: the first song you ever downloaded",
                    "Try: your ultimate middle school anthem",
                    "Try: the best song you discovered in a video game",
                    "Try: a track that reminds you of a specific summer",
                    "Try: the first band you saw live",
                    "Try: your go-to late-night drive track",
                    "Try: the song that always resets your mood",
                    "Try: a flawless album opener",
                    "Try: your ultimate rainy day comfort song",
                    "Try: the hardest gym hype track you know",
                    "Try: your undisputed karaoke weapon",
                    "Try: the song you always force your friends to hear",
                    "Try: a track you would play to introduce yourself",
                    "Try: the last song you sent to a friend",
                    "Try: the track you have on repeat right now"
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

                init() {
                    // Rotate placeholder every 3s when user isn't typing
                    this._phTimer = setInterval(() => {
                        if (this.searchQuery.length === 0) {
                            this.placeholderIdx = (this.placeholderIdx + 1) % this.placeholders.length;
                        }
                    }, 3000);
                },

                get displayedSuggestions() {
                    if (this.searchQuery.length > 0) {
                        return this.searchResults;
                    }
                    if (this.activeTag) {
                        return this.genreTracks;
                    }
                    return this.defaultSuggestedTracks;
                },

                get suggestionsHeader() {
                    if (this.searchQuery.length > 0) {
                        return "Search Results — Tap to Add";
                    }
                    if (this.activeTag) {
                        return "Trending in " + this.activeTag + " — Tap to Add";
                    }
                    return "A Bit of Everything — Tap to Add";
                },

                getArtistName(track) {
                    if (!track.artists) return 'Unknown Artist';
                    if (typeof track.artists === 'string') return track.artists;
                    if (Array.isArray(track.artists)) {
                        return track.artists.map(a => typeof a === 'string' ? a : a.name).join(', ');
                    }
                    return 'Unknown Artist';
                },

                async selectTag(genre) {
                    if (this.activeTag === genre) {
                        this.activeTag = null;
                        this.genreTracks = [];
                        return;
                    }
                    // Clear search query if selecting a tag to avoid conflict
                    this.searchQuery = '';
                    this.activeTag = genre;
                    this.isLoadingGenre = true;
                    try {
                        let queryTag = genre.toLowerCase().replace(' ', '-');
                        let r = await fetch(`/search/tracks?query=${encodeURIComponent('genre:' + queryTag)}`);
                        if (r.ok) {
                            let data = await r.json();
                            if (Array.isArray(data) && data.length > 0) {
                                // Slice to 5 maximum suggestions
                                this.genreTracks = data.slice(0, 5);
                            } else {
                                // Try direct term query
                                let r2 = await fetch(`/search/tracks?query=${encodeURIComponent(genre)}`);
                                if (r2.ok) {
                                    let data2 = await r2.json();
                                    this.genreTracks = Array.isArray(data2) ? data2.slice(0, 5) : [];
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Failed to fetch genre tracks:', e);
                    } finally {
                        this.isLoadingGenre = false;
                    }
                },

                async performSearch() {
                    if (this.searchQuery.length < 3) {
                        this.searchResults = [];
                        return;
                    }
                    // Clear active tag when searching
                    this.activeTag = null;
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
                            // Ensure structure is correct
                            const formattedTrack = {
                                id: track.id,
                                name: track.name,
                                artists: track.artists,
                                album: track.album
                            };
                            this.selectedTracks.push(formattedTrack);
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
