<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    protected $baseUrl = 'https://www.googleapis.com/youtube/v3/';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.key');
    }

    /**
     * Search for a single video and return its details.
     */
    public function searchVideo(string $query)
    {
        $response = Http::timeout(30)->get($this->baseUrl . 'search', [
            'key' => $this->apiKey,
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => 1,
        ]);

        if ($response->failed() || empty($response->json('items'))) {
            return null;
        }

        $item = $response->json('items')[0];

        return [
            'video_id' => $item['id']['videoId'],
            'url' => 'https://www.youtube.com/watch?v=' . $item['id']['videoId'],
        ];
    }

    /**
     * Get details for a specific video, including tags.
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
            'tags' => $item['snippet']['tags'] ?? [],
        ];
    }
}