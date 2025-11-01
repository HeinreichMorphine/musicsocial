<div class="bg-custom-periwinkle/50 rounded-xl shadow-lg p-5 mt-6 space-y-3">
    <h3 class="text-xl font-bold text-custom-dark-blue">Suggested for you</h3>
    @forelse ($recommendedShares->take(5) as $share)
        <div class="flex items-center space-x-3">
            <img src="{{ $share->album_art_url }}" alt="Album Art" class="w-12 h-12 rounded">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $share->track_name }}</p>
                <p class="text-xs text-gray-600 truncate">{{ $share->artist_name }}</p>
            </div>
        </div>
    @empty
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    @endforelse
</div>
