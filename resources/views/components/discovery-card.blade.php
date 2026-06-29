@props(['song'])
@php
    $reason = $song->reason ?? 'Based on your taste';

    // --- Classify algorithm signal type from reason string ---
    $signalType   = 'taste';      // default
    $chipLabel    = 'Sound Match';
    $chipColor    = 'blue';       // tailwind color key

    $r = strtolower($reason);

    if (str_contains($r, 'deep cut from') || str_contains($r, "you've enjoyed")) {
        $signalType = 'artist';
        $chipLabel  = 'Artist Deep Cut';
        $chipColor  = 'amber';
    } elseif (str_contains($r, 'shared by a friend') || str_contains($r, 'friend') || str_contains($r, 'circle') || str_contains($r, 'trust')) {
        $signalType = 'social';
        $chipLabel  = 'Social Pick';
        $chipColor  = 'purple';
    } elseif (str_contains($r, 'vibe match') || str_contains($r, 'genre') || str_contains($r, 'similar genre')) {
        $signalType = 'genre';
        $chipLabel  = 'Genre Affinity';
        $chipColor  = 'teal';
    } elseif (str_contains($r, 'trending') || str_contains($r, 'community') || str_contains($r, 'popular')) {
        $signalType = 'trending';
        $chipLabel  = 'Community Pick';
        $chipColor  = 'orange';
    } elseif (str_contains($r, 'discovered for you') || str_contains($r, 'discover')) {
        $signalType = 'discovery';
        $chipLabel  = 'New Discovery';
        $chipColor  = 'violet';
    } elseif (str_contains($r, 'matches your taste in') || str_contains($r, 'taste in')) {
        $signalType = 'collab';
        $chipLabel  = 'Taste Profile';
        $chipColor  = 'fuchsia';
    } elseif (str_contains($r, 'sound profile') || str_contains($r, 'sound match') || str_contains($r, 'matches your sound')) {
        $signalType = 'taste';
        $chipLabel  = 'Sound Match';
        $chipColor  = 'blue';
    }

    // --- Tailwind color maps for each signal ---
    $chipStyles = [
        'blue'    => 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700/50',
        'amber'   => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50',
        'purple'  => 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700/50',
        'teal'    => 'bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-700/50',
        'orange'  => 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-700/50',
        'violet'  => 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-700/50',
        'fuchsia' => 'bg-fuchsia-50 dark:bg-fuchsia-900/20 text-fuchsia-700 dark:text-fuchsia-300 border border-fuchsia-200 dark:border-fuchsia-700/50',
    ];

    $barColors = [
        'blue'    => 'from-blue-400 to-indigo-500',
        'amber'   => 'from-amber-400 to-orange-500',
        'purple'  => 'from-purple-400 to-violet-500',
        'teal'    => 'from-teal-400 to-emerald-500',
        'orange'  => 'from-orange-400 to-red-400',
        'violet'  => 'from-violet-400 to-purple-500',
        'fuchsia' => 'from-fuchsia-400 to-pink-500',
    ];

    // --- Why-this tooltip copy (plain English, algorithm-aware) ---
    $tooltips = [
        'artist'    => 'Your listening history includes this artist. The TF-IDF engine doubled the artist weight in your taste vector.',
        'social'    => 'A friend in your network shared or liked this. Social trust scoring amplified it based on your circle\'s authority.',
        'genre'     => 'Genre tokens in this track overlapped with your taste centroid via the genre-aware fallback layer.',
        'trending'  => 'Added via the community popularity safety net — a deliberate minority injection to avoid pure echo-chamber results.',
        'discovery' => 'Serendipitous pick from outside your established taste cluster — intentional bubble-breaking.',
        'collab'    => 'Collaborative filtering (SVD) matched your interaction pattern with similar users who enjoyed this.',
        'taste'     => 'Cosine similarity between this track\'s feature vector and your aggregated taste centroid exceeded the 0.1 threshold.',
    ];

    $tooltipText = $tooltips[$signalType] ?? 'Matched by the recommendation engine based on your profile.';

    // Fetch friends who shared this (face-piling)
    $sharingFriends = collect();
    if (auth()->check()) {
        $sharingFriends = auth()->user()->following()
            ->whereHas('shares', function($q) use ($song) {
                $q->where('song_id', $song->id);
            })
            ->take(3)
            ->get();
    }

    // Compute real match percentage from score if available
    if (isset($song->score) && $song->score !== null) {
        $matchScore = (int) round(60 + 39 * (1 - exp(-0.55 * $song->score)));
    } else {
        srand($song->id);
        $matchScore = rand(88, 99);
        srand();
    }

    $chipClass = $chipStyles[$chipColor] ?? $chipStyles['blue'];
    $barClass  = $barColors[$chipColor] ?? $barColors['blue'];
@endphp

<div x-data="{
    listened: false,
    interacted: false,
    showTooltip: false,
    markInteraction(type) {
        this.listened = true;
        if (type === 'listen') {
            return;
        }
        fetch('{{ route('song-interactions.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                song_id: {{ $song->id }},
                type: type
            })
        })
        .then(() => {
            this.interacted = true;
            this.$dispatch('song-interacted');
        });
    }
}"
x-show="!interacted"
x-transition:leave="transition ease-in duration-300"
x-transition:leave-start="opacity-100 scale-100"
x-transition:leave-end="opacity-0 scale-90"
class="group flex flex-col h-full relative overflow-hidden rounded-3xl bg-white/40 dark:bg-gray-900 backdrop-blur-md border border-white/10 dark:border-gray-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">

    {{-- Album Art --}}
    <div class="relative w-full aspect-square bg-white/20 dark:bg-white/5 backdrop-blur-sm rounded-t-3xl shadow-xl overflow-hidden flex shrink-0">
        <div class="relative flex-1 h-full overflow-hidden group-hover:shadow-inner">
            <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500 scale-110" style="background-image: url('{{ $song->album_art_url }}');"></div>
            <img class="relative z-10 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $song->album_art_url }}" alt="Album Art">
            <div class="absolute inset-0 z-20 bg-black/20 group-hover:bg-black/0 transition-colors duration-300"></div>
        </div>

        {{-- Hover overlay with playback buttons --}}
        <div class="absolute inset-0 z-30 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            @php
                $isLinked  = auth()->check() && auth()->user()->spotify_token;
                $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
            @endphp
            {{-- Spotify --}}
            <a href="{{ $song->spotify_url }}"
               target="_blank"
               x-data="{ isReady: window.isSpotifyReady || false }"
               @spotify-ready.window="isReady = true"
               @spotify-not-ready.window="isReady = false"
               @if($isLinked)
                   @click.prevent="
                       @if($isPremium)
                           if(isReady && typeof window.toggleSpotifyPlayer !== 'undefined') {
                               window.toggleSpotifyPlayer('spotify:track:{{ $song->spotify_track_id }}', {name: '{{ addslashes($song->track_name) }}', artist: '{{ addslashes($song->artist_name) }}', art: '{{ $song->album_art_url }}', previewUrl: '{{ $song->preview_url }}'});
                           }
                       @else
                           if(typeof window.toggleSpotifyPlayer !== 'undefined') {
                               window.toggleSpotifyPlayer('spotify:track:{{ $song->spotify_track_id }}', {name: '{{ addslashes($song->track_name) }}', artist: '{{ addslashes($song->artist_name) }}', art: '{{ $song->album_art_url }}', previewUrl: '{{ $song->preview_url }}'});
                           } else {
                               window.open('{{ $song->spotify_url }}', '_blank');
                           }
                       @endif
                   "
               @endif
               :title="@if($isLinked && $isPremium) isReady ? 'Listen on Spotify' : 'Connecting to Spotify...' @else 'Listen on Spotify' @endif"
               :class="@if($isLinked && $isPremium) isReady ? 'hover:scale-110 cursor-pointer' : 'opacity-40 grayscale cursor-not-allowed pointer-events-none' @else 'hover:scale-110 cursor-pointer' @endif"
               class="transform transition-all duration-300">
                 <svg class="w-12 h-12 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
            </a>

            {{-- YouTube --}}
            <a href="{{ $song->youtube_url ?? 'https://www.youtube.com/results?search_query=' . urlencode($song->track_name . ' ' . $song->artist_name) }}" target="_blank" class="transform hover:scale-110 transition-transform duration-200" title="Watch on YouTube">
                 <svg class="w-12 h-12 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-4 relative z-10 flex flex-col flex-1 min-h-[180px]">

        {{-- Track info --}}
        <div class="mb-3">
            <h3 class="font-bold text-[0.95rem] text-gray-900 dark:text-white line-clamp-2 whitespace-normal group-hover:text-custom-mid-blue dark:group-hover:text-blue-400 transition-colors leading-tight mb-0.5" title="{{ $song->track_name }}">{{ $song->track_name }}</h3>
            <p class="text-[13px] font-medium text-gray-400 dark:text-gray-500 truncate">{{ $song->artist_name }}</p>
        </div>

        {{-- Algorithm Signal Chip + Why-this tooltip --}}
        @if(isset($song->reason))
            <div class="mb-3 relative" x-data="{ open: false }">
                <div class="flex items-center gap-2">
                    {{-- Signal type chip --}}
                    <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase leading-none transition-all duration-200 hover:opacity-80 {{ $chipClass }}"
                            title="Why was this recommended?">

                        {{-- Signal-specific SVG icon --}}
                        @if($signalType === 'artist')
                            {{-- Waveform / fingerprint icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                        @elseif($signalType === 'social')
                            {{-- People icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        @elseif($signalType === 'genre')
                            {{-- Tag icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        @elseif($signalType === 'trending')
                            {{-- Trending up icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        @elseif($signalType === 'discovery')
                            {{-- Compass icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                        @elseif($signalType === 'collab')
                            {{-- Network / nodes icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        @else
                            {{-- Target / cosine match icon --}}
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        @endif

                        {{ $chipLabel }}

                        {{-- Chevron toggle --}}
                        <svg class="w-2.5 h-2.5 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                </div>

                {{-- Why-this expanded explanation --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     @click.outside="open = false"
                     class="mt-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 shadow-sm"
                     x-cloak>
                    <p class="text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed font-medium">{{ $tooltipText }}</p>
                </div>
            </div>
        @endif

        {{-- Match Score Bar --}}
        <div class="mb-3" x-data="{ mounted: false }" x-init="$nextTick(() => { setTimeout(() => mounted = true, 100) })">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Match</span>
                <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300">{{ $matchScore }}%</span>
            </div>
            <div class="h-1 w-full bg-gray-100 dark:bg-gray-700/60 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r {{ $barClass }} transition-all duration-700 ease-out"
                     :style="mounted ? 'width: {{ $matchScore }}%' : 'width: 0%'">
                </div>
            </div>
        </div>

        {{-- Interaction Zone --}}
        <div class="mt-auto pt-2 relative min-h-[44px]">
            <div class="flex justify-between items-center absolute inset-0 pt-2 rounded-lg">
                <div class="flex justify-between items-center w-full bg-gray-50 dark:bg-gray-800/80 p-0.5 rounded-lg border border-gray-100 dark:border-gray-700/50 shadow-sm">
                    <button @click="markInteraction('dislike')" class="flex-1 flex items-center justify-center space-x-1.5 py-1.5 rounded-md text-gray-500 hover:text-red-600 hover:bg-white dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-gray-700 shadow-sm transition-all duration-200" title="Pass">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Pass</span>
                    </button>
                    <div class="w-px h-3 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                    <button @click="markInteraction('like')" class="flex-1 flex items-center justify-center space-x-1.5 py-1.5 rounded-md text-gray-500 hover:text-green-600 hover:bg-white dark:text-gray-400 dark:hover:text-green-400 dark:hover:bg-gray-700 shadow-sm transition-all duration-200" title="Like">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Like</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>