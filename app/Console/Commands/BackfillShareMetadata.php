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
            $genresFound = false;

            $genresFound = false;

            // 1. Try Spotify First
            if ($share->spotify_track_id) {
                try {
                    $trackData = $spotifyService->getTrack($share->spotify_track_id);

                    if (isset($trackData['error'])) {
                        $this->error("  - Spotify FAILED: " . $trackData['error']);
                    } elseif (!empty($trackData['genres'])) {
                        $oldGenres = $share->genres ?? 'NULL';
                        $newGenres = json_encode($trackData['genres']);
                        $share->genres = $newGenres;
                        $share->save();
                        $this->info("  - SUCCESS (Spotify): Updated genres from {$oldGenres} to {$newGenres}");
                        $updatedCount++;
                        $genresFound = true;
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (Spotify): An exception occurred: " . $e->getMessage());
                }
            }

            // 2. If Spotify fails, try MusicBrainz as a fallback
            if (!$genresFound && $share->artist_name) {
                $this->comment("  - INFO: Spotify failed. Trying MusicBrainz fallback...");
                try {
                    $mbGenres = $musicBrainzService->getArtistGenres($share->artist_name);

                    if (isset($mbGenres['error'])) {
                        $this->error("  - MusicBrainz FAILED: " . $mbGenres['error']);
                    } elseif (!empty($mbGenres)) {
                        $oldGenres = $share->genres ?? 'NULL';
                        $newGenres = json_encode($mbGenres);
                        $share->genres = $newGenres;
                        $share->save();
                        $this->info("  - SUCCESS (MusicBrainz): Updated genres from {$oldGenres} to {$newGenres}");
                        $updatedCount++;
                        $genresFound = true;
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (MusicBrainz): An exception occurred: " . $e->getMessage());
                }
            }

            // 3. If MusicBrainz also fails, try YouTube as a fallback
            if (!$genresFound && $share->youtube_video_id) {
                $this->comment("  - INFO: MusicBrainz failed. Trying YouTube fallback...");
                try {
                    $videoData = $youTubeService->getVideo($share->youtube_video_id);

                    if ($videoData && !empty($videoData['tags'])) {
                        $genreKeywords = ['pop', 'rock', 'hip hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop'];
                        $foundGenres = [];
                        foreach ($videoData['tags'] as $tag) {
                            foreach ($genreKeywords as $keyword) {
                                if (stripos($tag, $keyword) !== false) {
                                    $foundGenres[] = $keyword;
                                }
                            }
                        }
                        $foundGenres = array_unique($foundGenres);

                        if (!empty($foundGenres)) {
                            $oldGenres = $share->genres ?? 'NULL';
                            $newGenres = json_encode($foundGenres);
                            $share->genres = $newGenres;
                            $share->save();
                            $this->info("  - SUCCESS (YouTube): Updated genres from {$oldGenres} to {$newGenres}");
                            $updatedCount++;
                        } else {
                            $this->warn("  - FAILED (YouTube): Found tags, but no relevant genre keywords matched.");
                        }
                    } else {
                        $this->warn("  - FAILED (YouTube): Could not fetch video tags from YouTube for video ID {$share->youtube_video_id}.");
                    }
                } catch (\Exception $e) {
                    $this->error("  - ERROR (YouTube): An exception occurred: " . $e->getMessage());
                }
            } elseif (!$genresFound) {
                $this->warn("  - FAILED: No genres found from any source.");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nFinished backfilling share metadata.");
        $this->info("Summary: {$updatedCount} out of {$sharesToUpdate->count()} shares were updated.");
    }
}
