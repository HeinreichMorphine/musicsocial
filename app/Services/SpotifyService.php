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
     * Get genres for a track from Spotify, with fallbacks to MusicBrainz and Discogs.
     *
     * @param string $trackId
     * @return array
     */
    public function getGenresForTrack(string $trackId): array
    {
        $data = $this->getGenresWithSources($trackId);
        return $data['genres'] ?? [];
    }

    /**
     * Get genres for a track with detailed source information.
     *
     * @param string $trackId
     * @return array
     */
    public function getGenresWithSources(string $trackId): array
    {
        // Cache the detailed result (v2 cache key)
        return Cache::remember("genres_track_v2_{$trackId}", 60 * 60 * 24 * 7, function () use ($trackId) {
            $token = $this->getAccessToken();
            if (!$token) {
                return ['genres' => [], 'sources' => []];
            }

            $trackResponse = Http::withToken($token)
                ->timeout(30)
                ->get($this->baseUrl . 'tracks/' . $trackId);

            if ($trackResponse->failed()) {
                return ['genres' => [], 'sources' => []];
            }

            $track = $trackResponse->json();
            $artistName = $track['artists'][0]['name'] ?? 'Unknown Artist';
            $trackName = $track['name'] ?? 'Unknown Track';
            
            $debugSources = [
                'spotify_artist' => [],
                'spotify_album' => [],
                'musicbrainz' => [],
                'discogs' => [],
            ];
            $allGenres = [];

            // 1. Spotify Artist Genres
            try {
                $artistIds = array_map(fn($artist) => $artist['id'], $track['artists']);
                $artistsResponse = Http::withToken($token)
                    ->timeout(30)
                    ->get($this->baseUrl . 'artists', ['ids' => implode(',', $artistIds)]);

                if ($artistsResponse->successful()) {
                    $artists = $artistsResponse->json('artists');
                    $spotifyArtistGenres = collect($artists)->pluck('genres')->flatten()->all();
                    $debugSources['spotify_artist'] = $spotifyArtistGenres;
                    $allGenres = array_merge($allGenres, $spotifyArtistGenres);
                }
            } catch (\Exception $e) {
                Log::error('SpotifyService: Failed to fetch artist genres - ' . $e->getMessage());
            }

            // 2. Spotify Album Genres
            if (!empty($track['album']['id'])) {
                try {
                    $albumResponse = Http::withToken($token)
                        ->timeout(30)
                        ->get($this->baseUrl . 'albums/' . $track['album']['id']);

                    if ($albumResponse->successful()) {
                        $album = $albumResponse->json();
                        $spotifyAlbumGenres = $album['genres'] ?? [];
                        $debugSources['spotify_album'] = $spotifyAlbumGenres;
                        $allGenres = array_merge($allGenres, $spotifyAlbumGenres);
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: Failed to fetch album genres - ' . $e->getMessage());
                }
            }

            // 3. (NEW) Discogs Genres (High Quality)
            try {
                $discogsService = new \App\Services\DiscogsService();
                $discogsTags = $discogsService->getGenres($artistName, $trackName);
                if (!empty($discogsTags)) {
                     $debugSources['discogs'] = $discogsTags;
                     $allGenres = array_merge($allGenres, $discogsTags);
                }
            } catch (\Exception $e) {
                Log::error('SpotifyService: Discogs Error: ' . $e->getMessage());
            }

            // 4. Fallback: MusicBrainz (Only if we don't have enough data)
            // If we have less than 3 genres from Spotify + Discogs, try this.
            if (count(array_unique($allGenres)) < 3) {
                
                // MusicBrainz
                try {
                    $musicBrainzService = new MusicBrainzService();
                    $mbGenres = $musicBrainzService->getArtistGenres($artistName);
                    if (is_array($mbGenres) && !isset($mbGenres['error'])) {
                        $debugSources['musicbrainz_fallback'] = $mbGenres;
                        $allGenres = array_merge($allGenres, $mbGenres);
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: MusicBrainz Error: ' . $e->getMessage());
                }
            }

            // Clean and Sanitize
            $cleaner = new GenreCleanerService();
            $uniqueGenres = $cleaner->clean($allGenres);
            
            // Limit to top 5 AFTER cleaning to ensure they are high quality
            $finalGenres = array_slice($uniqueGenres, 0, 5);

            return [
                'genres' => $finalGenres,
                'sources' => $debugSources
            ];
        });
    }
    /**
     * Get a user's recently played tracks from Spotify.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getUserRecentlyPlayed($user)
    {
        if (!$user->spotify_token) {
            return [];
        }

        $response = Http::withToken($user->spotify_token)
            ->timeout(30)
            ->get($this->baseUrl . 'me/player/recently-played', [
                'limit' => 10,
            ]);

        // Attempt to refresh token if 401 (Expired)
        if ($response->status() === 401 && $user->spotify_refresh_token) {
             $newToken = $this->refreshUserToken($user);
             if ($newToken) {
                 $response = Http::withToken($newToken)
                    ->timeout(30)
                    ->get($this->baseUrl . 'me/player/recently-played', [
                        'limit' => 10,
                    ]);
             }
        }

        if ($response->failed()) {
            Log::error('Spotify Recently Played Error: ' . $response->status() . ' - ' . $response->body());
            return [];
        }
        
        $items = $response->json('items') ?? [];
        
        // Filter out duplicates based on track ID
        $uniqueItems = collect($items)->unique('track.id')->values()->all();

        Log::info('Spotify Recently Played fetched: ' . count($uniqueItems) . ' unique items (from ' . count($items) . ' total).');
        return $uniqueItems;
    }

    /**
     * Refresh a user's Spotify access token.
     * 
     * @param \App\Models\User $user
     * @return string|null
     */
    public function refreshUserToken($user)
    {
        try {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->spotify_refresh_token,
                'client_id' => config('services.spotify.client_id'),
                'client_secret' => config('services.spotify.client_secret'),
            ]);

            if ($response->successful()) {
                $check = $response->json();
                $accessToken = $check['access_token'] ?? null;
                
                if (!$accessToken) {
                    Log::error('Spotify Token Refresh: Access token missing in response', $check);
                    return null;
                }

                // Update user
                $user->spotify_token = $accessToken;
                if (isset($check['refresh_token'])) {
                    $user->spotify_refresh_token = $check['refresh_token'];
                }
                $user->save();
                return $accessToken;
            }
            
            Log::error('Spotify Token Refresh Failed: ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Spotify Token Refresh Exception: ' . $e->getMessage());
        }
        return null;
    }
    /**
     * Get the current user's playlists.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getUserPlaylists($user)
    {
        if (!$user->spotify_token) return [];

        $response = Http::withToken($user->spotify_token)
            ->get($this->baseUrl . 'me/playlists', ['limit' => 50]);

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $this->refreshUserToken($user);
            if ($newToken) {
                $response = Http::withToken($newToken)
                    ->get($this->baseUrl . 'me/playlists', ['limit' => 50]);
            }
        }

        if ($response->failed()) {
            Log::error('Spotify Get Playlists Error: ' . $response->body());
            return [];
        }

        return $response->json('items') ?? [];
    }

    /**
     * Create a new playlist for the user.
     *
     * @param \App\Models\User $user
     * @param string $name
     * @return array|null
     */
    public function createPlaylist($user, $name)
    {
        if (!$user->spotify_token) return null;

        // Get User ID first
        $profileResponse = Http::withToken($user->spotify_token)->get($this->baseUrl . 'me');
        $spotifyUserId = $profileResponse->json('id');

        if (!$spotifyUserId) return null;

        $response = Http::withToken($user->spotify_token)
            ->post($this->baseUrl . "users/{$spotifyUserId}/playlists", [
                'name' => $name,
                'description' => 'Created via MusicSocial',
                'public' => false
            ]);

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $this->refreshUserToken($user);
            if ($newToken) {
                $response = Http::withToken($newToken)
                    ->post($this->baseUrl . "users/{$spotifyUserId}/playlists", [
                        'name' => $name,
                        'description' => 'Created via MusicSocial',
                        'public' => false
                    ]);
            }
        }

        if ($response->failed()) {
             Log::error('Spotify Create Playlist Error: ' . $response->body());
             return null;
        }

        return $response->json();
    }

    /**
     * Add a track to a playlist.
     *
     * @param \App\Models\User $user
     * @param string $playlistId
     * @param string $trackUri (e.g., spotify:track:xxxx)
     * @return bool
     */
    public function addTrackToPlaylist($user, $playlistId, $trackUri)
    {
        if (!$user->spotify_token) return false;

        $response = Http::withToken($user->spotify_token)
            ->post($this->baseUrl . "playlists/{$playlistId}/tracks", [
                'uris' => [$trackUri]
            ]);

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $this->refreshUserToken($user);
            if ($newToken) {
                $response = Http::withToken($newToken)
                    ->post($this->baseUrl . "playlists/{$playlistId}/tracks", [
                        'uris' => [$trackUri]
                    ]);
            }
        }

        if ($response->failed()) {
            Log::error('Spotify Add Track Error: ' . $response->body());
            return false;
        }

        return true;
    }
}