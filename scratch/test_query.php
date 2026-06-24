<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
$likeExpression = $driver === 'sqlite'
    ? "'%[SONG:' || songs.spotify_track_id || ']%'"
    : "concat('%[SONG:', songs.spotify_track_id, ']%')";

$songs = \App\Models\Song::select('songs.*')
    ->addSelect([
        'comments_count' => \App\Models\Comment::selectRaw('count(*)')
            ->whereRaw("comments.body LIKE {$likeExpression}")
    ])
    ->withCount('shares')
    ->get();

echo "Driver: $driver\n";
echo "Total Songs: " . count($songs) . "\n";
foreach ($songs as $song) {
    if ($song->shares_count > 0 || $song->comments_count > 0) {
        echo "Song ID {$song->id} ({$song->track_name}): Shares={$song->shares_count}, Comments={$song->comments_count}\n";
    }
}
