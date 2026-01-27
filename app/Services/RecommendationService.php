<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    protected $pythonServiceUrl;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->pythonServiceUrl = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000');
    }

    /**
     * Get recommendations for a user from the Python recommender service.
     *
     * @param int $userId
     * @return array
     */
    public function getRecommendations(int $userId): array
    {
        try {
            $url = $this->pythonServiceUrl . '/recommendations/' . $userId;
            Log::info("RecommendationService: Requesting recommendations from: " . $url);

            // Retry up to 3 times, waiting 1000ms (1s) between attempts.
            // Set a timeout of 5 seconds per attempt.
            $response = Http::timeout(5)->retry(3, 1000)->get($url);

            if ($response->successful()) {
                $recommendations = $response->json();
                Log::info("RecommendationService: Received " . count($recommendations['recommendations'] ?? []) . " recommendations.");
                // Return the full array of recommendation objects, including share_id, score, and reason.
                return $recommendations['recommendations'] ?? [];
            } else {
                Log::error('Python recommender service error: ' . $response->status() . ' - ' . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Error calling Python recommender service: ' . $e->getMessage());
            return [];
        }
    }
}
