<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    protected $baseUrl = 'https://api.spotify.com/v1/';
    protected $tokenUrl = 'https://accounts.spotify.com/api/token';

    /**
     * Get a valid access token from cache or request a new one.
     */
    public function getAccessToken()
    {
        return Cache::remember('spotify_access_token', 3540, function () {
            $response = Http::asForm()
                ->timeout(30) // Add timeout
                ->withBasicAuth(config('services.spotify.client_id'), config('services.spotify.client_secret'))
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                Log::error('Spotify Token API Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            Log::info('Successfully fetched new Spotify access token.');
            return $response->json('access_token');
        });
    }

    /**
     * Search for tracks on Spotify.
     */
    public function searchTracks($query, $limit = 10)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::error('Cannot search tracks; Spotify access token is missing.');
            return [];
        }

        $response = Http::withToken($token)
            ->timeout(30) // Add timeout
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
        if (!$token) {
            return ['error' => 'Cannot get track; Spotify access token is missing or invalid. Check your .env credentials and clear the cache.'];
        }

        // Get track details
        $trackResponse = Http::withToken($token)
            ->timeout(30) // Add timeout
            ->get($this->baseUrl . 'tracks/' . $trackId);

        if ($trackResponse->failed()) {
            return ['error' => "Spotify Track API Error ({$trackResponse->status()}): " . $trackResponse->body()];
        }

        $track = $trackResponse->json();

        // Get all artist IDs from the track
        $artistIds = array_map(fn($artist) => $artist['id'], $track['artists']);

        // Fetch details for all artists in a single call
        $artistsResponse = Http::withToken($token)
            ->timeout(30) // Add timeout
            ->get($this->baseUrl . 'artists', ['ids' => implode(',', $artistIds)]);

        if ($artistsResponse->failed()) {
            // This is less critical, so we don't return a hard error, just empty genres
            return ['track' => $track, 'artist' => null, 'genres' => []];
        }

        $artists = $artistsResponse->json('artists');

        // Aggregate genres from all artists
        $genres = collect($artists)->pluck('genres')->flatten()->unique()->values()->all();

        // Fallback to album genres if artist genres are empty
        if (empty($genres) && !empty($track['album']['id'])) {
            $albumResponse = Http::withToken($token)
                ->timeout(30)
                ->get($this->baseUrl . 'albums/' . $track['album']['id']);

            if ($albumResponse->successful()) {
                $album = $albumResponse->json();
                $genres = $album['genres'] ?? [];
            }
        }

        $primaryArtist = $artists[0] ?? null;

        return ['track' => $track, 'artist' => $primaryArtist, 'genres' => $genres];
    }
}
