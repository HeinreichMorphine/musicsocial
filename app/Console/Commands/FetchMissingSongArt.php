<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\SpotifyService;
use Illuminate\Console\Command;

class FetchMissingSongArt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-song-art {--force : Force update all songs} {--limit= : Limit the number of songs to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches missing album art for songs in the database using the Spotify API';

    /**
     * Execute the console command.
     */
    public function handle(SpotifyService $spotifyService)
    {
        $query = Song::query();

        if (!$this->option('force')) {
            $query->whereNull('album_art_url')
                  ->orWhere('album_art_url', '')
                  ->orWhere('album_art_url', 'like', '%reso.png%');
        }

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $songs = $query->get();

        if ($songs->isEmpty()) {
            $this->info('No songs found with missing album art.');
            return;
        }

        $this->info("Found {$songs->count()} songs to process.");

        $bar = $this->output->createProgressBar($songs->count());
        $bar->start();

        foreach ($songs as $song) {
            if (!$song->spotify_track_id) {
                $bar->advance();
                continue;
            }

            try {
                $trackData = $spotifyService->getRawTrack($song->spotify_track_id);

                if ($trackData && isset($trackData['album']['images'][0]['url'])) {
                    $artUrl = $trackData['album']['images'][0]['url'];
                    $song->update(['album_art_url' => $artUrl]);
                } else {
                    $this->warn("\nCould not find art for track: {$song->track_name} by {$song->artist_name}");
                }
            } catch (\Exception $e) {
                $this->error("\nError processing track {$song->spotify_track_id}: " . $e->getMessage());
            }

            $bar->advance();
            
            // Avoid rate limits
            usleep(100000); // 100ms
        }

        $bar->finish();
        $this->newLine();
        $this->info('Finished processing songs.');
    }
}
