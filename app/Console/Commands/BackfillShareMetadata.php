<?php
namespace App\Console\Commands;

use App\Models\Song;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use Illuminate\Console\Command;


class BackfillShareMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-genres {--force} {--track=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate through all existing songs and fetch any missing metadata.';

    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService, YouTubeService $youTubeService)
    {
        $this->info('Starting to backfill song metadata...');

        $query = Song::query();

        if ($this->option('track')) {
            $query->where('track_name', 'like', '%' . $this->option('track') . '%');
        }

        if (!$this->option('force') && !$this->option('track')) {
             $query->where(function ($q) {
                 $q->whereNull('genres')->orWhere('genres', '[]')->orWhere('genres', '');
             });
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

            // 1. Fetch from Spotify Service (includes Spotify, MusicBrainz, Discogs with caching)
            if ($song->spotify_track_id) {
                try {
                    // Use new method to get sources
                    $responseData = $spotifyService->getGenresWithSources($song->spotify_track_id);
                    $fetchedGenres = $responseData['genres'] ?? [];
                    $sources = $responseData['sources'] ?? [];

                    if (!empty($fetchedGenres) || !empty($sources)) {
                        if (!empty($fetchedGenres)) {
                            $genres = array_unique(array_merge($genres, $fetchedGenres));
                        }

                        // Display detailed source info
                        $this->info("  Includes genre tags for '{$song->track_name}':");
                        foreach ($sources as $source => $tags) {
                            if (!empty($tags)) {
                                 // Clean formatting for nicer output
                                 $prettySource = ucwords(str_replace('_', ' ', $source));
                                 $this->line("    <comment>{$prettySource}</comment>: " . implode(', ', $tags));
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log::error("Backfill Error for {$song->id}: " . $e->getMessage());
                }
            }

            // 2. YouTube as fallback (if genres are still empty or for enrichment)
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
            // Mainstream Genres
            'pop', 'rock', 'hip hop', 'hip-hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal',
            'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop', 'afrobeat', 'blues', 'disco', 'gospel',
            'house', 'techno', 'trance', 'trap', 'world', 'lo-fi', 'lofi', 'chill', 'bedroom pop',

            // Niche Genres
            'synthwave', 'new wave', 'punk', 'folk', 'ambient', 'acoustic', 'shoegaze', 'dream pop', 'post-rock', 'math rock',
            'midwest emo', 'screamo', 'hardcore', 'metalcore', 'death metal', 'black metal', 'doom metal', 'stoner rock',
            'psychedelic rock', 'garage rock', 'surf rock', 'jangle pop', 'power pop', 'noise pop', 'twee pop', 'chamber pop',
            'art pop', 'hyperpop', 'glitchcore', 'bubblegum bass', 'deconstructed club', 'future bass', 'vaporwave', 'seapunk',
            'witch house', 'darkwave', 'coldwave', 'ethereal wave', 'gothic rock', 'industrial', 'ebm', 'aggrotech',
            'futurepop', 'synth-pop', 'electropop', 'electro-industrial', 'idm', 'drill and bass', 'glitch', 'breakcore',
            'jungle', 'drum and bass', 'dubstep', 'grime', 'uk garage', '2-step', 'footwork', 'juke', 'chicago house',
            'acid house', 'deep house', 'progressive house', 'electro house', 'big room', 'hardstyle', 'jumpstyle',
            'gabba', 'hardcore techno', 'speedcore', 'terrorcore', 'frenchcore', 'uptempo hardcore'
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