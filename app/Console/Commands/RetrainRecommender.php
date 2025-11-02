<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RetrainRecommender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retrain-recommender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a request to the Python service to retrain the recommendation model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending request to retrain recommender...');

        $url = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000') . '/retrain';

        try {
            $response = Http::post($url);

            if ($response->successful()) {
                $this->info('Recommender retraining request sent successfully.');
                $this->info($response->body());
            } else {
                $this->error('Failed to send recommender retraining request.');
                $this->error($response->body());
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while sending the request:');
            $this->error($e->getMessage());
        }
    }
}
