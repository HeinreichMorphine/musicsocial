<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\YouTubeService;
use Illuminate\Console\Command;

class RefetchYouTubeLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refetch-youtube {--force : Re-fetch even if ID exists} {--track= : Search for a specific track}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-fetch YouTube video IDs for songs to ensure they are embeddable and high quality.';

    /**
     * Execute the console command.
     */
    public function handle(YouTubeService $youTubeService)
    {
        $this->info('Starting YouTube link refresh...');

        $query = Song::query();

        if ($this->option('track')) {
            $query->where('track_name', 'like', '%' . $this->option('track') . '%');
        }

        $songs = $query->get();

        if ($songs->isEmpty()) {
            $this->info('No songs found to process.');
            return;
        }

        $this->info("Found {$songs->count()} songs. This might take a while due to API rate limits.");
        $bar = $this->output->createProgressBar($songs->count());
        $bar->start();

        foreach ($songs as $song) {
            try {
                // Fetch new embeddable video ID
                $videoData = $youTubeService->searchVideo($song->track_name . ' ' . $song->artist_name);
                
                if ($videoData && isset($videoData['video_id'])) {
                    $song->update([
                        'youtube_video_id' => $videoData['video_id'],
                        'youtube_url' => $videoData['url'] ?? ('https://www.youtube.com/watch?v=' . $videoData['video_id'])
                    ]);
                }

                // Small sleep to be respectful to API
                usleep(200000); // 0.2 seconds
            } catch (\Exception $e) {
                $this->error("\nError processing song ID {$song->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nFinished refreshing YouTube links.");
    }
}
