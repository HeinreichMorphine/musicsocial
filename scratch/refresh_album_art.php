<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Song;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Log;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spotifyService = app(SpotifyService::class);
$songs = Song::all();

echo "Starting Forceful Album Art Repair for " . $songs->count() . " songs...\n";

foreach ($songs as $index => $song) {
    echo "Processing [" . ($index + 1) . "/" . $songs->count() . "]: {$song->track_name} - {$song->artist_name}...\n";
    
    $found = false;
    
    // 1. Try fetching by existing ID if it exists
    if ($song->spotify_track_id) {
        try {
            $trackData = $spotifyService->getRawTrack($song->spotify_track_id);
            if ($trackData && isset($trackData['album']['images'][0]['url'])) {
                $song->album_art_url = $trackData['album']['images'][0]['url'];
                $song->save();
                echo "  ✓ Fixed via existing ID.\n";
                $found = true;
            }
        } catch (\Exception $e) {}
    }
    
    // 2. If ID lookup failed or art is still missing/invalid, search by name
    if (!$found) {
        echo "  🔍 ID failed. Searching by name...\n";
        $searchResults = $spotifyService->searchTracks("{$song->track_name} {$song->artist_name}", 1);
        
        if (!empty($searchResults) && isset($searchResults[0]['album']['images'][0]['url'])) {
            $track = $searchResults[0];
            $song->spotify_track_id = $track['id'];
            $song->album_art_url = $track['album']['images'][0]['url'];
            $song->spotify_url = $track['external_urls']['spotify'] ?? $song->spotify_url;
            $song->save();
            echo "  ✓ Fixed via Global Search (New ID: {$track['id']}).\n";
        } else {
            echo "  ✖ Could not find this track anywhere on Spotify.\n";
        }
    }
    
    usleep(100000); // 0.1s rate limiting
}

echo "\nRepair Finished!\n";
