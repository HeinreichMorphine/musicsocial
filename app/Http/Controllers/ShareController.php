<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Services\SpotifyService;
use App\Services\YouTubeService; // <-- [NEW] Import YouTubeService
use Illuminate\Http\Request;

class ShareController extends Controller
{
    protected $spotifyService;
    protected $youTubeService; // <-- [NEW] Add YouTubeService property

    // [UPDATED] Inject both services
    public function __construct(SpotifyService $spotifyService, YouTubeService $youTubeService)
    {
        $this->spotifyService = $spotifyService;
        $this->youTubeService = $youTubeService;
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

            if (!$trackData) {
                return back()->with('error', 'Could not find track on Spotify.');
            }

            // Prepare data for our database
            $artistName = $trackData['artists'][0]['name'] ?? 'Unknown Artist';
            $trackName = $trackData['name'];

            $dataToSave = array_merge($dataToSave, [
                'spotify_track_id' => $validated['spotify_track_id'],
                'track_name' => $trackName,
                'artist_name' => implode(', ', array_map(fn($artist) => $artist['name'], $trackData['artists'])),
                'album_art_url' => $trackData['album']['images'][0]['url'] ?? null,
                'spotify_url' => $trackData['external_urls']['spotify'] ?? '#',
            ]);

            // Search YouTube and add to data
            $searchQuery = $trackName . ' ' . $artistName;
            $youTubeData = $this->youTubeService->searchVideo($searchQuery);

            if ($youTubeData) {
                $dataToSave['youtube_video_id'] = $youTubeData['video_id'];
                $dataToSave['youtube_url'] = $youTubeData['url'];
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
}
