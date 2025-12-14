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
    protected $signature = 'app:backfill-genres {--force}';

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

        $query = Song::query();

        if (!$this->option('force')) {
             $query->whereNull('genres')->orWhere('genres', '[]')->orWhere('genres', '');
        }

        $songsToUpdate = $query->get();

        if ($songsToUpdate->isEmpty()) {
            $this->info('All songs already have complete metadata. Use --force to re-process all.');
            return;
        }

        $this->info("Found {$songsToUpdate->count()} songs to process.");
        $updatedCount = 0;

        $bar = $this->output->createProgressBar($songsToUpdate->count());
        $bar->start();

        foreach ($songsToUpdate as $song) {
            // $this->line("\nProcessing Song ID: {$song->id} (Title: {$song->track_name})"); // Verbose

            $genres = json_decode($song->genres, true) ?? [];
            if (!is_array($genres)) $genres = [];

            // 1. AudioDB
            $audioDbGenres = $audioDbService->getGenres($song->track_name, $song->artist_name);
            if (!empty($audioDbGenres)) {
                $genres = array_unique(array_merge($genres, $audioDbGenres));
            }

            // 2. MusicBrainz
            if ($song->artist_name) {
                try {
                    $mbGenres = $musicBrainzService->getArtistGenres($song->artist_name);
                    if (!empty($mbGenres) && !isset($mbGenres['error'])) {
                        $genres = array_unique(array_merge($genres, $mbGenres));
                    }
                } catch (\Exception $e) {
                    // Silent fail
                }
            }

            // 3. Spotify
            if ($song->spotify_track_id) {
                $spotifyGenres = $spotifyService->getGenresForTrack($song->spotify_track_id);
                if (!empty($spotifyGenres)) {
                    $genres = array_unique(array_merge($genres, $spotifyGenres));
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
                        }
                    }
                } catch (\Exception $e) {
                   // Silent fail
                }
            }

            $song->update(['genres' => json_encode(array_values(array_unique($genres)))]);
            if (!empty($genres)) {
                 $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nFinished backfilling song metadata.");
        $this->info("Summary: {$updatedCount} songs updated or verified.");
    }

    private function extractGenresFromText(string $text): array
    {
        $genreKeywords = [
            'pop', 'rock', 'hip hop', 'hip-hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 
            'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop', 'afrobeat', 'blues', 'disco', 'gospel', 
            'house', 'techno', 'trance', 'trap', 'world', 'lo-fi', 'lofi', 'chill', 'bedroom pop', 'dream pop', 'shoegaze',
            'synthwave', 'new wave', 'punk', 'folk', 'ambient', 'acoustic'
        ];
        
        $foundGenres = [];
        foreach ($genreKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $foundGenres[] = $keyword;
            }
        }
        return $foundGenres;
    }
}