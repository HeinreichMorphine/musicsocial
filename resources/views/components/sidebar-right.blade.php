<div x-data="{}" class="bg-white/60 dark:bg-black backdrop-blur-lg rounded-3xl p-5 border border-white/40 dark:border-white/10 shadow-xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-200/50 dark:border-gray-700/50 pb-2">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Suggested for you</h3>
        <a href="{{ route('discovery') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline transition-all">See all</a>
    </div>

    @if (isset($recommendedSongs) && !$recommendedSongs->isEmpty())
        @foreach ($recommendedSongs->take(5) as $song)
            @php
                $ytUrl = $song->youtube_url
                    ?: 'https://www.youtube.com/results?search_query=' . urlencode($song->track_name . ' ' . $song->artist_name);
            @endphp
            <div class="space-y-1">
                {{-- Clicking opens the platform chooser modal — no inline playback --}}
                <div class="flex items-center space-x-3 xl:space-x-4 group hover:bg-white/40 dark:hover:bg-gray-800 p-2 rounded-xl transition-all duration-300 relative cursor-pointer"
                     @click.prevent="$dispatch('open-playback-chooser', {
                         spotifyUrl: '{{ addslashes($song->spotify_url) }}',
                         youtubeUrl: '{{ addslashes($ytUrl) }}',
                         trackName:  '{{ addslashes($song->track_name) }}',
                         artistName: '{{ addslashes($song->artist_name) }}'
                     })">

                    {{-- Album art thumbnail --}}
                    <div class="relative w-12 h-12 xl:w-16 xl:h-16 flex-shrink-0 overflow-hidden rounded-lg shadow-md">
                        <img class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                             src="{{ $song->album_art_url }}"
                             onerror="this.src='{{ asset('icons/reso.png') }}'; this.onerror=null;"
                             alt="Album Art">

                        {{-- Visual play cue only --}}
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Track info --}}
                    <div class="flex-1 min-w-0">
                        <span class="text-base font-bold text-gray-900 dark:text-white leading-tight truncate block">
                            {{ $song->track_name }}
                        </span>
                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate mb-1">{{ $song->artist_name }}</p>

                        @if(isset($song->reason))
                            @php
                                $reasonLower = strtolower($song->reason);
                                $badgeBg = 'bg-indigo-600 dark:bg-indigo-500';
                                $badgeLabel = 'Taste';

                                if (Str::contains($reasonLower, ['friend', 'circle', 'network'])) {
                                    $badgeBg = 'bg-purple-600 dark:bg-purple-500';
                                    $badgeLabel = 'Friends';
                                } elseif (Str::contains($reasonLower, ['same artist', 'artist', 'fans', 'cut from'])) {
                                    $badgeBg = 'bg-amber-600 dark:bg-amber-500';
                                    $badgeLabel = 'Artist';
                                } elseif (Str::contains($reasonLower, ['similar genres', 'vibe'])) {
                                    $badgeBg = 'bg-teal-600 dark:bg-teal-500';
                                    $badgeLabel = 'Genre';
                                } elseif (Str::contains($reasonLower, ['popular', 'trending', 'community'])) {
                                    $badgeBg = 'bg-orange-600 dark:bg-orange-500';
                                    $badgeLabel = 'Trending';
                                } elseif (Str::contains($reasonLower, ['sound profile', 'taste', 'history', 'discovered', 'vibe match'])) {
                                    $badgeBg = 'bg-blue-600 dark:bg-blue-500';
                                    $badgeLabel = 'Taste';
                                }
                            @endphp
                            <div class="inline-flex items-center text-[10px] font-bold text-white px-2 py-0.5 rounded-full shadow-sm {{ $badgeBg }}">
                                {{ $badgeLabel }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    @endif
</div>
