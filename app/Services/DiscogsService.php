<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscogsService
{
    protected $baseUrl = 'https://api.discogs.com/';
    protected $consumerKey;
    protected $consumerSecret;
    protected $userAgent;

    public function __construct()
    {
        $this->consumerKey = config('services.discogs.key');
        $this->consumerSecret = config('services.discogs.secret');
        // Discogs requires a custom User-Agent
        $this->userAgent = 'ResoApp/1.0';
    }

    /**
     * Get Styles (Niche Genres) and Genres for a Track by searching for its release.
     *
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    public function getGenres(string $artistName, string $trackName): array
    {
        if (!$this->consumerKey || !$this->consumerSecret) {
            Log::warning('DiscogsService: Credentials missing.');
            return [];
        }

        // 1. Prepare Artist Candidates (Handle "Dave, Central Cee" or "Artist A feat. Artist B")
        // We prioritize longer names because they are more specific and less likely to be ambiguous (e.g. "Central Cee" > "Dave")
        $candidates = preg_split('/(,|&|feat\.|ft\.)/i', $artistName);
        $candidates = array_map('trim', $candidates);
        $candidates = array_filter($candidates); // remove empty
        
        // Sort by length DESC (Longest first)
        usort($candidates, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($candidates as $candidate) {
            $genres = $this->searchForArtistStyles($candidate, $trackName);
            if (!empty($genres)) {
                return $genres;
            }
        }

        return [];
    }

    /**
     * Perform the dual-strategy search (Release -> Fallback to Artist) for a single artist name.
     *
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    protected function searchForArtistStyles($artistName, $trackName) {
        $query = $artistName . ' - ' . $trackName;

        try {
            // 1. Specific Release Search (Best Quality)
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Authorization' => "Discogs key={$this->consumerKey}, secret={$this->consumerSecret}",
            ])->get($this->baseUrl . 'database/search', [
                'q' => $query,
                'type' => 'release', // 'release' or 'master'
                'per_page' => 5,     // We only need the top result
            ]);

            if ($response->failed()) {
                Log::error('Discogs API Error: ' . $response->status() . ' - ' . $response->body());
                return [];
            }

            $results = $response->json('results');

            $foundRelease = null;

            if (!empty($results)) {
                foreach ($results as $result) {
                    if (stripos($result['title'], $artistName) !== false) {
                        $foundRelease = $result;
                        break;
                    } else {
                         Log::info("Discogs: Skipped result '{$result['title']}' because it didn't match artist '$artistName'");
                    }
                }
            }

            // FALLBACK: If Strict Search failed, try finding ANY release by this artist
            // This captures genres for album tracks that aren't singles (e.g. "Wave to Earth - love.")
            // SAFETY CHECK: Only do this for artist names >= 3 chars to avoid collisions (e.g. "CB" -> Reggae false positive)
            if (!$foundRelease && strlen($artistName) >= 3) {
                Log::info("Discogs: Specific release not found for '$query'. Trying Artist Fallback...");
                
                // Quote the artist name to force exact match
                $fallbackQuery = '"' . $artistName . '"';
                
                $fallbackResponse = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Authorization' => "Discogs key={$this->consumerKey}, secret={$this->consumerSecret}"
                ])->get($this->baseUrl . 'database/search', [
                    'q' => $fallbackQuery,
                    'type' => 'release', // Find any release by them
                    'per_page' => 5
                ]);

                if ($fallbackResponse->failed()) {
                    Log::error('Discogs API Fallback Error: ' . $fallbackResponse->status() . ' - ' . $fallbackResponse->body());
                    return [];
                }

                $fallbackResults = $fallbackResponse->json('results');
                
                if (!empty($fallbackResults)) {
                    foreach ($fallbackResults as $result) {
                        // Verify this release is actually by the artist (Start of title or followed by a dash)
                        if (stripos($result['title'], $artistName) === 0 || stripos($result['title'], $artistName.' -') !== false) {
                            $foundRelease = $result;
                            Log::info("Discogs: Success using Artist Fallback. Found: '{$result['title']}'");
                            break;
                        }
                    }
                }
            } elseif (!$foundRelease) {
                Log::info("Discogs: Skipping Artist Fallback for '$artistName' (Name too short/unsafe)");
            }

            if (!$foundRelease) {
                Log::info("Discogs: No results found even after fallback for '$artistName'");
                return [];
            }
            
            $broadGenres = $foundRelease['genre'] ?? []; // e.g. ["Rock"]
            $nicheStyles = $foundRelease['style'] ?? []; // e.g. ["Shoegaze", "Dream Pop"]

            // Merge them, but prioritize Styles for your "Taste DNA"
            $allTags = array_merge($broadGenres, $nicheStyles);

            return array_values(array_unique($allTags));

        } catch (\Exception $e) {
            Log::error('DiscogsService Exception: ' . $e->getMessage());
            return [];
        }
    }
}
