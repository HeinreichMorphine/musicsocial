<?php

namespace App\Services;

use App\Models\Song;
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
                ->timeout(30)
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
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchTracks($query, $limit = 10)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::error('Cannot search tracks; Spotify access token is missing.');
            return [];
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'search', [
                'q' => $query,
                'type' => 'track',
                'limit' => $limit,
            ]);

        return $response->json('tracks.items');
    }

    /**
     * Get a single track's details from Spotify and create/update a Song model.
     *
     * @param string $trackId
     * @return array
     */
    public function getTrack(string $trackId)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['error' => 'Cannot get track; Spotify access token is missing or invalid. Check your .env credentials and clear the cache.'];
        }

        // Get track details
        $trackResponse = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'tracks/' . $trackId);

        if ($trackResponse->failed()) {
            return ['error' => "Spotify Track API Error ({$trackResponse->status()}): " . $trackResponse->body()];
        }

        $track = $trackResponse->json();

        $genres = $this->getGenresForTrack($trackId);

        $primaryArtistName = implode(', ', array_map(fn($a) => $a['name'], $track['artists']));

        // Create or update Song model
        $song = Song::firstOrCreate(
            ['spotify_track_id' => $trackId],
            [
                'track_name' => $track['name'],
                'artist_name' => $primaryArtistName,
                'album_art_url' => $track['album']['images'][0]['url'] ?? null,
                'genres' => !empty($genres) ? json_encode($genres) : null,
                'release_date' => $track['album']['release_date'] ?? null,
                'spotify_url' => $track['external_urls']['spotify'] ?? '#',
            ]
        );

        return ['song' => $song, 'album_art_url' => $track['album']['images'][0]['url'] ?? null];
    }

    /**
     * Get genres for a track from Spotify, with fallbacks to MusicBrainz and TheAudioDB.
     *
     * @param string $trackId
     * @return array
     */
    public function getGenresForTrack(string $trackId): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        $trackResponse = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'tracks/' . $trackId);

        if ($trackResponse->failed()) {
            return [];
        }

        $track = $trackResponse->json();
        $artistName = $track['artists'][0]['name'] ?? 'Unknown Artist';
        $trackName = $track['name'] ?? 'Unknown Track';

        // Spotify artist genres
        try {
            $artistIds = array_map(fn($artist) => $artist['id'], $track['artists']);
            $artistsResponse = Http::withToken($token)
                ->timeout(30)
                ->get($this->baseUrl . 'artists', ['ids' => implode(',', $artistIds)]);

            if ($artistsResponse->successful()) {
                $artists = $artistsResponse->json('artists');
                $genres = collect($artists)->pluck('genres')->flatten()->unique()->values()->all();
            }
        } catch (\Exception $e) {
            Log::error('SpotifyService: Failed to fetch artist genres - ' . $e->getMessage());
        }

        // Spotify album genres
        if (empty($genres) && !empty($track['album']['id'])) {
            try {
                $albumResponse = Http::withToken($token)
                    ->timeout(30)
                    ->get($this->baseUrl . 'albums/' . $track['album']['id']);

                if ($albumResponse->successful()) {
                    $album = $albumResponse->json();
                    $genres = array_merge($genres, $album['genres'] ?? []);
                }
            } catch (\Exception $e) {
                Log::error('SpotifyService: Failed to fetch album genres - ' . $e->getMessage());
            }
        }

        // Fallback to MusicBrainz
        // Fallback to External Services (MusicBrainz & AudioDB) if Spotify fails
        if (empty($genres)) {
            $externalGenres = [];

            // 1. MusicBrainz
            try {
                $musicBrainzService = new MusicBrainzService();
                $mbGenres = $musicBrainzService->getArtistGenres($artistName);
                if (is_array($mbGenres) && !isset($mbGenres['error'])) {
                    $externalGenres = array_merge($externalGenres, $mbGenres);
                }
            } catch (\Exception $e) {
                Log::error('SpotifyService Fallback - MusicBrainz Error: ' . $e->getMessage());
            }

            // 2. AudioDB
            try {
                $audioDbService = new AudioDbService();
                $adbGenres = $audioDbService->getGenres($trackName, $artistName);
                if (!empty($adbGenres)) {
                    $externalGenres = array_merge($externalGenres, $adbGenres);
                }
            } catch (\Exception $e) {
                Log::error('SpotifyService Fallback - AudioDB Error: ' . $e->getMessage());
            }

            $genres = array_unique(array_merge($genres, $externalGenres));
        }

        return array_values(array_unique($genres));
    }
}