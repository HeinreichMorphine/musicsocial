@props(['song'])
<div class="group block relative overflow-hidden rounded-3xl bg-white/40 backdrop-blur-md border border-white/20 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
    <div class="relative w-full aspect-square overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500 scale-110" style="background-image: url('{{ $song->album_art_url }}');"></div>
        <img class="relative z-10 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="{{ $song->album_art_url }}" alt="Album Art">
        <div class="absolute inset-0 z-20 bg-black/20 group-hover:bg-black/0 transition-colors duration-300"></div>
        
        <!-- Hover Overlay with Dual Buttons -->
        <div class="absolute inset-0 z-30 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <!-- Spotify Button -->
            <a href="{{ $song->spotify_url }}" target="_blank" class="transform hover:scale-110 transition-transform duration-200" title="Listen on Spotify">
                 <img src="{{ asset('icons/spotify_icon.png') }}" alt="Spotify" class="w-12 h-12 drop-shadow-lg">
            </a>
            
            <!-- YouTube Button -->
            <a href="{{ $song->youtube_url ?? 'https://www.youtube.com/results?search_query=' . urlencode($song->track_name . ' ' . $song->artist_name) }}" target="_blank" class="transform hover:scale-110 transition-transform duration-200" title="Watch on YouTube">
                 <img src="{{ asset('icons/youtube_icon.png') }}" alt="YouTube" class="w-12 h-12 drop-shadow-lg rounded-full bg-white">
            </a>
        </div>
    </div>
    <div class="p-4 relative z-10">
        <h3 class="font-bold text-lg text-gray-900 truncate group-hover:text-custom-mid-blue transition-colors">{{ $song->track_name }}</h3>
        <p class="text-sm text-gray-600 truncate">{{ $song->artist_name }}</p>
        @if(isset($song->reason))
            <div class="mt-3 flex items-start text-xs text-indigo-600 bg-indigo-50/50 rounded-lg p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1.017a7.002 7.002 0 016.29 6.291 1 1 0 11-1.98.208A5.002 5.002 0 0010 5V4a1 1 0 01-1-1zm0 16a7.002 7.002 0 01-6.29-6.291 1 1 0 111.98-.208A5.002 5.002 0 0010 15v1a1 1 0 01-1 1zm-5-8a1 1 0 00-1 1v1.017a7.002 7.002 0 006.29 6.291 1 1 0 10.208-1.98A5.002 5.002 0 015 10V9a1 1 0 00-1-1zm11 0a1 1 0 00-1 1v1.017a5.002 5.002 0 01-4.792 4.973 1 1 0 10.208 1.98A7.002 7.002 0 0015 11.017V10a1 1 0 00-1-1zM10 6a4 4 0 100 8 4 4 0 000-8z" /></svg>
                <span class="break-words leading-tight">{{ $song->reason }}</span>
            </div>
        @endif
    </div>
</div>