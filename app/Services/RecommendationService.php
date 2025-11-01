<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    protected $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000');
    }

    public function getRecommendations(int $userId): array
    {
        try {
            $response = Http::get($this->pythonServiceUrl . '/recommendations/' . $userId);

            if ($response->successful()) {
                $recommendations = $response->json();
                return array_column($recommendations, 'item_id');
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
