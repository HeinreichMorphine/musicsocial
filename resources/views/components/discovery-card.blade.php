<a href="{{ $share->spotify_url }}" target="_blank" class="block bg-white shadow rounded-lg overflow-hidden mb-4 hover:shadow-xl transition-shadow duration-200">
    <div class="relative w-full" style="padding-top: 100%;">
        <img class="absolute inset-0 w-full h-full object-cover" src="{{ $share->album_art_url }}" alt="Album Art">
    </div>
    <div class="p-4">
        <h3 class="font-semibold text-lg">{{ $share->track_name }}</h3>
            <p class="text-sm text-gray-600 truncate">{{ $share->artist_name }}</p>
            @if(isset($share->reason))
                <div class="mt-2 flex items-center text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1.017a7.002 7.002 0 016.29 6.291 1 1 0 11-1.98.208A5.002 5.002 0 0010 5V4a1 1 0 01-1-1zm0 16a7.002 7.002 0 01-6.29-6.291 1 1 0 111.98-.208A5.002 5.002 0 0010 15v1a1 1 0 01-1 1zm-5-8a1 1 0 00-1 1v1.017a7.002 7.002 0 006.29 6.291 1 1 0 10.208-1.98A5.002 5.002 0 015 10V9a1 1 0 00-1-1zm11 0a1 1 0 00-1 1v1.017a5.002 5.002 0 01-4.792 4.973 1 1 0 10.208 1.98A7.002 7.002 0 0015 11.017V10a1 1 0 00-1-1zM10 6a4 4 0 100 8 4 4 0 000-8z" /></svg>
                    <span>{{ $share->reason }}</span>
                </div>
            @endif
    </div>
</a>
