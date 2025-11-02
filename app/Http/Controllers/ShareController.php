<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use App\Services\MusicBrainzService; // <-- [NEW] Import MusicBrainzService
use Illuminate\Http\Request;

class ShareController extends Controller
{
    protected $spotifyService;
    protected $youTubeService;
    protected $musicBrainzService; // <-- [NEW] Add MusicBrainzService property

    public function __construct(SpotifyService $spotifyService, YouTubeService $youTubeService, MusicBrainzService $musicBrainzService)
    {
        $this->spotifyService = $spotifyService;
        $this->youTubeService = $youTubeService;
        $this->musicBrainzService = $musicBrainzService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:music,text',
            'caption' => 'nullable|string|max:1000',
            'spotify_track_id' => 'nullable|string|max:255',
        ]);

        $dataToSave = [
            'caption' => $validated['caption'],
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'music') {
            // Ensure spotify_track_id is present for music shares
            if (empty($validated['spotify_track_id'])) {
                return back()->withErrors(['spotify_track_id' => 'Spotify track ID is required for music shares.']);
            }

            // Fetch track data from Spotify
            $trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);

            if (!$trackData || !$trackData['track']) {
                return back()->with('error', 'Could not find track on Spotify.');
            }

            $track = $trackData['track'];
            $artist = $trackData['artist'];

            // Prepare data for our database
            $artistName = $track['artists'][0]['name'] ?? 'Unknown Artist';
            $trackName = $track['name'];

            $dataToSave = array_merge($dataToSave, [
                'spotify_track_id' => $validated['spotify_track_id'],
                'track_name' => $trackName,
                'artist_name' => implode(', ', array_map(fn($a) => $a['name'], $track['artists'])),
                'album_art_url' => $track['album']['images'][0]['url'] ?? null,
                'spotify_url' => $track['external_urls']['spotify'] ?? '#',
                'genres' => !empty($trackData['genres']) ? json_encode($trackData['genres']) : null,
            ]);

            // Search YouTube and add to data
            $searchQuery = $trackName . ' ' . $artistName;
            $youTubeData = $this->youTubeService->searchVideo($searchQuery);

            if ($youTubeData) {
                $dataToSave['youtube_video_id'] = $youTubeData['video_id'];
                $dataToSave['youtube_url'] = $youTubeData['url'];
            }

            // [NEW] MusicBrainz fallback for genres
            if (empty($dataToSave['genres']) && !empty($dataToSave['artist_name'])) {
                $mbGenres = $this->musicBrainzService->getArtistGenres($dataToSave['artist_name']);
                if (!empty($mbGenres)) {
                    $dataToSave['genres'] = json_encode($mbGenres);
                }
            }

            // [NEW] YouTube fallback for genres
            if (empty($dataToSave['genres']) && !empty($dataToSave['youtube_video_id'])) {
                $videoData = $this->youTubeService->getVideo($dataToSave['youtube_video_id']);
                if ($videoData && !empty($videoData['tags'])) {
                    $genreKeywords = ['pop', 'rock', 'hip hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop'];
                    $foundGenres = [];
                    foreach ($videoData['tags'] as $tag) {
                        foreach ($genreKeywords as $keyword) {
                            if (stripos($tag, $keyword) !== false) {
                                $foundGenres[] = $keyword;
                            }
                        }
                    }
                    $foundGenres = array_unique($foundGenres);

                    if (!empty($foundGenres)) {
                        $dataToSave['genres'] = json_encode($foundGenres);
                    }
                }
            }
        } elseif ($validated['type'] === 'text') {
            // For text shares, caption is required
            if (empty($validated['caption'])) {
                return back()->withErrors(['caption' => 'Caption is required for text shares.']);
            }
        }

        // Create the share
        $request->user()->shares()->create($dataToSave);

        return redirect(route('dashboard'));
    }

    // ... (index, create, show, etc. methods remain the same) ...

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Share $share)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
public function destroy(Share $share)
{
    // Authorize the action
    if (auth()->id() !== $share->user_id) {
        return response()->json(['error' => 'Unauthorized action.'], 403);
    }

    // Delete the share
    $share->delete();

    // Return a success response
    return response()->json(['message' => 'Share deleted successfully.']);
}
    /**
     * Toggles the "dislike" status for a given share.
     */
    public function toggleDislike(Share $share)
    {
        // Get the currently authenticated user
        $user = auth()->user();

        // Prevent user from disliking their own share
        if ($user->id === $share->user_id) {
            return response()->json([
                'message' => 'You cannot dislike your own share.',
                'disliked' => $user->dislikes->contains($share), // Should always be false
                'dislikesCount' => $share->dislikes()->count(),
            ], 403); // Forbidden
        }

        // If the user has liked this share, remove the like first
        if ($user->likes->contains($share)) {
            $user->likes()->detach($share);
        }

        // Use the toggle method to attach if not attached,
        // or detach if already attached.
        $user->dislikes()->toggle($share);

        // Return a JSON response with the new dislike count and disliked status
        return response()->json([
            'disliked' => $user->dislikes->contains($share),
            'dislikesCount' => $share->dislikes()->count(),
        ]);
    }

}
