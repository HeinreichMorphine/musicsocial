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
     * Get recommendations for a user from the Python recommender service,
     * or fall back to database recommendations if unavailable.
     *
     * @param int $userId
     * @return array
     */
    public function getRecommendations(int $userId): array
    {
        try {
            $url = $this->pythonServiceUrl . '/recommendations/' . $userId;
            Log::info("RecommendationService: Requesting recommendations from: " . $url);

            // Fast timeout: 1s connect, 2s response timeout, 1 quick retry after 200ms
            $response = Http::connectTimeout(1)->timeout(2)->retry(1, 200)->get($url);

            if ($response->successful()) {
                $recommendations = $response->json();
                $recs = $recommendations['recommendations'] ?? [];
                if (!empty($recs)) {
                    Log::info("RecommendationService: Received " . count($recs) . " recommendations from Python service.");
                    return $recs;
                }
            } else {
                Log::error('Python recommender service error: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Python recommender service unreachable: ' . $e->getMessage() . '. Using DB fallback recommendations.');
        }

        return $this->getDatabaseFallbackRecommendations($userId);
    }

    /**
     * Fallback database recommendation engine when Python service is unavailable or returns empty.
     *
     * @param int $userId
     * @return array
     */
    public function getDatabaseFallbackRecommendations(int $userId): array
    {
        Log::info("RecommendationService: Generating DB fallback recommendations for user {$userId}");

        try {
            // Get interacted song IDs to exclude
            $interactedSongIds = \App\Models\SongInteraction::where('user_id', $userId)
                ->pluck('song_id')
                ->toArray();

            // Fetch candidate songs from DB excluding interacted ones
            $candidateSongs = \App\Models\Song::whereNotIn('id', $interactedSongIds)
                ->inRandomOrder()
                ->limit(60)
                ->get();

            if ($candidateSongs->isEmpty()) {
                $candidateSongs = \App\Models\Song::inRandomOrder()->limit(60)->get();
            }

            $recommendations = [];
            $reasons = [
                'Matches your overall musical taste profile',
                'Personalized sound match for listeners like you',
                'Top pick based on artist similarity',
                'Fits your favorite genre and style vibe',
            ];

            foreach ($candidateSongs as $index => $song) {
                $reason = $reasons[$index % count($reasons)];
                $score = round(3.5 - ($index * 0.04), 2);
                if ($score < 1.0) {
                    $score = 1.0;
                }

                $recommendations[] = [
                    'song_id' => (int) $song->id,
                    'score' => (float) $score,
                    'reason' => $reason,
                    'social_boost' => 0.0,
                    'debug' => [
                        'source' => 'db_fallback'
                    ]
                ];
            }

            return $recommendations;
        } catch (\Exception $e) {
            Log::error('Error generating DB fallback recommendations: ' . $e->getMessage());
            return [];
        }
    }
}
