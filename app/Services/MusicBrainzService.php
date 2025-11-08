<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MusicBrainzService
{
    protected $baseUrl = 'https://musicbrainz.org/ws/2/';
    protected $userAgent;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->userAgent = config('services.musicbrainz.user_agent');
        if (empty($this->userAgent)) {
            Log::error('MusicBrainz User-Agent is not configured. Please set MUSICBRAINZ_USER_AGENT in your .env file.');
        }
    }

    /**
     * Get genres for an artist from MusicBrainz.
     *
     * @param string $artistName
     * @return array|null
     */
    public function getArtistGenres(string $artistName): ?array
    {
        if (empty($this->userAgent)) {
            return ['error' => 'MusicBrainz User-Agent is not configured. Please set MUSICBRAINZ_USER_AGENT in your .env file.'];
        }

        try {
            // 1. Search for the artist to get their MBID
            $searchResponse = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->retry(3, 100)->timeout(10)->get($this->baseUrl . 'artist/', [
                'query' => 'artist:' . $artistName,
                'fmt' => 'json',
                'limit' => 1, // Get the most relevant artist
            ]);

            if ($searchResponse->failed()) {
                return ['error' => "MusicBrainz Artist search failed for '{$artistName}'. Status: {$searchResponse->status()}, Body: {$searchResponse->body()}"];
            }
            if (empty($searchResponse->json('artists'))) {
                return null; // No error, just no artists found
            }

            $artistMbid = $searchResponse->json('artists.0.id');

            // 2. Lookup the artist by MBID to get tags
            $lookupResponse = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->retry(3, 100)->timeout(10)->get($this->baseUrl . 'artist/' . $artistMbid, [
                'inc' => 'tags', // Include tags
                'fmt' => 'json',
            ]);

            if ($lookupResponse->failed()) {
                return ['error' => "MusicBrainz Artist lookup failed for MBID '{$artistMbid}'. Status: {$lookupResponse->status()}, Body: {$lookupResponse->body()}"];
            }

            $artistData = $lookupResponse->json();

            $genres = [];
            if (!empty($artistData['tags'])) {
                foreach ($artistData['tags'] as $tag) {
                    // MusicBrainz tags often have a 'count' and 'name'
                    $genres[] = $tag['name'];
                }
            }
            return $genres;

        } catch (\Exception $e) {
            Log::error("MusicBrainz API Error for artist '{$artistName}': " . $e->getMessage());
            return ['error' => "MusicBrainz API Error for artist '{$artistName}': " . $e->getMessage()];
        }
    }
}