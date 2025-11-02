<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    protected $baseUrl = 'https://api.spotify.com/v1/';
    protected $tokenUrl = 'https://accounts.spotify.com/api/token';

    /**
     * Get a valid access token from cache or request a new one.
     */
    public function getAccessToken()
    {
        // Remember the token for 59 minutes (Spotify tokens expire in 60)
        return Cache::remember('spotify_access_token', 3540, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('services.spotify.client_id'), config('services.spotify.client_secret'))
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                ]);

            return $response->json('access_token');
        });
    }

    /**
     * Search for tracks on Spotify.
     */
    public function searchTracks($query, $limit = 10)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->get($this->baseUrl . 'search', [
                'q' => $query,
                'type' => 'track',
                'limit' => $limit,
            ]);

        return $response->json('tracks.items');
    }
    /**
     * Get a single track's details from Spotify.
     */
    public function getTrack(string $trackId)
    {
        $token = $this->getAccessToken();

        // Get track details
        $trackResponse = Http::withToken($token)
            ->get($this->baseUrl . 'tracks/' . $trackId);

        if ($trackResponse->failed()) {
            return null;
        }

        $track = $trackResponse->json();

        // Get artist details to fetch genres
        $artistId = $track['artists'][0]['id'];
        $artistResponse = Http::withToken($token)
            ->get($this->baseUrl . 'artists/' . $artistId);

        if ($artistResponse->failed()) {
            // Return track data even if artist lookup fails
            return ['track' => $track, 'artist' => null];
        }

        $artist = $artistResponse->json();

        return ['track' => $track, 'artist' => $artist];
    }
}
