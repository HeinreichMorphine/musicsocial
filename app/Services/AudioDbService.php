<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioDbService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.audiodb.base_url');
        $this->apiKey = config('services.audiodb.api_key');
    }

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
            // Per v1 docs: searchtrack.php?s={Artist}&t={Track}
            $response = Http::get($this->baseUrl . $this->apiKey . '/searchtrack.php', [
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
