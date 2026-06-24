<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Share;
use App\Models\Song;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Collection;

// Create mock relations
$user = new User([
    'name' => 'Test User',
    'spotify_id' => 'spotify-user-123',
    'spotify_token' => 'mock-token',
    'spotify_refresh_token' => 'mock-refresh-token',
    'spotify_product' => 'premium', // or free
]);
$user->id = 1;

$song = new Song([
    'track_name' => 'Parallel Night',
    'artist_name' => 'Seo Youngju',
    'album_art_url' => 'https://example.com/album.jpg',
    'spotify_track_id' => '0IP8Jsqa3qjFYmZsWtwI1V',
    'spotify_url' => 'https://open.spotify.com/track/0IP8Jsqa3qjFYmZsWtwI1V',
    'preview_url' => 'https://example.com/preview.mp3',
]);
$song->id = 1;

$share = new Share([
    'caption' => 'my current fav kdrama song',
    'type' => 'song',
]);
$share->id = 1;
$share->created_at = now();
$share->setRelation('user', $user);
$share->setRelation('song', $song);
$share->setRelation('comments', new Collection());
$share->setRelation('likes', new Collection());
$share->setRelation('dislikes', new Collection());
$share->setRelation('bookmarks', new Collection());

try {
    // Force log in user
    auth()->login($user);
    
    // Render the blade component
    $html = Blade::render('<x-share-card :share="$share" />', ['share' => $share]);
    echo "SUCCESS:\n";
    echo $html;
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
