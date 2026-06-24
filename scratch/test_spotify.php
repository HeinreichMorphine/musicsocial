<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spotifyService = app(App\Services\SpotifyService::class);
$trackId = '43iIQbw5hx986dUEZbr3eN';

echo "Testing Spotify track fetch for ID: $trackId\n";
$result = $spotifyService->getTrack($trackId);

if (isset($result['error'])) {
    echo "ERROR: " . $result['error'] . "\n";
} else {
    echo "SUCCESS!\n";
    echo "Song Title: " . $result['song']->track_name . "\n";
    echo "Artist: " . $result['song']->artist_name . "\n";
    echo "Spotify URL: " . $result['song']->spotify_url . "\n";
}
