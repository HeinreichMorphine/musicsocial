<div class="bg-white shadow rounded-lg overflow-hidden mb-4">
    <img class="w-full h-48 object-cover" src="{{ $share->album_art_url }}" alt="Album Art">
    <div class="p-4">
        <h3 class="font-semibold text-lg">{{ $share->track_name }}</h3>
        <p class="text-gray-600">{{ $share->artist_name }}</p>
        <div class="mt-4">
            <a href="{{ $share->external_url }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Listen</a>
        </div>
    </div>
</div>
