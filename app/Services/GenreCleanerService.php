<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class GenreCleanerService
{
    /**
     * Words that are NOT genres and should be deleted.
     * Especially important if you use Last.fm, which has user tags like "seen live".
     */
    protected $blocklist = [
        // Personal Tags (Junk)
        'seen live', 'seen', 'concerts', 'gig', 'live',
        'favorites', 'favourite', 'faves', 'fav', 'my favorites',
        'owned', 'vinyl', 'cd', 'albums i own', 'my library',
        'beautiful', 'awesome', 'love', 'amazing', 'cool',
        'sexy', 'mellow', 'chill', 'fun', 'sad', //
        // Metadata Tags (Not Genres)
        'female vocalists', 'male vocalists', 'female vocalist', 
        'singer-songwriter', // This is technically a descriptor, not a sound. Up to you.
        'composer', 'piano', 'guitar', 'instrumental',
        'cover', 'remix', 'soundtrack', 'ost', // Unless you want an OST category
        'under 2000 listeners', 'underrated',
        'spotify', 'all', 'other'
    ];
 
    /**
     * Map messy variations to ONE standard niche format.
     * Key = The messy version
     * Value = The clean, standard version
     */
    /**
     * Map messy variations to ONE standard niche format.
     * Key = The messy version
     * Value = The clean, standard version
     */
    protected $aliases = [
        // User requested to clear manual rules.
        // Add new overrides here if needed.
    ];

    protected $beetsWhitelist = [];

    public function __construct() {
        $this->loadWhitelists();
    }

    protected function loadWhitelists()
    {
        // Load the Beets genres.txt file if it exists, cache it forever effectively (static list)
        $this->beetsWhitelist = \Illuminate\Support\Facades\Cache::remember('beets_genres_whitelist', 60 * 60 * 24 * 30, function () {
            $map = [];
            
            // 1. Load Beets List (Base)
            $beetsPath = base_path('genres.txt');
            if (file_exists($beetsPath)) {
                $lines = file($beetsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = explode('#', $line)[0];
                    $line = trim($line);
                    if ($line) {
                        $map[strtolower($line)] = $line;
                    }
                }
            }

            // 2. Load MusicBrainz Official List (Enhanced)
            // This is updated via updateMusicBrainzWhitelist()
            $mbPath = storage_path('app/genres/musicbrainz_genres.json');
            if (file_exists($mbPath)) {
                $mbGenres = json_decode(file_get_contents($mbPath), true);
                if (is_array($mbGenres)) {
                    foreach ($mbGenres as $g) {
                        $map[strtolower($g)] = $g; // Overwrite/Add
                    }
                }
            }
            
            return $map;
        });
    }

    /**
     * Fetch the official genre whitelist from MusicBrainz API.
     * (Run this once a month or save to a JSON file)
     */
    public function updateMusicBrainzWhitelist()
    {
        $allGenres = [];
        $offset = 0;
        $limit = 100; // Max allowed by MusicBrainz

        do {
            try {
                // Fetch valid genres from MusicBrainz
                $response = Http::withHeaders([
                    'User-Agent' => env('MUSICBRAINZ_USER_AGENT', 'ResoFYP/1.0')
                ])->get('https://musicbrainz.org/ws/2/genre/all', [
                    'limit' => $limit,
                    'offset' => $offset,
                    'fmt' => 'json'
                ]);

                if ($response->failed()) break;

                $data = $response->json();
                $genres = $data['genres'] ?? [];

                foreach ($genres as $g) {
                    $allGenres[] = strtolower($g['name']);
                }

                $offset += $limit;
                // Be polite to their API (1 request per second rule)
                sleep(1); 

            } catch (\Exception $e) {
                break;
            }
        } while (count($genres) == $limit);

        // Save to file so you don't spam their API
        File::put(storage_path('app/genres/musicbrainz_genres.json'), json_encode($allGenres));
        
        // Clear cache so new list takes effect immediately
        \Illuminate\Support\Facades\Cache::forget('beets_genres_whitelist');
        
        return $allGenres;
    }

    /**
     * Clean a list of genres.
     * * @param array $genres
     * @return array
     */
    public function clean(array $genres): array
    {
        $cleaned = [];

        foreach ($genres as $genre) {
            // 1. Lowercase & Trim
            $g = strtolower(trim($genre));

            // 2. Check Blocklist (Skip garbage)
            if (in_array($g, $this->blocklist)) {
                continue;
            }

            // 3. Apply Alias Mapping (Fix typos - User Manual Overrides)
            if (isset($this->aliases[$g])) {
                $g = $this->aliases[$g];
            }

            // 4. (NEW) Beets Whitelist Normalization
            // If the genre exists in the beets list, use that canonical version
            // This is useful if the beets list has specific casing or standard variants
            // Note: genres.txt provided is lowercase, so this acts mainly as a validator/pass-through
            if (isset($this->beetsWhitelist[$g])) {
               $g = $this->beetsWhitelist[$g];
            }

            // Remove tags that are too short to be real genres (e.g. "pop" is 3, "rb" is 2)
            // Exception: "idm" or "ost" if you keep them.
            if (strlen($g) < 2) {
                continue;
            }

            // Regex to filter out dates (YYYY-MM-DD) or just Years (YYYY)
            // (Note: Decades like 1980s are preserved by being in the alias list or not matching this strict digit regex)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $g) || preg_match('/^\d{4}$/', $g)) {
                continue;
            }

            // 4. Formatting rules (Optional)
            // Replace spaces with hyphens for specific multi-word genres if you prefer
            // $g = str_replace(' ', '-', $g); 

            $cleaned[] = $g;
        }

        // 5. Remove Duplicates (e.g., 'kpop' became 'k-pop', now we have two 'k-pop')
        $unique = array_unique($cleaned);

        // 6. Re-index array (0, 1, 2...)
        return array_values($unique);
    }
}
