<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cleaner = new \App\Services\GenreCleanerService();
$genres = $cleaner->clean(['Contemporary R&B']);
var_dump($genres);

$endpoint = base64_decode('aHR0cHM6Ly9pdHVuZXMuYXBwbGUuY29tL3NlYXJjaA==');
$heuristicResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($endpoint, [
    'term' => 'Dyar Pshder Dancing in the Crowd',
    'entity' => 'song',
    'limit' => 1
]);

var_dump($heuristicResponse->json('results')[0]['primaryGenreName']);
