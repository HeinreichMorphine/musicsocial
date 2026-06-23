@props(['song'])
@props(['song'])
@php
    $reason = $song->reason ?? 'Based on your taste';
    $genres = [];
    $isArtistMatch = false;
    $artistMatchName = '';
    $isSharedByFriend = false;
    
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
            <!-- Match Score overlay -->
            <div class="absolute top-2 right-2 z-40 bg-white/80 dark:bg-black/80 backdrop-blur-sm text-gray-900 dark:text-white text-[10px] font-bold px-2 py-1 rounded-full border border-black/5 dark:border-white/10 flex items-center shadow-md">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                {{ $matchScore }}%
            </div>

            <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500 scale-110" style="background-image: url('{{ $song->album_art_url }}');"></div>
            <img class="relative z-10 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $song->album_art_url }}" alt="Album Art">
            <div class="absolute inset-0 z-20 bg-black/20 group-hover:bg-black/0 transition-colors duration-300"></div>
        </div>
        
        <!-- Hover Overlay with Dual Buttons -->
        <div class="absolute inset-0 z-30 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <!-- Spotify Button -->
            @if(auth()->user()->spotify_token)
            <a href="{{ $song->spotify_url }}" target="_blank" class="transform hover:scale-110 transition-transform duration-200" title="Listen on Spotify">
                 <svg class="w-12 h-12 drop-shadow-lg" fill="#1DB954" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141 4.32-1.38 9.841-.719 13.44 1.5.42.3.6.84.3 1.32zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
            </a>
            @endif
            
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

        @if(isset($song->reason))
            <!-- Reasoning Zone -->
            <div class="mt-1 relative flex-1 flex flex-col">
                <!-- 2. Humanized Reason Text -->
                <div class="text-[12px] text-gray-500 dark:text-gray-400 font-medium leading-snug mb-2 flex-1 line-clamp-2">
                    {{ $song->reason }}
                </div>
            </div>
        @endif

        <!-- Interaction Zone (Fixed height bottom footprint) -->
        <div class="mt-auto pt-4 relative min-h-[44px]">
            <div x-show="!listened" class="flex justify-start absolute inset-0 pt-4" style="z-index: 10;">
                <button @click="markInteraction('listen')" class="group/btn flex items-center space-x-1 px-3 py-1.5 h-[34px] rounded-lg text-gray-400 dark:text-gray-500 hover:text-custom-mid-blue dark:hover:text-blue-400 hover:bg-custom-mid-blue/10 dark:hover:bg-blue-400/10 transition-colors border border-transparent hover:border-custom-mid-blue/20 dark:hover:border-blue-400/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover/btn:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                    <span class="text-[11px] font-bold uppercase tracking-wider">Listened</span>
                </button>
            </div>
            
            <div x-show="listened" style="display: none; z-index: 10;" class="flex justify-between items-center absolute inset-0 pt-4  rounded-lg">
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