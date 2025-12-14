<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Song;

class FixWaveToEarthGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:wave-to-earth-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually sets genres for artist "wave to earth"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $artistName = 'wave to earth';
        $genres = ["r&b", "soul", "indie", "indie love folk", "folk"];
        
        $this->info("Looking for songs by: $artistName");

        $songs = Song::where('artist_name', 'LIKE', "%{$artistName}%")->get();

        if ($songs->isEmpty()) {
            $this->error("No songs found for artist: $artistName");
            return;
        }

        $this->info("Found {$songs->count()} songs. Updating genres...");

        foreach ($songs as $song) {
            // We are overwriting existing genres as requested, or we could merge. 
            // User said "add tehese gerne", but given they are currently blank/empty, overwriting is safer/cleaner.
            $song->update([
                'genres' => json_encode($genres)
            ]);
            $this->line("Updated: {$song->track_name}");
        }

        $this->info("Done! specific genres applied to wave to earth.");
    }
}
