<?php

namespace App\Services;

use App\Models\Song;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class YouTubeService
{
    protected $baseUrl = 'https://www.googleapis.com/youtube/v3/';
    protected $apiKey;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->apiKey = config('services.youtube.key');
    }

    /**
     * Search for a single embeddable video and return its details.
     *
     * @param string $query
     * @return array|null
     */
    public function searchVideo(string $query)
    {
        $cacheKey = 'yt_search_' . md5($query);

        // 1. Check cache manually first to avoid caching failures
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 2. Fetch results with native embeddable filtering
        $response = Http::timeout(30)->get($this->baseUrl . 'search', [
            'key' => $this->apiKey,
            'part' => 'snippet',
            'q' => $query . ' official',
            'type' => 'video',
            'videoEmbeddable' => 'true', // Native filter saves us a second API call!
            'maxResults' => 1, // We only need the top result now
        ]);

        // 3. Handle Quota/API Errors (Do NOT cache these so we can retry after reset)
        if ($response->failed()) {
            $error = $response->json('error.message') ?? 'Unknown error';
            if (str_contains(strtolower($error), 'quota')) {
                \Log::error('YouTube API Quota Exceeded! Searches will fail until reset.');
            } else {
                \Log::warning('YouTube Search API failed: ' . $error);
            }
            return null; 
        }

        // 4. Handle "Legitimately No Results" (Safe to cache so we don't spam the API)
        if (empty($response->json('items'))) {
            \Log::info('YouTube Search found 0 items for query: ' . $query);
            Cache::put($cacheKey, null, now()->addDay());
            return null;
        }

        // 5. Process Successful Result
        $videoId = $response->json('items')[0]['id']['videoId'];
        
        $result = [
            'video_id' => $videoId,
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
        ];

        // Cache the successful result for 24 hours
        Cache::put($cacheKey, $result, now()->addDay());

        return $result;
    }

    /**
     * Get details for a specific video, including tags.
     *
     * @param string $videoId
     * @return array|null
     */
    public function getVideo(string $videoId)
    {
        $response = Http::timeout(30)->get($this->baseUrl . 'videos', [
            'key' => $this->apiKey,
            'part' => 'snippet',
            'id' => $videoId,
        ]);

        if ($response->failed() || empty($response->json('items'))) {
            return null;
        }

        $item = $response->json('items')[0];

        return [
            'title' => $item['snippet']['title'] ?? null,
            'description' => $item['snippet']['description'] ?? null,
            'tags' => $item['snippet']['tags'] ?? [],
        ];
    }
}