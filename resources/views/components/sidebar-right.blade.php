<div class="bg-white/60 backdrop-blur-md border border-white/20 rounded-3xl shadow-lg p-6 space-y-4">
    <h3 class="text-xl font-bold text-gray-900 border-b border-gray-200/50 pb-2">Suggested for you</h3>
    @if (isset($recommendedSongs) && !$recommendedSongs->isEmpty())
        @foreach ($recommendedSongs->take(5) as $song)
            <a href="{{ $song->spotify_url }}" target="_blank" class="flex items-center space-x-4 group hover:bg-white/40 p-3 rounded-2xl transition-all duration-300">
            <div class="relative w-16 h-16 flex-shrink-0 overflow-hidden rounded-xl shadow-md">
                 <img class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" src="{{ $song->album_art_url }}" alt="Album Art">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-gray-900 leading-tight group-hover:text-custom-mid-blue transition-colors mb-0.5">{{ $song->track_name }}</p>
                <p class="text-sm text-gray-600 truncate mb-1.5">{{ $song->artist_name }}</p>
                @if(isset($song->reason))
                    <div class="inline-flex items-center text-[10px] font-bold text-white px-2 py-0.5 rounded-full shadow-sm {{ 
                        Str::contains($song->reason, 'friend') ? 'bg-pink-500' : 
                        (Str::contains($song->reason, ['favorite artist', 'Same artist']) ? 'bg-yellow-500' : 
                        (Str::contains($song->reason, 'listening history') ? 'bg-blue-500' : 
                        (Str::contains($song->reason, 'Popular') ? 'bg-orange-500' : 'bg-indigo-500'))) 
                    }}">
                        @if(Str::contains($song->reason, 'friend'))
                            <span class="mr-1">❤️</span> Friends
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
