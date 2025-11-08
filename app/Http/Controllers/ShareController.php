<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\Song;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use App\Services\MusicBrainzService;
use App\Services\AudioDbService;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    protected $spotifyService;
    protected $youTubeService;
    protected $musicBrainzService;
    protected $audioDbService;

    public function __construct(SpotifyService $spotifyService, YouTubeService $youTubeService, MusicBrainzService $musicBrainzService, AudioDbService $audioDbService)
    {
        $this->spotifyService = $spotifyService;
        $this->youTubeService = $youTubeService;
        $this->musicBrainzService = $musicBrainzService;
        $this->audioDbService = $audioDbService;
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
            'youtube_video_id' => 'nullable|string|max:255',
        ]);

        if ($validated['type'] === 'music') {
            if (empty($validated['spotify_track_id']) && empty($validated['youtube_video_id'])) {
                return back()->withErrors(['music_id' => 'Spotify track ID or YouTube video ID is required for music shares.']);
            }

            $song = null;

            if (!empty($validated['spotify_track_id'])) {
                $trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);

                if (isset($trackData['error'])) {
                    return back()->with('error', $trackData['error']);
                }

                $song = $trackData['song'];
            }

            if ($song) {
                $genres = json_decode($song->genres, true) ?? [];

                $audioDbGenres = $this->audioDbService->getGenres($song->track_name, $song->artist_name);
                if (!empty($audioDbGenres)) {
                    $genres = array_unique(array_merge($genres, $audioDbGenres));
                }

                $musicBrainzGenres = $this->musicBrainzService->getArtistGenres($song->artist_name);
                if ($musicBrainzGenres && !isset($musicBrainzGenres['error'])) {
                    $genres = array_unique(array_merge($genres, $musicBrainzGenres));
                }

                $spotifyGenres = json_decode($song->genres, true) ?? [];
                $genres = array_unique(array_merge($genres, $spotifyGenres));

                if (empty($song->youtube_video_id)) {
                    $youTubeData = $this->youTubeService->searchVideo($song->track_name . ' ' . $song->artist_name);
                    if ($youTubeData) {
                        $song->update([
                            'youtube_video_id' => $youTubeData['video_id'],
                            'youtube_url' => $youTubeData['url'],
                        ]);

                        if (empty($genres)) {
                            $videoData = $this->youTubeService->getVideo($youTubeData['video_id']);
                            if ($videoData) {
                                $youtubeGenres = $this->extractGenresFromText($videoData['title'] . ' ' . implode(' ', $videoData['tags'] ?? []) . ' ' . $videoData['description']);
                                if (!empty($youtubeGenres)) {
                                    $genres = array_unique(array_merge($genres, $youtubeGenres));
                                }
                            }
                        }
                    }
                }

                $song->update(['genres' => json_encode(array_values(array_unique($genres)))]);

                $request->user()->shares()->create([
                    'song_id' => $song->id,
                    'caption' => $validated['caption'],
                    'type' => $validated['type'],
                ]);
            } else {
                return back()->with('error', 'Could not create or find song.');
            }

        } elseif ($validated['type'] === 'text') {
            if (empty($validated['caption'])) {
                return back()->withErrors(['caption' => 'Caption is required for text shares.']);
            }
            $request->user()->shares()->create($validated);
        }

        return redirect(route('dashboard'));
    }

    private function extractGenresFromText(string $text): array
    {
        $genreKeywords = ['pop', 'rock', 'hip hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal', 'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop', 'afrobeat', 'blues', 'disco', 'gospel', 'house', 'techno', 'trance', 'trap', 'world'];
        $foundGenres = [];
        foreach ($genreKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $foundGenres[] = $keyword;
            }
        }
        return $foundGenres;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the 'song' relationship
        $shares = Share::with('song')->get();
        return view('shares.index', compact('shares')); // Assuming you have an index view for shares
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shares.create'); // Assuming you have a create view for shares
    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        // Eager load the 'song' relationship
        $share->load('song');
        return view('shares.show', compact('share')); // Assuming you have a show view for shares
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        // Eager load the 'song' relationship
        $share->load('song');
        return view('shares.edit', compact('share')); // Assuming you have an edit view for shares
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Share $share)
    {
        // For now, assuming only caption can be updated for a share
        $validated = $request->validate([
            'caption' => 'nullable|string|max:1000',
        ]);

        $share->update($validated);

        return redirect(route('dashboard'))->with('success', 'Share updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Share $share)
    {
        if (auth()->id() !== $share->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $share->delete();

        return response()->json(['message' => 'Share deleted successfully.']);
    }

    /**
     * Toggles the "dislike" status for a given share.
     */
    public function toggleDislike(Share $share)
    {
        $user = auth()->user();

        if ($user->id === $share->user_id) {
            return response()->json([
                'message' => 'You cannot dislike your own share.',
                'disliked' => $user->dislikes->contains($share),
                'dislikesCount' => $share->dislikes()->count(),
            ], 403);
        }

        if ($user->likes->contains($share)) {
            $user->likes()->detach($share);
        }

        $user->dislikes()->toggle($share);

        return response()->json([
            'disliked' => $user->dislikes->contains($share),
            'dislikesCount' => $share->dislikes()->count(),
        ]);
    }
}