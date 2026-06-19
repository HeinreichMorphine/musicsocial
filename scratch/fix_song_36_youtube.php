<?php

use App\Models\Song;
use App\Models\Share;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$videoId = 'acA8Rr3gEco';
$youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoId;

$songPayload = [
    'spotify_track_id' => '16Z0an8D4BJNm3VbWWpTnv',
    'youtube_video_id' => $videoId,
    'track_name' => "Past Won't Leave My Bed",
    'artist_name' => 'Joji',
    'album_art_url' => 'https://i.scdn.co/image/ab67616d0000b273a51d2c2b79d59becd0e48dcd',
    'spotify_url' => 'https://open.spotify.com/track/16Z0an8D4BJNm3VbWWpTnv',
    'youtube_url' => $youtubeUrl,
    'genres' => json_encode([
        'non-music', 'classical', 'contemporary', 'alt-pop', 'alternative r&b',
        'art pop', 'ballad', 'instrumental hip hop', 'lo-fi hip hop', 'r&b/soul',
        'trip hop', 'Non-Music', 'Classical', 'Contemporary',
    ]),
    'release_date' => '2025-11-07',
];

echo "=== Before ===\n";
$song = Song::find(36);
if ($song) {
    echo "Song 36: youtube_video_id={$song->youtube_video_id} youtube_url={$song->youtube_url}\n";
} else {
    echo "Song 36 not found — will create.\n";
}

$user = User::where('name', 'adam507132131')->first();
if ($user) {
    $shares = Share::where('user_id', $user->id)
        ->whereHas('song', fn ($q) => $q->where('track_name', "Past Won't Leave My Bed"))
        ->with('song')
        ->get();
    echo "Shares by adam507132131 for this track: {$shares->count()}\n";
    foreach ($shares as $share) {
        echo "  Share {$share->id} song_id={$share->song_id} yt={$share->song?->youtube_video_id}\n";
    }
} else {
    echo "User adam507132131 not found.\n";
}

echo "\n=== Applying fix ===\n";
$song = Song::updateOrCreate(['id' => 36], $songPayload);
echo "Song 36 updated: youtube_video_id={$song->youtube_video_id}\n";

if ($user) {
    $updated = Share::where('user_id', $user->id)
        ->whereHas('song', fn ($q) => $q->where('track_name', "Past Won't Leave My Bed"))
        ->update(['song_id' => 36]);
    echo "Shares re-linked to song_id 36: {$updated}\n";
}

// Remove orphan duplicate songs for same spotify track (optional cleanup)
$dupes = Song::where('spotify_track_id', '16Z0an8D4BJNm3VbWWpTnv')
    ->where('id', '!=', 36)
    ->get();
foreach ($dupes as $dupe) {
    $shareCount = Share::where('song_id', $dupe->id)->count();
    echo "Duplicate song id={$dupe->id} yt={$dupe->youtube_video_id} shares={$shareCount}\n";
    if ($shareCount > 0) {
        Share::where('song_id', $dupe->id)->update(['song_id' => 36]);
        echo "  -> moved {$shareCount} share(s) to song 36\n";
    }
    if (Share::where('song_id', $dupe->id)->doesntExist()) {
        $dupe->delete();
        echo "  -> deleted orphan duplicate song {$dupe->id}\n";
    }
}

echo "\n=== After ===\n";
$song = Song::find(36);
echo "Song 36: youtube_video_id={$song->youtube_video_id} youtube_url={$song->youtube_url}\n";
echo "Embed URL: https://www.youtube.com/embed/{$song->youtube_video_id}\n";
