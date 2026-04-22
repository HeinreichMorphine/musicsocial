<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request;

class SpotifySearchController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Search for tracks.
     */
    public function search(Request $request)
    {
        $query = $request->validate(['query' => 'required|string|min:3'])['query'];

        $tracks = $this->spotifyService->searchTracks($query);

        return response()->json($tracks);
    }
    /**
     * Get user's recently played tracks.
     */
    public function recentlyPlayed(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->spotify_token) {
            return response()->json([]);
        }

        $items = $this->spotifyService->getUserRecentlyPlayed($user);
        
        // Format to match search results structure slightly, or just return as is
        // Spotify returns { track: {...}, played_at: ... }
        // We want to extract just the track info mostly, but keeping structure is fine
        $formatted = array_map(function($item) {
            return $item['track'];
        }, $items);

        return response()->json($formatted);
    }

    /**
     * Get track details by ID.
     */
    public function show($id)
    {
        $trackData = $this->spotifyService->getTrack($id);
        if (isset($trackData['error'])) {
            return response()->json(['error' => $trackData['error']], 400);
        }
        return response()->json($trackData);
    }
}