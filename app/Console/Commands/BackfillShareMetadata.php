<?php

namespace App\Console\Commands;

use App\Models\Share;
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
    public function handle(SpotifyService $spotifyService, YouTubeService $youTubeService)
    {
        $this->info('Starting to backfill share metadata...');

        $shares = Share::all();

        foreach ($shares as $share) {
            if ($share->type === 'music' && $share->spotify_track_id) {
                try {
                    $data = $spotifyService->getTrack($share->spotify_track_id);
                    if ($data && $data['track']) {
                        $track = $data['track'];
                        $artist = $data['artist'];

                        $updateData = [];

                        if (!$share->spotify_audio_features && isset($track['audio_features'])) {
                            $updateData['spotify_audio_features'] = $track['audio_features'];
                        }

                        if (!$share->genres && $artist && isset($artist['genres'])) {
                            $updateData['genres'] = json_encode($artist['genres']);
                        }

                        if (!empty($updateData)) {
                            $share->update($updateData);
                            $this->info("Updated metadata for share #{$share->id}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to update metadata for share #{$share->id}: {$e->getMessage()}");
                }
            }

            if ($share->type === 'youtube' && $share->youtube_video_id && !$share->youtube_tags) {
                try {
                    $video = $youTubeService->getVideo($share->youtube_video_id);
                    if ($video) {
                        $share->update(['youtube_tags' => $video['tags']]);
                        $this->info("Updated YouTube tags for share #{$share->id}");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to update YouTube tags for share #{$share->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Finished backfilling share metadata.');
    }
}
