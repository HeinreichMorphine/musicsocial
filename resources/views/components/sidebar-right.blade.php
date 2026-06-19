<div x-data="{}" class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-5 border border-white/40 dark:border-white/10 shadow-xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-200/50 dark:border-gray-700/50 pb-2">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Suggested for you</h3>
        <a href="{{ route('discovery') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline transition-all">See all</a>
    </div>
    @if (isset($recommendedSongs) && !$recommendedSongs->isEmpty())
        @foreach ($recommendedSongs->take(5) as $song)
            <div class="space-y-3">
                <div class="flex items-center space-x-3 xl:space-x-4 group hover:bg-white/40 dark:hover:bg-gray-800 p-2 rounded-xl transition-all duration-300 relative cursor-pointer"
                     @click="window.location.href='{{ route('discovery') }}'">
                    
                    <div class="relative w-12 h-12 xl:w-16 xl:h-16 flex-shrink-0 overflow-hidden rounded-lg shadow-md"
                         @click.stop="window.toggleSpotifyPreview('sid-{{ $song->id }}', '{{ $song->spotify_track_id }}')">
                         <img class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" 
                              src="{{ $song->album_art_url }}" 
                              onerror="this.src='{{ asset('icons/reso.png') }}'; this.onerror=null;" 
                              alt="Album Art">
                         
                         {{-- Play Overlay --}}
                         <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                             <svg id="spe-icon-play-sid-{{ $song->id }}" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                 <path d="M8 5v14l11-7z"/>
                             </svg>
                             <svg id="spe-icon-stop-sid-{{ $song->id }}" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24" style="display:none;">
                                 <path d="M6 6h12v12H6z"/>
                             </svg>
                         </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('discovery') }}" @click.stop class="text-base font-bold text-gray-900 dark:text-white leading-tight hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-1 truncate block">
                            {{ $song->track_name }}
                        </a>
                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate mb-1.5">{{ $song->artist_name }}</p>
                        
                        @if(isset($song->reason))
                            <div class="inline-flex items-center text-[10px] font-bold text-white px-2 py-0.5 rounded-full shadow-sm {{ 
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

                    {{-- External Link Icon (Absolute top right) --}}
                    <a href="{{ $song->spotify_url }}" target="_blank" @click.stop class="absolute top-2 right-2 p-1 bg-white/80 dark:bg-black/80 rounded-md opacity-0 group-hover:opacity-100 transition-opacity hover:scale-110" title="Open in Spotify">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm5.503 17.297c-.215.352-.676.463-1.025.249-2.821-1.722-6.372-2.112-10.555-1.157-.402.092-.803-.16-.895-.562-.092-.403.159-.803.562-.895 4.582-1.048 8.513-.603 11.664 1.323.35.215.461.676.249 1.042zm1.468-3.262c-.272.441-.849.581-1.289.31-3.23-1.986-8.153-2.564-11.973-1.405-.497.151-1.023-.131-1.173-.627-.15-.497.13-.1023.627-1.173 4.364-1.323 9.779-.675 13.51 1.616.44.271.58.848.309 1.289zm.127-3.41c-3.874-2.3-10.273-2.513-14.001-1.381-.594.18-1.223-.153-1.403-.747-.18-.594.153-1.223.747-1.403 4.288-1.303 11.352-1.054 15.82 1.597.534.317.708 1.005.391 1.539-.317.534-1.005.708-1.539.391z"/>
                        </svg>
                    </a>
                </div>

                {{-- Spotify Preview Container --}}
                <div id="spe-container-sid-{{ $song->id }}" 
                     class="px-2"
                     style="display:none;"
                     x-on:click.stop>
                    <iframe id="spe-frame-sid-{{ $song->id }}"
                        src=""
                        width="100%" height="80"
                        frameborder="0"
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy"
                        style="border-radius:12px; display:block;">
                    </iframe>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    @endif
</div>
