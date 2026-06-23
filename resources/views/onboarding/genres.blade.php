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
        // Forced Light Mode for Onboarding
        document.documentElement.classList.remove('dark');
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(156, 163, 175, 0.1); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(79, 70, 229, 0.4); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(79, 70, 229, 0.8); }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes popIn {
            0%   { opacity: 0; transform: scale(0.8); }
            70%  { transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-scale-in  { animation: scaleIn 0.2s ease-out forwards; }
        .animate-pop-in    { animation: popIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards; }
        .animate-fade-up   { animation: fadeUp 0.4s ease-out forwards; }
        .animate-slide-in  { animation: slideInRight 0.3s ease-out forwards; }
        [x-cloak] { display: none !important; }

        /* Progressive CTA glow */
        .cta-ready { box-shadow: 0 20px 50px rgba(79,70,229,0.4), 0 0 0 0 rgba(99,102,241,0); }
        .cta-ready:hover { box-shadow: 0 24px 60px rgba(79,70,229,0.55), 0 0 0 6px rgba(99,102,241,0.12); }

        /* Sticky benefit strip */
        .benefit-strip {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border-bottom: 1px solid rgba(99,102,241,0.15);
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased selection:bg-indigo-100">

    {{-- Sticky Benefit Strip --}}
    <div class="benefit-strip sticky top-0 z-50 py-2.5 px-4 text-center text-sm font-medium text-indigo-700">
        The 5–10 songs you pick here train your taste profile — better matches, better recommendations, better people to follow.
    </div>

    <div class="min-h-screen flex flex-col items-center pt-8 pb-24 px-4 sm:px-6 lg:px-8 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/40 via-white to-slate-50"
         x-data="onboardingApp()">

        <div class="w-full max-w-5xl space-y-10 animate-fade-up">

            {{-- Header --}}
            <div class="text-center">
                <div class="inline-block p-4 rounded-full bg-indigo-50 mb-4 border border-indigo-100">
                    <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 15v.878m13.5-3c-.235-.083-.487-.128-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 18v.878m-16.5-3c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 21v-1.122" />
                    </svg>
                </div>
                <h2 class="text-5xl font-black tracking-tight text-slate-900 sm:text-6xl mb-4">
                    Curate Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Song Shelf</span>
                </h2>
                <p class="mt-2 text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                    Pick your all-time favorites below — the more specific, the smarter your feed.
                </p>
            </div>

            {{-- Search Area --}}
            <div class="relative max-w-2xl mx-auto z-20">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-slate-300 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.400ms="performSearch"
                           :disabled="isSubmitting"
                           x-ref="searchInput"
                           x-init="$nextTick(() => $refs.searchInput.focus())"
                           class="block w-full pl-12 pr-4 py-5 border-0 rounded-2xl leading-5 bg-white text-slate-900 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none sm:text-lg shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] transition-all disabled:opacity-50"
                           placeholder="Search for an artist, album, or track...">
                </div>

                {{-- Searching spinner --}}
                <div x-show="isSearching" x-cloak class="mt-4 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Search Results --}}
                <div x-show="searchResults.length > 0 && searchQuery.length > 2"
                     x-transition
                     class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="track in searchResults" :key="track.id">
                        <div @click="toggleTrack(track)"
                             class="flex items-center p-4 rounded-2xl cursor-pointer transition-all duration-300 border shadow-sm hover:shadow-xl hover:-translate-y-0.5"
                             :class="isSelected(track.id) ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-500/20' : 'bg-white border-slate-100 hover:border-indigo-100 hover:bg-slate-50'">
                            <img :src="track.album?.images[0]?.url || '/images/default-album.png'" class="w-14 h-14 rounded-lg shadow-sm object-cover flex-shrink-0" alt="Album Art">
                            <div class="ml-4 flex-1 truncate">
                                <h4 class="text-slate-900 font-bold truncate" x-text="track.name"></h4>
                                <p class="text-sm text-slate-500 truncate" x-text="track.artists?.map(a => a.name).join(', ')"></p>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <svg x-show="isSelected(track.id)" class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <svg x-show="!isSelected(track.id)" class="w-7 h-7 text-slate-200 hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Suggested Starter Tracks (server-side, shown when shelf is nearly empty) --}}
            @if(!empty($suggestedTracks))
                <div x-show="selectedTracks.length < 3 && searchQuery.length === 0"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="max-w-2xl mx-auto">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-3">Trending right now — tap to add instantly</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($suggestedTracks as $st)
                            @php
                                $stId    = $st['id'] ?? '';
                                $stName  = $st['name'] ?? 'Unknown';
                                $stArt   = $st['album']['images'][0]['url'] ?? '';
                                $stArtist = collect($st['artists'] ?? [])->pluck('name')->join(', ');
                            @endphp
                            @if($stId)
                                <button type="button"
                                        x-data='{
                                            track: {
                                                id:     "{{ addslashes($stId) }}",
                                                name:   "{{ addslashes($stName) }}",
                                                artists: [{ name: "{{ addslashes($stArtist) }}" }],
                                                album:  { images: [{ url: "{{ addslashes($stArt) }}" }] }
                                            }
                                        }'
                                        @click="toggleTrack(track)"
                                        :class="isSelected('{{ $stId }}') ? 'ring-2 ring-indigo-500 border-indigo-200 bg-indigo-50 scale-95' : 'border-slate-100 bg-white hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5'"
                                        class="relative flex items-center gap-3 p-3 rounded-2xl border transition-all duration-300 text-left group shadow-sm">
                                    <img src="{{ $stArt }}" alt="{{ $stName }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 shadow-sm">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $stName }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $stArtist }}</p>
                                    </div>
                                    <span x-show="isSelected('{{ $stId }}')" class="flex-shrink-0 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Your Shelf --}}
            <div class="bg-white/80 rounded-[2.5rem] p-8 md:p-12 border border-white shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] backdrop-blur-2xl relative z-10 overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>

                {{-- Shelf header --}}
                <div class="flex justify-between items-end mb-10 border-b border-slate-100 pb-6 relative z-10">
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 flex items-center gap-3">
                            <span class="p-2 bg-indigo-600 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </span>
                            Your Shelf
                        </h3>
                        {{-- Dynamic status copy --}}
                        <p class="text-base text-indigo-600 font-semibold mt-2"
                           x-show="selectedTracks.length < 5"
                           x-text="'Add ' + (5 - selectedTracks.length) + ' more to unlock your feed'">
                        </p>
                        <p class="text-base text-emerald-600 mt-2 font-medium flex items-center gap-1.5"
                           x-show="selectedTracks.length >= 5">
                            <span class="p-0.5 bg-emerald-100 rounded-full">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </span>
                            Shelf ready — add more for sharper recommendations (optional)
                        </p>
                    </div>
                    <div class="text-2xl font-black font-mono tracking-tighter"
                         :class="selectedTracks.length >= 5 ? 'text-emerald-500' : 'text-indigo-600'">
                        <span x-text="selectedTracks.length" class="text-4xl"></span><span class="text-slate-300 mx-1">/</span>10
                    </div>
                </div>

                {{-- Track grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                    <template x-for="track in selectedTracks" :key="track.id">
                        <div class="relative group rounded-[1.5rem] overflow-hidden shadow-md bg-white border border-slate-100 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_20px_40px_-10px_rgba(79,70,229,0.3)] animate-pop-in">
                            <img :src="track.album?.images[0]?.url || '/images/default-album.png'" class="w-full aspect-square object-cover" alt="Album Art">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <h4 class="text-white font-bold text-sm leading-tight line-clamp-2" x-text="track.name"></h4>
                                <p class="text-xs text-indigo-300 mt-1 truncate" x-text="track.artists?.[0]?.name || 'Unknown Artist'"></p>
                            </div>
                            <button @click="removeTrack(track.id)"
                                    class="absolute top-3 right-3 bg-white/90 hover:bg-red-500 hover:text-white text-red-500 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-all scale-75 group-hover:scale-100 shadow-xl backdrop-blur-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    {{-- Empty placeholder slots --}}
                    <template x-for="i in Math.max(0, 5 - selectedTracks.length)">
                        <div class="rounded-[1.5rem] border-2 border-dashed border-slate-200 aspect-square flex flex-col items-center justify-center bg-slate-50/50 text-slate-400 hover:text-indigo-400 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all duration-300 group">
                            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-100 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-2v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-2"></path>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Taste Preview Card (shows after 5th track) --}}
                <div x-show="selectedTracks.length >= 5"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 transform translate-y-6"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="mt-8 p-5 rounded-2xl bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center flex-shrink-0 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-indigo-900">Based on your shelf, we're already finding your tribe.</p>
                        <p class="text-xs text-indigo-600 mt-0.5">Complete onboarding to see your taste matches and personalized feed.</p>
                    </div>
                    <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>

            {{-- Progressive CTA --}}
            <div class="flex flex-col items-center space-y-4 pb-20">
                <button @click="submitShelf"
                        :disabled="selectedTracks.length < 5 || isSubmitting"
                        class="relative w-full sm:w-auto px-16 py-6 rounded-[2rem] font-black text-xl text-white transition-all duration-500 transform flex items-center justify-center gap-4 active:scale-95 overflow-hidden"
                        :class="selectedTracks.length >= 5 ? 'cta-ready cursor-pointer hover:-translate-y-1' : 'cursor-not-allowed'">

                    {{-- Progressive background fill layer --}}
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-violet-600 transition-opacity duration-500"
                          :style="'opacity:' + Math.min(1, Math.max(0.18, selectedTracks.length / 5))">
                    </span>
                    {{-- Gray base layer visible only when not ready --}}
                    <span class="absolute inset-0 bg-slate-200 transition-opacity duration-500"
                          :style="'opacity:' + (selectedTracks.length >= 5 ? 0 : 1 - Math.min(0.82, selectedTracks.length / 5))">
                    </span>

                    {{-- Label --}}
                    <span class="relative z-10 flex items-center gap-4"
                          :class="selectedTracks.length >= 5 ? 'text-white' : 'text-slate-400'">
                        <span x-show="!isSubmitting">Complete Onboarding</span>
                        <svg x-show="!isSubmitting" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        <span x-show="isSubmitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Optimizing your feed...
                        </span>
                    </span>
                </button>
            </div>
        </div>

        {{-- Error Toast --}}
        <div x-show="errorMessage"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="fixed top-16 left-1/2 -translate-x-1/2 z-[100] w-full max-w-sm"
             style="display: none;">
            <div class="bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-red-400">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="errorMessage" class="font-bold"></span>
            </div>
        </div>

        {{-- "Nice pick!" Micro-toast (bottom center) --}}
        <div x-show="nicePickMsg"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100] pointer-events-none"
             style="display: none;">
            <div class="bg-indigo-600 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-sm font-semibold">
                <span class="text-lg">✓</span>
                <span x-text="nicePickMsg"></span>
            </div>
        </div>

    </div>

    <script>
        // Simple genre keyword map for client-side genre hints
        const GENRE_HINTS = {
            'pop': 'pop', 'hip hop': 'hip-hop', 'rap': 'hip-hop', 'r&b': 'R&B',
            'soul': 'soul', 'jazz': 'jazz', 'rock': 'rock', 'indie': 'indie',
            'electronic': 'electronic', 'edm': 'EDM', 'classical': 'classical',
            'country': 'country', 'metal': 'metal', 'punk': 'punk',
            'latin': 'latin', 'afrobeats': 'afrobeats', 'kpop': 'K-pop',
            'reggae': 'reggae', 'blues': 'blues', 'folk': 'folk',
        };

        function guessGenre(track) {
            const haystack = [
                track.name || '',
                (track.artists || []).map(a => a.name).join(' '),
                (track.album?.name || '')
            ].join(' ').toLowerCase();

            for (const [keyword, label] of Object.entries(GENRE_HINTS)) {
                if (haystack.includes(keyword)) return label;
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
                _nicePickTimer: null,

                showError(msg) {
                    this.errorMessage = msg;
                    setTimeout(() => { this.errorMessage = ''; }, 4000);
                },

                showNicePick(track) {
                    const genre = guessGenre(track);
                    this.nicePickMsg = genre
                        ? `Nice pick! That tells us you're into ${genre}.`
                        : `Nice pick! Your shelf is taking shape.`;
                    clearTimeout(this._nicePickTimer);
                    this._nicePickTimer = setTimeout(() => { this.nicePickMsg = ''; }, 2200);
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
                            this.showNicePick(track);
                        } else {
                            this.showError('You can only select up to 10 tracks.');
                        }
                    }
                },

                removeTrack(trackId) {
                    this.selectedTracks = this.selectedTracks.filter(t => t.id !== trackId);
                },

                async submitShelf() {
                    if (this.selectedTracks.length < 5) return;
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
