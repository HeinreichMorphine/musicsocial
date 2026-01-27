<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpotifyPlaylistController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Get the current user's playlists (JSON).
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $playlists = $this->spotifyService->getUserPlaylists($user);

        return response()->json($playlists);
    }

    /**
     * Add a track to a playlist.
     */
    public function addTrack(Request $request)
    {
        $request->validate([
            'playlist_id' => 'required|string',
            'track_uri' => 'required|string', // spotify:track:xxxx
        ]);

        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $success = $this->spotifyService->addTrackToPlaylist(
            $user, 
            $request->playlist_id, 
            $request->track_uri
        );

        if ($success) {
            return response()->json(['message' => 'Track added to playlist!']);
        }

        return response()->json(['error' => 'Failed to add track to playlist.'], 500);
    }

    /**
     * Create a new playlist.
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $result = $this->spotifyService->createPlaylist($user, $request->name);

        if ($result) {
            return response()->json($result);
        }

        return response()->json(['error' => 'Failed to create playlist.'], 500);
    }
}
