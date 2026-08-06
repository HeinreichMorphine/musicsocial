@props(['song'])
@php
    $reason = $song->reason ?? 'Based on your taste';
    $genres = [];
    $isArtistMatch = false;
    $artistMatchName = '';
    $isSharedByFriend = false;

    // Determine algorithm signal chip
    $chipLabel = $song->chip_label ?? 'Listeners Like You';

    if ($chipLabel === 'Artist Deep Cut') {
        $chipColor = 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700/40';
        $barColor  = 'from-amber-400 to-amber-600';
        $dotColor  = 'bg-amber-500';
    } elseif ($chipLabel === 'Sound Profile') {
        $chipColor = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700/40';
        $barColor  = 'from-blue-400 to-indigo-500';
        $dotColor  = 'bg-blue-500';
    } elseif ($chipLabel === 'Social Pick') {
        $chipColor = 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700/40';
        $barColor  = 'from-purple-400 to-violet-500';
        $dotColor  = 'bg-purple-500';
    } elseif ($chipLabel === 'Genre Affinity') {
        $chipColor = 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-700/40';
        $barColor  = 'from-teal-400 to-emerald-500';
        $dotColor  = 'bg-teal-500';
    } elseif ($chipLabel === 'Community Pick') {
        $chipColor = 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-700/40';
        $barColor  = 'from-orange-400 to-rose-500';
        $dotColor  = 'bg-orange-500';
    } elseif ($chipLabel === 'Listeners Like You') {
        $chipColor = 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/40';
        $barColor  = 'from-indigo-400 to-blue-500';
        $dotColor  = 'bg-indigo-500';
    } elseif ($chipLabel === 'Taste Match') {
        $chipColor = 'bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-700 dark:text-fuchsia-300 border border-fuchsia-200 dark:border-fuchsia-700/40';
        $barColor  = 'from-fuchsia-400 to-pink-500';
        $dotColor  = 'bg-fuchsia-500';
    } else {
        $chipColor = 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-700/40';
        $barColor  = 'from-violet-400 to-indigo-500';
        $dotColor  = 'bg-violet-500';
    }
    
    // Parse Reason
    if (str_contains(strtolower($reason), 'matches your taste in')) {
        preg_match('/matches your taste in (.*?)(?: •|$)/i', $reason, $matches);
        if (isset($matches[1])) {
            $rawGenres = explode(', ', $matches[1]);
            foreach($rawGenres as $g) {
                // Remove any text after dashes, middle dots, or the word "shared"
                $cleanGenre = explode(' - ', $g)[0];
                $cleanGenre = explode(' · ', $cleanGenre)[0];
                if (stripos($cleanGenre, 'shared') !== false) {
                    $cleanGenre = substr($cleanGenre, 0, stripos($cleanGenre, 'shared'));
                }
                // Strip trailing non-word chars just in case
                $genres[] = trim($cleanGenre, " \t\n\r\0\x0B-·•");
            }
        }
    }
    
    if (str_contains(strtolower($reason), "you've enjoyed")) {
        $isArtistMatch = true;
        preg_match("/you've enjoyed (.*?) before/i", $reason, $matches);
        if (isset($matches[1])) {
            $artistMatchName = $matches[1];
        }
    }
    
    if (str_contains(strtolower($reason), 'shared by a friend')) {
        $isSharedByFriend = true;
    }
    
    // Fetch friends who shared this (Face-piling)
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
        // Map scores (typically 0.05 to ~6.0+) using an elegant exponential formula to fit 60% to 99% range
        $matchScore = (int) round(60 + 39 * (1 - exp(-0.55 * $song->score)));
    } else {
        // Fallback seeded match score if score is missing
        srand($song->id);
        $matchScore = rand(88, 99);
        srand();
    }
@endphp
<div x-data="{ 
    listened: false,
    interacted: false, 
    markInteraction(type) {
        this.listened = true; // Ensure listened is true if they click like/dislike directly
        if (type === 'listen') {
        
            return;
        }
        
        // For Like/Dislike, we call API and remove
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
            this.interacted = true; // This will hide the card
            this.$dispatch('song-interacted');
        });
    }
}" 
x-show="!interacted"
x-transition:leave="transition ease-in duration-300"
x-transition:leave-start="opacity-100 scale-100"
x-transition:leave-end="opacity-0 scale-90"
class="group flex flex-col h-full relative overflow-hidden rounded-3xl bg-white/40 dark:bg-gray-900 backdrop-blur-md border border-white/10 dark:border-gray-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
    
    <div class="relative w-full aspect-square bg-white/20 dark:bg-white/5 backdrop-blur-sm rounded-t-3xl shadow-xl overflow-hidden flex shrink-0">
        
        <!-- Album Art Container -->
        <div class="relative flex-1 h-full overflow-hidden group-hover:shadow-inner">

            <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500 scale-110" style="background-image: url('{{ $song->album_art_url }}');"></div>
            <img class="relative z-10 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $song->album_art_url }}" alt="Album Art">
            <div class="absolute inset-0 z-20 bg-black/20 group-hover:bg-black/0 transition-colors duration-300"></div>
        </div>
        
        <!-- Hover Overlay with Dual Buttons -->
        <div class="absolute inset-0 z-30 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            @php
                $isLinked = auth()->check() && auth()->user()->spotify_token;
                $isPremium = auth()->check() && auth()->user()->isSpotifyPremium();
            @endphp
            <!-- Spotify Button -->
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
            
            <!-- YouTube Button (Translucent Default) -->
            <a href="{{ $song->youtube_url ?? 'https://www.youtube.com/results?search_query=' . urlencode($song->track_name . ' ' . $song->artist_name) }}" target="_blank" class="transform hover:scale-110 transition-transform duration-200" title="Watch on YouTube">
                 <svg class="w-12 h-12 drop-shadow-lg" fill="#FF0000" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
        </div>
    </div>
    <div class="p-4 relative z-10 flex flex-col flex-1 min-h-[160px]">
        
        <div class="mb-2">
            <h3 class="font-bold text-[0.95rem] text-gray-900 dark:text-white line-clamp-2 whitespace-normal group-hover:text-custom-mid-blue dark:group-hover:text-blue-400 transition-colors leading-tight mb-0.5" title="{{ $song->track_name }}">{{ $song->track_name }}</h3>
            <p class="text-[13px] font-medium text-gray-400 dark:text-gray-500 truncate">{{ $song->artist_name }}</p>
        </div>

        @php
            // Detect social proof patterns in the reason for Listeners Like You
            $isCollabFiltering = ($chipLabel === 'Listeners Like You');
            $likedByUser = null;
            $sharedByUser = null;
            $similarTasteUser = null;

            if ($isCollabFiltering) {
                if (preg_match('/Liked by ([^,]+), a user you follow/i', $reason, $m)) {
                    $likedByUser = trim($m[1]);
                } elseif (preg_match('/Shared by ([^,]+), a listener with similar taste/i', $reason, $m)) {
                    $sharedByUser = trim($m[1]);
                } elseif (preg_match('/(?:similar taste to|Liked by users with similar taste to) ([^"]+)/i', $reason, $m)) {
                    $similarTasteUser = trim($m[1]);
                }
            }
        @endphp

        @if(isset($song->reason))
            <!-- Reasoning Zone -->
            <div class="mt-2 relative flex-1 flex flex-col gap-2">

                <!-- Algorithm Signal Chip -->
                <div class="inline-flex items-center gap-1.5 w-fit">
                    <span class="text-[11px] font-bold tracking-wide px-2 py-0.5 rounded-full {{ $chipColor }}">
                        {{ $chipLabel }}
                    </span>
                </div>

                @if($isCollabFiltering && ($likedByUser || $sharedByUser || $similarTasteUser))
                    <!-- Social Proof Collaborative Filtering Reason -->
                    <div class="flex items-start gap-2 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/40 rounded-xl px-2.5 py-2">
                        <div class="shrink-0 w-6 h-6 rounded-full bg-indigo-200 dark:bg-indigo-800 flex items-center justify-center mt-0.5">
                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <p class="text-[11px] text-indigo-700 dark:text-indigo-300 leading-snug font-medium">
                            @if($likedByUser)
                                Liked by <span class="font-bold">{{ $likedByUser }}</span>, a user you follow
                            @elseif($sharedByUser)
                                Shared by <span class="font-bold">{{ $sharedByUser }}</span>, a listener with similar taste
                            @elseif($similarTasteUser)
                                Liked by listeners with similar taste to <span class="font-bold">{{ $similarTasteUser }}</span>
                            @endif
                        </p>
                    </div>
                @else
                    <!-- Standard Reason Sub-text -->
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 leading-snug line-clamp-2">
                        {{ $song->reason }}
                    </p>
                @endif

                <!-- Match Score Bar -->
                <div class="flex items-center gap-2 mt-0.5">
                    <div class="flex-1 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full bg-gradient-to-r {{ $barColor }} transition-all duration-700 ease-out"
                            style="width: {{ $matchScore }}%; animation: scoreReveal 0.9s ease-out forwards;"
                            x-data
                            x-init="$el.style.width = '0%'; setTimeout(() => $el.style.width = '{{ $matchScore }}%', 120)"
                        ></div>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 tabular-nums w-8 text-right">{{ $matchScore }}%</span>
                </div>

            </div>
        @endif

        <!-- Interaction Zone (Fixed height bottom footprint) -->
        <div class="mt-auto pt-4 relative min-h-[44px]">
            <div class="flex justify-between items-center absolute inset-0 pt-4 rounded-lg">
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