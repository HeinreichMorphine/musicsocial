<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShelfSong;
use App\Models\SongInteraction;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;

class UserShelfController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    /**
     * Add a song to the user's shelf.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|string',
        ]);

        $user = Auth::user();

        if ($user->shelfSongs()->count() >= 10) {
            return response()->json(['error' => 'Your shelf is full (max 10 songs).'], 422);
        }

        if ($user->shelfSongs()->where('song_id', $validated['song_id'])->exists()) {
            return response()->json(['error' => 'Song is already on your shelf.'], 422);
        }

        $trackData = $this->spotifyService->getRawTrack($validated['song_id']);
        if (!$trackData) {
            return response()->json(['error' => 'Invalid track or Spotify error.'], 422);
        }

        UserShelfSong::create([
            'user_id' => $user->id,
            'song_id' => $validated['song_id'],
            'position' => $user->shelfSongs()->count(),
        ]);

        // Track as a like for recommendation engine
        // We still need the internal Song model for interactions
        $internalTrack = $this->spotifyService->getTrack($validated['song_id']);
        if (isset($internalTrack['song'])) {
            SongInteraction::updateOrCreate(
                ['user_id' => $user->id, 'song_id' => $internalTrack['song']->id, 'type' => 'like'],
                ['updated_at' => now()]
            );
        }

        return response()->json([
            'message' => 'Song added to your shelf.',
            'track' => $trackData
        ]);
    }

    /**
     * Remove a song from the user's shelf.
     */
    public function remove(Request $request, $songId)
    {
        $user = Auth::user();
        $shelfSong = $user->shelfSongs()->where('song_id', $songId)->first();

        if (!$shelfSong) {
            return response()->json(['error' => 'Song not found on your shelf.'], 404);
        }

        $shelfSong->delete();

        // Normalize positions
        $user->shelfSongs()->orderBy('position')->get()->each(function ($item, $index) {
            $item->update(['position' => $index]);
        });

        return response()->json(['message' => 'Song removed from shelf.']);
    }

    /**
     * Reorder songs on the shelf.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'song_ids' => 'required|array',
            'song_ids.*' => 'string'
        ]);

        $user = Auth::user();
        
        foreach ($validated['song_ids'] as $index => $songId) {
            $user->shelfSongs()->where('song_id', $songId)->update(['position' => $index]);
        }

        return response()->json(['message' => 'Shelf order updated.']);
    }
}
