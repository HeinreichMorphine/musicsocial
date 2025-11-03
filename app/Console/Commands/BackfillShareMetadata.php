<?php

namespace App\Console\Commands;

use App\Models\Share;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use App\Services\MusicBrainzService; // Add this line
use Illuminate\Console\Command;

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
    protected $description = 'Iterate through all existing shares and fetch any missing Spotify audio features or YouTube tags';

    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService, YouTubeService $youTubeService, MusicBrainzService $musicBrainzService)
    {
        $this->info('Starting to backfill share metadata with detailed status...');

        $sharesToUpdate = Share::where('type', 'music')
                               ->where(function ($query) {
                                   $query->whereNull('genres')
                                         ->orWhere('genres', '[]');
                               })->get();

        if ($sharesToUpdate->isEmpty()) {
            $this->info('All music shares already have genre data. No backfill needed.');
            return;
        }

        $this->info("Found {$sharesToUpdate->count()} music shares that need genre backfilling.");
        $updatedCount = 0;

        $bar = $this->output->createProgressBar($sharesToUpdate->count());
        $bar->start();

        foreach ($sharesToUpdate as $share) {
            $this->line("\nProcessing Share ID: {$share->id} (Track: {$share->track_name})");
            $currentGenres = []; // Collect all genres here

            // 1. Try Spotify
            if ($share->spotify_track_id) {
                try {
                    $trackData = $spotifyService->getTrack($share->spotify_track_id);
                    if (isset($trackData['error'])) {
                        $this->error("  - Spotify FAILED: " . $trackData['error']);
                    } elseif (!empty($trackData['genres'])) {
                        $currentGenres = array_merge($currentGenres, $trackData['genres']);
                        $this->info("  - SUCCESS (Spotify): Found genres.");
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (Spotify): An exception occurred: " . $e->getMessage());
                }
            }

            // 2. Try MusicBrainz
            if ($share->artist_name) {
                try {
                    $mbGenres = $musicBrainzService->getArtistGenres($share->artist_name);
                    if (isset($mbGenres['error'])) {
                        $this->error("  - MusicBrainz FAILED: " . $mbGenres['error']);
                    } elseif (!empty($mbGenres)) {
                        $currentGenres = array_merge($currentGenres, $mbGenres);
                        $this->info("  - SUCCESS (MusicBrainz): Found genres.");
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (MusicBrainz): An exception occurred: " . $e->getMessage());
                }
            }

            // Deduplicate and clean genres
            $currentGenres = array_unique(array_filter($currentGenres));

            // 3. YouTube Fallback (only if no genres found yet)
            if (empty($currentGenres) && $share->youtube_video_id) {
                $this->comment("  - INFO: No genres from Spotify/MusicBrainz. Trying YouTube fallback...");
                try {
                    $videoData = $youTubeService->getVideo($share->youtube_video_id);

                    if ($videoData && !empty($videoData['tags'])) {
                        $genreKeywords = ['pop', 'rock', 'hip hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop'];
                        $foundYouTubeGenres = [];
                        foreach ($videoData['tags'] as $tag) {
                            foreach ($genreKeywords as $keyword) {
                                if (stripos($tag, $keyword) !== false) {
                                    $foundYouTubeGenres[] = $keyword;
                                }
                            }
                        }
                        $foundYouTubeGenres = array_unique($foundYouTubeGenres);

                        if (!empty($foundYouTubeGenres)) {
                            $currentGenres = array_merge($currentGenres, $foundYouTubeGenres);
                            $this->info("  - SUCCESS (YouTube): Found genres.");
                        } else {
                            $this->warn("  - FAILED (YouTube): Found tags, but no relevant genre keywords matched.");
                        }
                    } else {
                        $this->warn("  - FAILED (YouTube): Could not fetch video tags from YouTube for video ID {$share->youtube_video_id}.");
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (YouTube): An exception occurred: " . $e->getMessage());
                }
            }

            // Save if genres were found
            if (!empty($currentGenres)) {
                $oldGenres = $share->genres ?? 'NULL';
                $newGenres = json_encode(array_values($currentGenres)); // Re-index array for JSON
                $share->genres = $newGenres;
                $share->save();
                $this->info("  - FINAL SUCCESS: Updated genres from {$oldGenres} to {$newGenres}");
                $updatedCount++;
            } else {
                $this->warn("  - FINAL FAILED: No genres found from any source for Share ID: {$share->id}.");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nFinished backfilling share metadata.");
        $this->info("Summary: {$updatedCount} out of {$sharesToUpdate->count()} shares were updated.");
    }
}
