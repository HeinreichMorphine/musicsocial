<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class CheckSiteStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:check {url=https://example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the HTTP status of a given URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $this->info("Attempting to connect to: $url");

        try {
            $response = Http::get($url);
            $status = $response->status();

            if ($response->successful()) {
                $this->info("✅ Success! Status: $status");
            } else {
                $this->warn("⚠️ Finished with non-success status: $status");
            }

        } catch (ConnectionException $e) {
            $this->error("❌ Connection failed: " . $e->getMessage());
            return 1; // Return a non-zero exit code on failure
        } catch (\Exception $e) {
            $this->error("❌ An unexpected error occurred: " . $e->getMessage());
            return 1;
        }

        return 0; // Success
    }
}
