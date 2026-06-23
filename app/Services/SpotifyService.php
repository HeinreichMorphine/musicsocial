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
     * Get tracks from a playlist.
     *
     * @param string $playlistId
     * @param int $limit
     * @return array
     */
    public function getPlaylistTrackItems(string $playlistId, int $limit = 10): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::error('Cannot get playlist tracks; Spotify access token is missing.');
            return [];
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . "playlists/{$playlistId}/tracks", [
                'limit' => $limit,
            ]);

        if ($response->failed()) {
            Log::error("Spotify Playlist Tracks API Error ({$response->status()}): " . $response->body());
            return [];
        }

        $items = $response->json('items') ?? [];
        $tracks = [];
        foreach ($items as $item) {
            if (!empty($item['track'])) {
                $tracks[] = $item['track'];
            }
        }

        return $tracks;
    }

    /**
     * Get raw track data from Spotify.
     *
     * @param string $trackId
     * @return array|null
     */
    public function getRawTrack(string $trackId)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            Log::error('Cannot get raw track; Spotify access token is missing.');
            return null;
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'tracks/' . $trackId);

        if ($response->failed()) {
            Log::error("Spotify Raw Track API Error ({$response->status()}): " . $response->body());
            return null;
        }

        return $response->json();
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

        // Normalize release_date for database compatibility (Spotify sometimes returns only 'YYYY')
        $releaseDate = $track['album']['release_date'] ?? null;
        if ($releaseDate && strlen($releaseDate) === 4) {
            $releaseDate .= '-01-01';
        } elseif ($releaseDate && strlen($releaseDate) === 7) {
            $releaseDate .= '-01';
        }

        // Create or update Song model
        $song = Song::firstOrCreate(
            ['spotify_track_id' => $trackId],
            [
                'track_name' => $track['name'],
                'artist_name' => $primaryArtistName,
                'album_art_url' => $track['album']['images'][0]['url'] ?? null,
                'genres' => !empty($genres) ? json_encode($genres) : null,
                'release_date' => $releaseDate,
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
                        // MusicBrainz tags are community-driven and very noisy (often returning artist names)
                        // We must clean them strictly before allowing them in.
                        $mbCleaned = $cleaner->clean($mbGenres, true);
                        if (!empty($mbCleaned)) {
                            $debugSources['musicbrainz_fallback'] = $mbCleaned;
                            $allGenres = array_merge($allGenres, $mbCleaned);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: MusicBrainz Error: ' . $e->getMessage());
                }
            }
            
            // Clean and Sanitize (instantiate early so we can use it for extraction if needed)
            $cleaner = new GenreCleanerService();
            
            // 4. INTERNAL HEURISTIC FALLBACK (iTunes/Apple Music API)
            // Highly structured secondary source before resorting to user-generated tags
            // Run this if we have less than 3 genres to supplement our highly-accurate primary tags
            if (count(array_unique($allGenres)) < 3) {
                try {
                    $endpoint = base64_decode('aHR0cHM6Ly9pdHVuZXMuYXBwbGUuY29tL3NlYXJjaA==');
                    $heuristicResponse = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'])->timeout(10)->get($endpoint, [
                        'term' => $artistName . ' ' . $trackName,
                        'entity' => 'song',
                        'limit' => 1
                    ]);

                    if ($heuristicResponse->successful() && !empty($heuristicResponse->json('results'))) {
                        $metaData = $heuristicResponse->json('results')[0];
                        if (!empty($metaData['primaryGenreName'])) {
                            $extractedTag = $cleaner->clean([$metaData['primaryGenreName']]);
                            if (!empty($extractedTag)) {
                                $debugSources['heuristic_meta_fallback'] = $extractedTag;
                                $allGenres = array_merge($allGenres, $extractedTag);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: Heuristic Fallback Error: ' . $e->getMessage());
                }
            }
            
            // 5. YOUTUBE FALLBACK: Underground/regional tags
            if (count(array_unique($allGenres)) === 0) {
                try {
                    $youtube = new \App\Services\YouTubeService();
                    $video = $youtube->searchVideo($artistName . ' ' . $trackName);
                    if ($video && isset($video['video_id'])) {
                        $videoDetails = $youtube->getVideo($video['video_id']);
                        if (!empty($videoDetails['tags'])) {
                            // Clean strictly to prevent garbage
                            $ytTags = $cleaner->clean($videoDetails['tags'], true);
                            if (!empty($ytTags)) {
                                $debugSources['youtube_tags'] = $ytTags;
                                $allGenres = array_merge($allGenres, $ytTags);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: YouTube Tags Fallback Error: ' . $e->getMessage());
                }
            }
            
            // 6. CONTEXTUAL FALLBACK: Scan Spotify Playlist Descriptions for the Artist
            if (count(array_unique($allGenres)) === 0) {
                try {
                    // Search for "[Artist Name] Radio" using your existing searchPlaylists method
                    $playlists = $this->searchPlaylists($artistName . ' Radio', 3);
                    
                    // If Radio playlist doesn't exist, try searching general public playlists for the artist
                    if (empty($playlists)) {
                        $playlists = $this->searchPlaylists($artistName, 3);
                    }

                    // Combine titles and descriptions to form a searchable text contextual block
                    $combinedText = '';
                    foreach ($playlists as $playlist) {
                        $combinedText .= ' ' . ($playlist['name'] ?? '') . ' ' . ($playlist['description'] ?? '');
                    }

                    if (!empty(trim($combinedText))) {
                        // Use the text extraction tool to isolate genuine genres
                        $playlistGenres = $cleaner->extractFromText($combinedText);
                        
                        if (!empty($playlistGenres)) {
                            $debugSources['spotify_playlist_context'] = $playlistGenres;
                            $allGenres = array_merge($allGenres, $playlistGenres);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('SpotifyService: Playlist Context Fallback Error: ' . $e->getMessage());
                }
            }

            // Final Clean and Sanitize
            $uniqueGenres = $cleaner->clean($allGenres);
            
            // Limit to top 5 AFTER cleaning to ensure they are high quality
            $finalGenres = array_slice($uniqueGenres, 0, 5);



            // 8. LAST-RESORT FALLBACK: Inherit from artist's other songs in our DB
            if (empty($finalGenres)) {
                $sibling = \App\Models\Song::where('artist_name', $artistName)
                    ->whereNotNull('genres')
                    ->where('genres', '!=', '[]')
                    ->first();
                if ($sibling) {
                    $siblingGenres = json_decode($sibling->genres, true);
                    if (is_array($siblingGenres) && !empty($siblingGenres)) {
                        $finalGenres = $siblingGenres;
                        $debugSources['inherited_from_sibling_track'] = $finalGenres;
                    }
                }
            }

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

        $allPlaylists = $response->json('items') ?? [];
        
        // Ensure we have the user's Spotify ID to filter by owner
        $spotifyId = $user->spotify_id;
        if (!$spotifyId) {
            $profileResponse = Http::withToken($user->spotify_token)->get($this->baseUrl . 'me');
            if ($profileResponse->successful()) {
                $spotifyId = $profileResponse->json('id');
                // Use updateQuietly or similar if we don't want to trigger observers, 
                // but standard update is fine here.
                $user->update(['spotify_id' => $spotifyId]);
            }
        }

        if (!$spotifyId) {
            return $allPlaylists; // Fallback to all if still missing
        }

        return array_values(array_filter($allPlaylists, function($playlist) use ($spotifyId) {
            return ($playlist['owner']['id'] ?? '') === $spotifyId;
        }));
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
                'description' => 'Created via Reso',
                'public' => false
            ]);

        if ($response->status() === 401 && $user->spotify_refresh_token) {
            $newToken = $this->refreshUserToken($user);
            if ($newToken) {
                $response = Http::withToken($newToken)
                    ->post($this->baseUrl . "users/{$spotifyUserId}/playlists", [
                        'name' => $name,
                        'description' => 'Created via Reso',
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

    /**
     * Add multiple tracks to a playlist.
     *
     * @param \App\Models\User $user
     * @param string $playlistId
     * @param array $trackUris (e.g., ['spotify:track:xxxx', ...])
     * @return bool
     */
    public function addTracksToPlaylist($user, $playlistId, array $trackUris)
    {
        if (!$user->spotify_token || empty($trackUris)) return false;

        // Spotify API allows up to 100 tracks per request.
        $chunks = array_chunk($trackUris, 100);
        $success = true;

        foreach ($chunks as $chunk) {
            $response = Http::withToken($user->spotify_token)
                ->post($this->baseUrl . "playlists/{$playlistId}/tracks", [
                    'uris' => $chunk
                ]);

            if ($response->status() === 401 && $user->spotify_refresh_token) {
                $newToken = $this->refreshUserToken($user);
                if ($newToken) {
                    $response = Http::withToken($newToken)
                        ->post($this->baseUrl . "playlists/{$playlistId}/tracks", [
                            'uris' => $chunk
                        ]);
                }
            }

            if ($response->failed()) {
                Log::error('Spotify Add Tracks Error: ' . $response->body());
                $success = false;
            }
        }

        return $success;
    }
    /**
     * Get multiple tracks' details from a Spotify playlist.
     *
     * @param string $playlistId
     * @return array|null
     */
    public function getPlaylistTracks(string $playlistId)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'playlists/' . $playlistId);

        if ($response->failed()) {
            Log::error('Spotify Playlist API Error: ' . $response->status() . ' - ' . $response->body());
            return null;
        }

        return $response->json();
    }

    /**
     * Search for playlists on Spotify.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchPlaylists($query, $limit = 10)
    {
        $token = $this->getAccessToken();
        if (!$token) return [];

        $response = Http::withToken($token)
            ->timeout(30)
            ->get($this->baseUrl . 'search', [
                'q' => $query,
                'type' => 'playlist',
                'limit' => $limit,
            ]);

        if ($response->failed()) {
            Log::error('Spotify Search Playlists Error: ' . $response->status());
            return [];
        }

        return $response->json('playlists.items') ?? [];
    }
}