<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShelfSong;
use App\Models\SongInteraction;
use App\Services\SpotifyService;

class OnboardingController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function genres()
    {
        return view('onboarding.genres');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_ids' => 'required|array|min:3|max:10',
            'song_ids.*' => 'string'
        ]);

        $user = $request->user();

        // Clear existing shelf
        $user->shelfSongs()->delete();

        foreach ($validated['song_ids'] as $index => $spotifyId) {
            // Fetch/Create the song in local DB
            $trackData = $this->spotifyService->getTrack($spotifyId);
            
            if (isset($trackData['error']) || !isset($trackData['song'])) {
                continue; // Skip if invalid
            }
            
            $song = $trackData['song'];

            // Save to the Shelf
            UserShelfSong::create([
                'user_id' => $user->id,
                'song_id' => $spotifyId, // Spotify ID
                'position' => $index,
            ]);

            // Save as a "like" for the ML model mapping (TC-02 & TC-05)
            SongInteraction::updateOrCreate(
                ['user_id' => $user->id, 'song_id' => $song->id],
                ['type' => 'like', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $user->update(['is_onboarded' => true]);

        return response()->json(['message' => 'Shelf curated successfully.', 'redirect' => route('dashboard', ['feed' => 'explore'])]);
    }

    // Skip logic removed to enforce TC-08 (Shelf Cold Start Resolution)
}
