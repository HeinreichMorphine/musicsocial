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
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
        }
        .pulse-glow {
            animation: subtlePulse 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    {{-- Single top strip --}}
    <div class="sticky top-0 z-50 bg-indigo-50/90 backdrop-blur-sm border-b border-indigo-100 py-2.5 px-4 text-center text-xs sm:text-sm font-semibold text-indigo-600 tracking-wide">
        Builds your taste profile for better recommendations & matches.
    </div>

    <div class="min-h-screen flex flex-col items-center pt-8 sm:pt-12 pb-28 px-4 sm:px-6"
         x-data="onboardingApp()">

        <div class="w-full max-w-lg space-y-6">

            {{-- Headline --}}
            <div class="text-center pt-8 pb-1" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                <h1 class="text-[2.25rem] sm:text-[2.5rem] leading-tight font-black text-slate-900 tracking-tight">
                    Let's build your <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">taste profile</span>
                </h1>
                <p class="mt-3 text-[15px] sm:text-base text-slate-600 font-medium leading-relaxed max-w-sm mx-auto">
                    Search for a few songs you love.
                </p>
            </div>

            {{-- Search bar container --}}
            <div class="relative z-30 space-y-4">
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
                           class="block w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-100 bg-white text-slate-900 placeholder-slate-300 text-base shadow-md focus:ring-4 focus:ring-indigo-500/10 focus:outline-none transition-all"
                           :placeholder="placeholders[placeholderIdx]">
                    {{-- Spinner inside input --}}
                    <div x-show="isSearching" x-cloak class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="animate-spin h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                    </div>
                </div>

                {{-- Two-Tier Assistive Genre tags --}}
                <div class="text-center space-y-3 pt-2">
                    <span class="text-[13px] font-bold text-slate-400 uppercase tracking-widest block">Or tap a vibe to get started</span>
                    <div class="flex flex-wrap gap-2 justify-center max-w-md mx-auto items-center">
                        <!-- Broad genres -->
                        <template x-for="genre in broadGenres" :key="genre">
                            <button type="button"
                                    @click="selectTag(genre)"
                                    class="px-4 py-2.5 rounded-full text-sm font-semibold transition-all duration-200 shadow-sm border focus:outline-none inline-flex items-center justify-center gap-2 active:scale-95"
                                    :class="activeTag === genre 
                                        ? 'bg-gradient-to-r from-indigo-600 to-violet-600 border-indigo-600 text-white shadow-indigo-100 scale-105' 
                                        : 'bg-white border-slate-100 hover:border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800'">
                                <span class="w-1.5 h-1.5 rounded-full block shrink-0" 
                                      :class="activeTag === genre ? 'bg-indigo-200' : 'bg-indigo-400'"></span>
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
                                    class="px-4 py-2.5 rounded-full text-sm font-semibold transition-all duration-200 shadow-sm border focus:outline-none inline-flex items-center justify-center gap-2 active:scale-95"
                                    :class="activeTag === genre 
                                        ? 'bg-gradient-to-r from-indigo-600 to-violet-600 border-indigo-600 text-white shadow-indigo-100 scale-105' 
                                        : 'bg-white border-slate-100 hover:border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800'">
                                <span class="w-1.5 h-1.5 rounded-full block shrink-0" 
                                      :class="activeTag === genre ? 'bg-indigo-200' : 'bg-indigo-400'"></span>
                                <span x-text="genre"></span>
                            </button>
                        </template>

                        <!-- Expander Button: styled matching regular tags for inline consistency -->
                        <button type="button"
                                @click="showAllGenres = !showAllGenres"
                                class="px-4 py-2.5 rounded-full text-sm font-semibold transition-all duration-200 shadow-sm border border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50 text-slate-600 hover:text-slate-800 focus:outline-none inline-flex items-center justify-center gap-1.5 active:scale-95">
                            <span x-text="showAllGenres ? 'Less genres' : 'More genres'"></span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="showAllGenres && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Search results overlay --}}
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
                                class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 border-b border-slate-50 last:border-0 active:scale-[0.98] transition-all duration-75 group"
                                :class="isSelected(track.id) && 'bg-indigo-50/60'">
                                <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                     class="w-10 h-10 rounded-lg object-cover flex-shrink-0 shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-base font-semibold text-slate-800 truncate" x-text="track.name"></div>
                                    <div class="text-sm text-slate-500 truncate" x-text="getArtistName(track)"></div>
                                </div>
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                     :class="isSelected(track.id) ? 'bg-indigo-600 border-indigo-600 scale-110' : 'border-slate-200 group-hover:border-indigo-300 group-hover:scale-105'">
                                    <svg x-show="isSelected(track.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Unified card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                {{-- ── SECTION 1: Suggestions ── --}}
                <div class="px-6 pt-5 pb-1">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[13px] font-bold text-slate-400 uppercase tracking-widest"
                           x-text="activeTag ? 'Trending in ' + activeTag + ' — tap to add' : 'A bit of everything — tap to add'">
                        </p>
                        <!-- Loading spinner for genre fetch -->
                        <div x-show="isLoadingGenre" x-cloak class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <span class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wider">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Suggestions list -->
                    <ul class="divide-y divide-slate-50" x-show="!isLoadingGenre">
                        <template x-for="track in displayedSuggestions" :key="track.id">
                            <li @click="toggleTrack(track)"
                                class="flex items-center gap-3 py-3.5 cursor-pointer hover:bg-slate-50/80 active:scale-[0.98] transition-all duration-75 -mx-6 px-6 group"
                                :class="isSelected(track.id) && 'bg-indigo-50/50'">
                                <img :src="track.album?.images[0]?.url || '/images/default-album.png'"
                                     :alt="track.name"
                                     class="w-10 h-10 rounded-xl object-cover flex-shrink-0 shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="text-base font-semibold text-slate-800 truncate" x-text="track.name"></div>
                                    <div class="text-sm font-normal text-slate-500 truncate mt-0.5" x-text="getArtistName(track)"></div>
                                </div>
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                     :class="isSelected(track.id)
                                         ? 'bg-indigo-600 border-indigo-600 scale-110'
                                         : 'border-slate-200 group-hover:border-indigo-300 group-hover:scale-105'">
                                    <svg x-show="isSelected(track.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </li>
                        </template>
                        <li x-show="displayedSuggestions.length === 0" class="py-5 text-xs text-slate-400 text-center">
                            No tracks available right now.
                        </li>
                    </ul>
                    <!-- Loading Skeletons -->
                    <div class="space-y-3 py-3" x-show="isLoadingGenre" x-cloak>
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
                <div class="mx-6 my-3 border-t border-slate-100"></div>

                {{-- ── SECTION 2: Shelf ── --}}
                <div class="px-6 pb-6">
                    <div class="flex items-center mb-4 text-sm sm:text-base font-semibold text-slate-800">
                        <span x-text="selectedTracks.length >= 5 ? 'Taste Profile: Ready' : 'Taste Profile: Building'"></span>
                        <span class="mx-1.5 text-slate-300 font-normal">·</span>
                        <span :class="selectedTracks.length >= 5 ? 'text-emerald-600' : 'text-slate-600'"
                              x-text="selectedTracks.length >= 5 ? selectedTracks.length + '/10' : selectedTracks.length + '/5'"></span>
                        <span class="mx-1.5 text-slate-300 font-normal" x-show="selectedTracks.length < 5">·</span>
                        <span class="text-sm font-medium text-slate-500"
                              x-show="selectedTracks.length < 5"
                              x-text="'pick ' + (5 - selectedTracks.length) + ' more to unlock'"></span>
                        <span class="mx-1.5 text-slate-300 font-normal" x-show="selectedTracks.length >= 5">·</span>
                        <span class="text-sm font-bold text-emerald-600" x-show="selectedTracks.length >= 5">ready to continue</span>
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

                        {{-- Ghost badge --}}
                        <div x-show="selectedTracks.length < 10"
                             class="flex flex-col items-center justify-center w-12 h-12 rounded-xl flex-shrink-0 border-2 border-dashed transition-all duration-300"
                             :class="selectedTracks.length === 0
                                 ? 'pulse-glow border-indigo-400 bg-indigo-50 shadow-md shadow-indigo-100/30'
                                 : (selectedTracks.length >= 5 ? 'border-emerald-200 bg-emerald-50' : 'border-indigo-200 bg-indigo-50')">
                            <span class="text-sm font-black leading-none"
                                  :class="selectedTracks.length >= 5 ? 'text-emerald-400' : 'text-indigo-400'"
                                  x-text="selectedTracks.length < 5 ? '+' + (5 - selectedTracks.length) : '+'">
                            </span>
                        </div>

                    </div>

                    {{-- First-time shelf hint --}}
                    <div x-show="showShelfTip && selectedTracks.length === 0"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-4 relative bg-indigo-50/95 border border-indigo-100/50"
                         style="padding: 12px; display: flex; align-items: start; gap: 10px; border-radius: 12px; position: relative; border: 1px solid rgba(224, 231, 255, 0.5);">
                        <div class="absolute bg-indigo-50 border-t border-l border-indigo-100/50" 
                             style="width: 10px; height: 10px; top: -6px; left: 24px; transform: rotate(45deg); border-top: 1px solid rgba(224, 231, 255, 0.5); border-left: 1px solid rgba(224, 231, 255, 0.5);"></div>
                        <svg class="text-indigo-500 mt-0.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div style="flex: 1 1 0%; min-width: 0;">
                            <p class="font-bold text-indigo-800" style="margin: 0 0 2px 0; font-size: 14px;">Quick Tip</p>
                            <p class="text-indigo-600/90" style="margin: 0; font-size: 13px; line-height: 1.4;">Your picks will appear here. Tap any trending track, search, or click on a vibe above to find your favorites!</p>
                        </div>
                        <button @click="dismissShelfTip()"
                                type="button"
                                class="text-indigo-400 hover:text-indigo-600 transition-colors" 
                                style="font-size: 16px; line-height: 1; flex-shrink: 0; margin-top: -4px; margin-right: -4px; padding: 4px; cursor: pointer; border: none; background: transparent;">
                            &times;
                        </button>
                    </div>

                </div>
            </div>{{-- end unified card --}}

            <div class="pt-8">
                <button @click="submitShelf"
                        :disabled="selectedTracks.length < 5 || isSubmitting"
                        type="button"
                        class="relative w-full py-5 rounded-2xl font-bold text-base sm:text-lg tracking-wide overflow-hidden transition-all duration-500 shadow-sm border"
                        :class="selectedTracks.length >= 5
                            ? 'shadow-lg shadow-indigo-300/40 hover:-translate-y-0.5 hover:shadow-xl cursor-pointer bg-slate-200 border-transparent animate-pulse-glow'
                            : (selectedTracks.length > 0
                                ? 'bg-indigo-50/80 border-indigo-100/50 cursor-not-allowed'
                                : 'bg-slate-100 border-slate-100 cursor-not-allowed')">

                    {{-- Indigo layer fills as tracks accumulate --}}
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[inherit] transition-opacity duration-500"
                          :style="'opacity:' + (selectedTracks.length >= 5 ? 1 : 0)">
                    </span>

                    <span class="relative z-10 flex items-center justify-center gap-2 transition-colors duration-300"
                          :class="selectedTracks.length >= 5 ? 'text-white' : (selectedTracks.length > 0 ? 'text-indigo-600/70' : 'text-slate-500')">
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
                        <svg x-show="!isSubmitting && selectedTracks.length >= 5" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                {{-- Actionable guidance helper text directly below --}}
                <p class="mt-3 text-center text-sm font-semibold transition-colors duration-300"
                   :class="selectedTracks.length >= 5 ? 'text-emerald-600' : 'text-slate-500'"
                   x-text="selectedTracks.length >= 5 
                       ? 'Ready to unlock your personalized feed!' 
                       : (5 - selectedTracks.length) + ' more song' + (5 - selectedTracks.length === 1 ? '' : 's') + ' to unlock your personalized feed'">
                </p>
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
                    return this.activeTag ? this.genreTracks : this.defaultSuggestedTracks;
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
