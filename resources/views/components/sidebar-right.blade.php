<div class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-6 border border-white/40 dark:border-white/10 shadow-xl space-y-4">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200/50 dark:border-gray-700/50 pb-2">Suggested for you</h3>
    @if (isset($recommendedSongs) && !$recommendedSongs->isEmpty())
        @foreach ($recommendedSongs->take(5) as $song)
            <a href="{{ $song->spotify_url }}" target="_blank" class="flex items-center space-x-4 group hover:bg-white/40 dark:hover:bg-gray-800 p-2 rounded-xl transition-all duration-300">
            <div class="relative w-16 h-16 flex-shrink-0 overflow-hidden rounded-lg shadow-md">
                 <img class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" src="{{ $song->album_art_url }}" alt="Album Art">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-gray-900 dark:text-white leading-tight group-hover:text-custom-mid-blue dark:group-hover:text-blue-400 transition-colors mb-1">{{ $song->track_name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 truncate mb-1.5">{{ $song->artist_name }}</p>
                @if(isset($song->reason))
                    <div class="inline-flex items-center text-xs font-bold text-white px-2.5 py-1 rounded-full shadow-sm {{ 
                        Str::contains($song->reason, 'friend') ? 'bg-purple-500' : 
                        (Str::contains($song->reason, ['favorite artist', 'Same artist']) ? 'bg-yellow-500' : 
                        (Str::contains($song->reason, 'listening history') ? 'bg-blue-500' : 
                        (Str::contains($song->reason, 'Popular') ? 'bg-orange-500' : 'bg-indigo-500'))) 
                    }}">
                        @if(Str::contains($song->reason, 'friend'))
                            <span class="mr-1">👥</span> Friends
                        @elseif(Str::contains($song->reason, ['favorite artist', 'Same artist']))
                            <span class="mr-1">⭐</span> Artist
                        @elseif(Str::contains($song->reason, 'listening history'))
                            <span class="mr-1">🎧</span> History
                        @elseif(Str::contains($song->reason, 'Popular'))
                            <span class="mr-1">🔥</span> Trending
                        @elseif(Str::contains($song->reason, 'Similar genres'))
                            <span class="mr-1">🎹</span> Genre
                        @else
                            <span class="mr-1">🎵</span> Taste
                        @endif
                    </div>
                @endif
            </div>
        </a>
        @endforeach
    @else
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    @endif
</div>
