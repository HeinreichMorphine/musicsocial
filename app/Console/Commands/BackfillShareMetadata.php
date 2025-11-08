<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use App\Services\MusicBrainzService;
use App\Services\AudioDbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BackfillShareMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-share-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate through all existing songs and fetch any missing metadata.';

    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService, YouTubeService $youTubeService, MusicBrainzService $musicBrainzService, AudioDbService $audioDbService)
    {
        $this->info('Starting to backfill song metadata...');

        $songsToUpdate = Song::whereNull('genres')->orWhere('genres', '[]')->get();

        if ($songsToUpdate->isEmpty()) {
            $this->info('All songs already have complete metadata. No backfill needed.');
            return;
        }

        $this->info("Found {$songsToUpdate->count()} songs that need metadata backfilling.");
        $updatedCount = 0;

        $bar = $this->output->createProgressBar($songsToUpdate->count());
        $bar->start();

        foreach ($songsToUpdate as $song) {
            $this->line("\nProcessing Song ID: {$song->id} (Title: {$song->track_name})");

            $genres = json_decode($song->genres, true) ?? [];

            // 1. AudioDB
            $audioDbGenres = $audioDbService->getGenres($song->track_name, $song->artist_name);
            if (!empty($audioDbGenres)) {
                $genres = array_unique(array_merge($genres, $audioDbGenres));
                $this->info("  - SUCCESS (AudioDB): Found and merged genres.");
            }

            // 2. MusicBrainz
            if ($song->artist_name) {
                try {
                    $mbGenres = $musicBrainzService->getArtistGenres($song->artist_name);
                    if (isset($mbGenres['error'])) {
                        $this->error("  - MusicBrainz FAILED: " . $mbGenres['error']);
                    } elseif (!empty($mbGenres)) {
                        $genres = array_unique(array_merge($genres, $mbGenres));
                        $this->info("  - SUCCESS (MusicBrainz): Found and merged genres.");
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (MusicBrainz): An exception occurred: " . $e->getMessage());
                }
            }

            // 3. Spotify
            if ($song->spotify_track_id) {
                $spotifyGenres = $spotifyService->getGenresForTrack($song->spotify_track_id);
                if (!empty($spotifyGenres)) {
                    $genres = array_unique(array_merge($genres, $spotifyGenres));
                    $this->info("  - SUCCESS (Spotify): Found and merged genres.");
                }
            }

            // 4. YouTube as fallback
            if (empty($genres) && $song->youtube_video_id) {
                try {
                    $videoData = $youTubeService->getVideo($song->youtube_video_id);
                    if ($videoData) {
                        $youtubeGenres = $this->extractGenresFromText($videoData['title'] . ' ' . implode(' ', $videoData['tags'] ?? []) . ' ' . $videoData['description']);
                        if (!empty($youtubeGenres)) {
                            $genres = array_unique(array_merge($genres, $youtubeGenres));
                            $this->info("  - SUCCESS (YouTube): Found and merged genres.");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (YouTube): An exception occurred: " . $e->getMessage());
                }
            }

            if (!empty($genres)) {
                $song->update(['genres' => json_encode(array_values(array_unique($genres)))]);
                $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nFinished backfilling song metadata.");
        $this->info("Summary: {$updatedCount} out of {$songsToUpdate->count()} songs were updated.");
    }

    private function extractGenresFromText(string $text): array
    {
        $genreKeywords = ['pop', 'rock', 'hip hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop', 'afrobeat', 'blues', 'disco', 'gospel', 'house', 'techno', 'trance', 'trap', 'world'];
        $foundGenres = [];
        foreach ($genreKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $foundGenres[] = $keyword;
            }
        }
        return $foundGenres;
    }
}