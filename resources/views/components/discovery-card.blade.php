@props(['song'])
@props(['song'])
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
class="group block relative overflow-hidden rounded-3xl bg-white/40 dark:bg-gray-900 backdrop-blur-md border border-white/20 dark:border-white/10 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
    
    <div class="relative w-full aspect-square p-1 bg-white/20 dark:bg-white/5 backdrop-blur-sm rounded-sm shadow-xl overflow-hidden flex">
        <!-- CD Spine -->
        <div class="w-4 h-full bg-gradient-to-r from-gray-800 to-gray-600 dark:from-black dark:to-gray-800 border-r border-white/20 flex flex-col justify-between py-1 shrink-0 z-20 relative shadow-md">
             <!-- Hinge Details -->
            <div class="w-full h-px bg-white/30 mb-1"></div>
            <div class="space-y-1 px-0.5">
                <div class="h-8 w-full border-t border-b border-white/10 bg-black/20"></div>
                <div class="h-8 w-full border-t border-b border-white/10 bg-black/20"></div>
            </div>
             <div class="w-full h-px bg-white/30 mt-1"></div>
        </div>

        <!-- Album Art Container -->
        <div class="relative flex-1 h-full overflow-hidden group-hover:shadow-inner">
             <!-- Plastic Shell Overlay -->
            <div class="absolute inset-0 z-30 pointer-events-none bg-gradient-to-tr from-white/10 via-transparent to-white/20 opacity-80 mix-blend-overlay"></div>
             <!-- Highlight Reflection -->
            <div class="absolute top-0 right-0 w-full h-1/2 bg-gradient-to-b from-white/10 to-transparent skew-x-12 z-30 pointer-events-none opacity-50"></div>

            <div class="absolute inset-0 bg-cover bg-center blur-xl opacity-0 group-hover:opacity-40 transition-opacity duration-500 scale-110" style="background-image: url('{{ $song->album_art_url }}');"></div>
            <img class="relative z-10 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $song->album_art_url }}" alt="Album Art">
            <div class="absolute inset-0 z-20 bg-black/20 group-hover:bg-black/0 transition-colors duration-300"></div>
        </div>
        
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
        <h3 class="font-bold text-lg text-gray-900 dark:text-white truncate group-hover:text-custom-mid-blue dark:group-hover:text-blue-400 transition-colors">{{ $song->track_name }}</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $song->artist_name }}</p>
        @if(isset($song->reason))
            <div class="mt-3 flex items-start text-xs text-indigo-600 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/40 rounded-lg p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1.017a7.002 7.002 0 016.29 6.291 1 1 0 11-1.98.208A5.002 5.002 0 0010 5V4a1 1 0 01-1-1zm0 16a7.002 7.002 0 01-6.29-6.291 1 1 0 111.98-.208A5.002 5.002 0 0010 15v1a1 1 0 01-1 1zm-5-8a1 1 0 00-1 1v1.017a7.002 7.002 0 006.29 6.291 1 1 0 10.208-1.98A5.002 5.002 0 015 10V9a1 1 0 00-1-1zm11 0a1 1 0 00-1 1v1.017a5.002 5.002 0 01-4.792 4.973 1 1 0 10.208 1.98A7.002 7.002 0 0015 11.017V10a1 1 0 00-1-1zM10 6a4 4 0 100 8 4 4 0 000-8z" /></svg>
                <span class="break-words leading-tight">{{ $song->reason }}</span>
            </div>
        @endif

        <!-- Interaction Zone -->
        <div class="mt-4 pt-3 border-t border-gray-100/50 dark:border-gray-700/50">
            <div x-show="!listened" class="flex items-center justify-center">
                 <label class="flex items-center space-x-2 cursor-pointer text-gray-500 dark:text-gray-400 hover:text-custom-mid-blue dark:hover:text-blue-400 transition-colors">
                    <input type="checkbox" @change="markInteraction('listen')" class="rounded text-custom-mid-blue focus:ring-custom-mid-blue/50 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    <span class="text-sm font-medium">I've listened to this</span>
                </label>
            </div>
            
            <div x-show="listened" style="display: none;" class="flex justify-center space-x-4">
                <button @click="markInteraction('dislike')" class="flex items-center space-x-1 text-gray-400 hover:text-red-500 transition-colors" title="Dislike (Removes from feed)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="text-xs">Pass</span>
                </button>
                <button @click="markInteraction('like')" class="flex items-center space-x-1 text-gray-400 hover:text-green-500 transition-colors" title="Like (Removes from feed)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    <span class="text-xs">Like</span>
                </button>
            </div>
        </div>
    </div>
</div>