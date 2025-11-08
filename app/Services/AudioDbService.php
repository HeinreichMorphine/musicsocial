<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioDbService
{
    protected $baseUrl = 'https://www.theaudiodb.com/api/v1/json/';
    protected $apiKey = '2'; // TheAudioDB provides a '2' for a free API key

    /**
     * Get genres for a track from TheAudioDB.
     *
     * @param string $trackName
     * @param string $artistName
     * @return array
     */
    public function getGenres(string $trackName, string $artistName): array
    {
        try {
            $response = Http::get($this->baseUrl . $this->apiKey . '/search.php', [
                's' => $artistName,
                't' => $trackName,
            ]);

            if ($response->successful() && !empty($response->json('track'))) {
                $track = $response->json('track')[0];
                if (!empty($track['strGenre'])) {
                    return [$track['strGenre']];
                }
            }
        } catch (\Exception $e) {
            Log::error("AudioDB API Error for '{$artistName} - {$trackName}': " . $e->getMessage());
        }

        return [];
    }
}
