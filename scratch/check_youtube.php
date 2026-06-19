<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$trackName = "Past Won't Leave My Bed";
$artistName = "Joji";

$youtubeService = app(App\Services\YouTubeService::class);
$videoData = $youtubeService->searchVideo("$trackName $artistName");
$videoId = $videoData['video_id'] ?? null;

echo "Found Video ID: " . ($videoId ?: "NONE") . "\n";
