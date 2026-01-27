<?php

use App\Models\User;
use App\Models\Share;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$searchTerm = 'adamakib5555';
$user = User::where('name', 'LIKE', "%$searchTerm%")
            ->orWhere('email', 'LIKE', "%$searchTerm%")
            ->first();

if (!$user) {
    echo "User '$searchTerm' not found.\n";
    exit;
}

echo "User ID: {$user->id}\n";
echo "Name: {$user->name}\n";

echo "--- RAW DB COUNTS ---\n";
echo "Raw Shares: " . DB::table('shares')->where('user_id', $user->id)->count() . "\n";
echo "Raw Likes: " . DB::table('likes')->where('user_id', $user->id)->count() . "\n";
echo "Raw SongInteractions: " . DB::table('song_interactions')->where('user_id', $user->id)->count() . "\n";

echo "\n--- RAW LIKES DETAILS ---\n";
$rawLikes = DB::table('likes')
    ->join('shares', 'likes.share_id', '=', 'shares.id')
    ->join('songs', 'shares.song_id', '=', 'songs.id')
    ->where('likes.user_id', $user->id)
    ->select('songs.track_name', 'songs.artist_name', 'songs.genres')
    ->get();

foreach ($rawLikes as $l) {
    echo "Like: {$l->track_name} by {$l->artist_name} - Genres: {$l->genres}\n";
}

echo "\n--- GLOBAL STATS ---\n";
echo "Total Shares in DB: " . DB::table('shares')->count() . "\n";
echo "Total Users in DB: " . DB::table('users')->count() . "\n";
