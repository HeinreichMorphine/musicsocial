<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Song;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Cache;

class RefetchEmptyGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refetch-empty-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto detect songs with empty genres and refetch them from Spotify, MusicBrainz, and Discogs';

    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService)
    {
        $songs = Song::whereNull('genres')
            ->orWhere('genres', '')
            ->orWhere('genres', '[]')
            ->orWhere('genres', '{}')
            ->get();

        if ($songs->isEmpty()) {
            $this->info('No songs with empty genres found.');
            return 0;
        }

        $this->info("Found {$songs->count()} songs with empty genres. Starting refetch...");

        $successCount = 0;
        $failedCount = 0;

        foreach ($songs as $song) {
            if (!$song->spotify_track_id) {
                $this->warn("Skipping song ID {$song->id} ('{$song->track_name}'): Missing Spotify Track ID.");
                $failedCount++;
                continue;
            }

            $this->info("Refetching genres for song ID {$song->id} ('{$song->track_name}' by '{$song->artist_name}')...");

            // Flush cache
            Cache::forget("genres_track_v2_{$song->spotify_track_id}");

            try {
                $genreData = $spotifyService->getGenresWithSources($song->spotify_track_id);

                if (!empty($genreData['genres'])) {
                    $song->update([
                        'genres' => json_encode(array_values(array_unique($genreData['genres'])))
                    ]);
                    $this->info("  Success! Genres: " . implode(', ', $genreData['genres']));
                    $successCount++;
                } else {
                    $this->warn("  No genres found.");
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $this->error("  Error: " . $e->getMessage());
                $failedCount++;
            }

            // Sleep briefly to respect API rate limits
            usleep(200000); // 200ms
        }

        $this->info("Completed! Success: {$successCount}, Failed/Skipped: {$failedCount}");
        return 0;
    }
}
