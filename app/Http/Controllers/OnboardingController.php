<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        // Fetch 4 diverse editor's picks (Pop, Rock, Jazz, Afrobeats) to prevent popularity bias, cached for 24 hours
        $suggestedTracks = Cache::remember('onboarding_diverse_suggested_tracks', 60 * 60 * 24, function () {
            try {
                $ids = [
                    '4D7t7g2jsYii9v173y506G', // Pop: Harry Styles - As It Was
                    '5uCaxm20t3865UpVJb0GgC', // Rock: Nirvana - Smells Like Teen Spirit
                    '7y620WfXhU1g0Z42L6zG2k', // Jazz: Frank Sinatra - Fly Me To The Moon
                    '5GDAWNs8t162gJV61PWqyW', // Afrobeats: Burna Boy - Last Last
                ];
                $tracks = [];
                foreach ($ids as $id) {
                    $track = $this->spotifyService->getRawTrack($id);
                    if ($track && !isset($track['error'])) {
                        $tracks[] = $track;
                    }
                }
                return $tracks;
            } catch (\Exception $e) {
                \Log::warning('Onboarding: Could not fetch suggested diverse tracks — ' . $e->getMessage());
                return [];
            }
        });

        return view('onboarding.genres', compact('suggestedTracks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_ids' => 'required|array|min:5|max:10',
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
                'song_id' => $spotifyId, // Spotify ID (String)
                'position' => $index,
            ]);

            // Record as a song interaction for Discovery page exclusion filtering.
            // The recommender's SVD training weight (4.0 pts) comes from the
            // user_shelf_songs table directly, NOT from this entry.
            SongInteraction::updateOrCreate(
                ['user_id' => $user->id, 'song_id' => $song->id],
                ['type' => 'like', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $user->update(['is_onboarded' => true]);

        return response()->json(['message' => 'Shelf curated successfully.', 'redirect' => route('dashboard', ['feed' => 'explore'])]);
    }

    /**
     * Skip has been removed — shelf curation is mandatory.
     * Every user must select at least 5 songs to ensure the recommendation
     * engine has enough data for personalized TF-IDF profiling (TC-07).
     */
}
