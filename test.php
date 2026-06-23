<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cleaner = new \App\Services\GenreCleanerService();
$genres1 = $cleaner->clean(['Contemporary R&B']);
$genres2 = $cleaner->clean(['Hip-Hop/Rap']);

echo "1: " . json_encode($genres1) . "\n";
echo "2: " . json_encode($genres2) . "\n";

$endpoint = base64_decode('aHR0cHM6Ly9pdHVuZXMuYXBwbGUuY29tL3NlYXJjaA==');
$heuristicResponse = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])->timeout(10)->get($endpoint, [
    'term' => 'Dyar Pshder Dancing in the Crowd',
    'entity' => 'song',
    'limit' => 1
]);

echo "HTTP 1: " . json_encode($heuristicResponse->json('results')) . "\n";
