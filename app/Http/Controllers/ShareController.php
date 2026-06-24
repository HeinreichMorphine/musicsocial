<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\Song;
use App\Services\SpotifyService;
use App\Services\YouTubeService;
use App\Services\MusicBrainzService;
use App\Services\DiscogsService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ShareController extends Controller
{
    protected $spotifyService;
    protected $youTubeService;
    protected $musicBrainzService;
    protected $discogsService;
    protected $recommendationService;

    public function __construct(SpotifyService $spotifyService, YouTubeService $youTubeService, MusicBrainzService $musicBrainzService, DiscogsService $discogsService, RecommendationService $recommendationService)
    {
        $this->spotifyService = $spotifyService;
        $this->youTubeService = $youTubeService;
        $this->musicBrainzService = $musicBrainzService;
        $this->discogsService = $discogsService;
        $this->recommendationService = $recommendationService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:music,text,recommendation_request',
            'caption' => 'nullable|string|max:1000',
            'spotify_track_id' => 'nullable|string|max:255',
            'youtube_video_id' => 'nullable|string|max:255',
        ]);

        if ($validated['type'] === 'music' || $validated['type'] === 'recommendation_request') {
            if (empty($validated['spotify_track_id']) && empty($validated['youtube_video_id'])) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Spotify track ID or YouTube video ID is required for music shares.'], 422);
                }
                return back()->withErrors(['music_id' => 'Spotify track ID or YouTube video ID is required for music shares.']);
            }

            $song = null;

            if (!empty($validated['spotify_track_id'])) {
                $trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);

                if (isset($trackData['error'])) {
                    if ($request->wantsJson()) {
                        return response()->json(['error' => $trackData['error']], 400);
                    }
                    return back()->with('error', $trackData['error']);
                }

                $song = $trackData['song'];
            }

            if ($song) {
                $genres = json_decode($song->genres, true) ?? [];

                // 1. Enhance with MusicBrainz (Artist Genres)
                $musicBrainzGenres = $this->musicBrainzService->getArtistGenres($song->artist_name);
                if ($musicBrainzGenres && !isset($musicBrainzGenres['error'])) {
                    $genres = array_unique(array_merge($genres, $musicBrainzGenres));
                }

                // 2. Enhance with Discogs (Track Styles/Genres)
                $discogsGenres = $this->discogsService->getGenres($song->artist_name, $song->track_name);
                if (!empty($discogsGenres)) {
                    $genres = array_unique(array_merge($genres, $discogsGenres));
                }

                // Ensure we have a YouTube Video ID
                if (empty($song->youtube_video_id)) {
                    \Log::info('Searching YouTube for: ' . $song->track_name . ' ' . $song->artist_name);
                    $youTubeData = $this->youTubeService->searchVideo($song->track_name . ' ' . $song->artist_name);
                    if ($youTubeData) {
                        \Log::info('YouTube found for Song ID ' . $song->id . ': ' . $youTubeData['video_id']);
                        $song->update([
                            'youtube_video_id' => $youTubeData['video_id'],
                            'youtube_url' => $youTubeData['url'],
                        ]);
                    } else {
                        \Log::warning('YouTube search returned NO results for Song ID ' . $song->id);
                    }
                } else {
                    \Log::info('Song ID ' . $song->id . ' already has YouTube ID: ' . $song->youtube_video_id);
                }

                // 3. Final Fallback: YouTube Tags (Last Resort)
                if (empty($genres) && !empty($song->youtube_video_id)) {
                    $videoData = $this->youTubeService->getVideo($song->youtube_video_id);
                    if ($videoData) {
                        $youtubeGenres = $this->extractGenresFromText($videoData['title'] . ' ' . implode(' ', $videoData['tags'] ?? []) . ' ' . $videoData['description']);
                        if (!empty($youtubeGenres)) {
                            $genres = array_unique(array_merge($genres, $youtubeGenres));
                        }
                    }
                }

                $song->update(['genres' => json_encode(array_values(array_unique($genres)))]);

                $share = $request->user()->shares()->create([
                    'song_id' => $song->id,
                    'caption' => $validated['caption'],
                    'type' => $validated['type'],
                ]);
            } else {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Could not create or find song.'], 400);
                }
                return back()->with('error', 'Could not create or find song.');
            }

        } elseif ($validated['type'] === 'text') {
            if (empty($validated['caption'])) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Caption is required for text shares.'], 422);
                }
                return back()->withErrors(['caption' => 'Caption is required for text shares.']);
            }
            $share = $request->user()->shares()->create($validated);
        }

        if ($request->wantsJson() && isset($share)) {
            $share->load(['user', 'song', 'likes', 'dislikes', 'comments.user', 'comments.replies', 'comments.parent']); // Load relationships needed for the card
            $html = view('components.share-card', ['share' => $share])->render();
            return response()->json(['html' => $html, 'message' => 'Share created successfully.']);
        }

        return redirect(route('dashboard'));
    }

    private function extractGenresFromText(string $text): array
    {
        $genreKeywords = [
            // Mainstream Genres
            'pop', 'rock', 'hip hop', 'hip-hop', 'r&b', 'electronic', 'dance', 'country', 'jazz', 'classical', 'metal',
            'indie', 'alternative', 'soul', 'funk', 'reggae', 'latin', 'k-pop', 'afrobeat', 'blues', 'disco', 'gospel',
            'house', 'techno', 'trance', 'trap', 'world', 'lo-fi', 'lofi', 'chill', 'bedroom pop',

            // Niche Genres
            'synthwave', 'new wave', 'punk', 'folk', 'ambient', 'acoustic', 'shoegaze', 'dream pop', 'post-rock', 'math rock',
            'midwest emo', 'screamo', 'hardcore', 'metalcore', 'death metal', 'black metal', 'doom metal', 'stoner rock',
            'psychedelic rock', 'garage rock', 'surf rock', 'jangle pop', 'power pop', 'noise pop', 'twee pop', 'chamber pop',
            'art pop', 'hyperpop', 'glitchcore', 'bubblegum bass', 'deconstructed club', 'future bass', 'vaporwave', 'seapunk',
            'witch house', 'darkwave', 'coldwave', 'ethereal wave', 'gothic rock', 'industrial', 'ebm', 'aggrotech',
            'futurepop', 'synth-pop', 'electropop', 'electro-industrial', 'idm', 'drill and bass', 'glitch', 'breakcore',
            'jungle', 'drum and bass', 'dubstep', 'grime', 'uk garage', '2-step', 'footwork', 'juke', 'chicago house',
            'acid house', 'deep house', 'progressive house', 'electro house', 'big room', 'hardstyle', 'jumpstyle',
            'gabba', 'hardcore techno', 'speedcore', 'terrorcore', 'frenchcore', 'uptempo hardcore'
        ];
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
    public function show(Request $request, Share $share)
    {
        /** @var User $user */
        $user = Auth::user();

        // Eager load relationships (removed generic 'comments' eager load for performance)
        $share->load(['song']);

        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();

            // Sort the recommended songs by score
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });

            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
            });
        }

        // Fetch users to suggest (e.g., users not followed by the current user)
        $usersToSuggest = User::where('id', '!=', $user->id)
                            ->whereDoesntHave('followers', function ($query) use ($user) {
                                $query->where('follower_id', $user->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        // Optimize Comment Loading
        $totalCommentsCount = $share->comments()->count();
        $previewComments = $share->comments()->latest()->with('user')->take(3)->get();
        
        // Fetch top-level comments
        $commentsQuery = $share->comments()
            ->whereDoesntHave('parent')
            ->with(['user', 'replies.user', 'replies.replies.user']);

        if ($share->type === 'recommendation_request') {
            // Fetch all top-level comments to sort them by upvote count in memory
            // Note: For very large comment sections, a DB column would be better.
            $comments = $commentsQuery->get()->sortByDesc(fn($c) => $c->getUpvoteCount())->values();
            // Manually paginate if needed, or just pass the collection for now
            // Given the 'paginate' call in original code, I'll use simple pagination here
            $comments = new \Illuminate\Pagination\LengthAwarePaginator(
                $comments->forPage($request->input('page', 1), 10),
                $comments->count(),
                10,
                $request->input('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $comments = $commentsQuery->latest()->paginate(10);
        }

        return view('shares.show', [
            'share' => $share,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
            'comments' => $comments,
            'totalCommentsCount' => $totalCommentsCount,
            'previewComments' => $previewComments,
        ]);
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
        if ($request->user()->id !== $share->user_id) {
             return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:1000',
        ]);

        $share->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Share updated successfully.',
                'caption' => $share->caption
            ]);
        }

        return redirect(route('dashboard'))->with('success', 'Share updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Share $share)
    {
        $userId = auth()->id();
        \Log::info("User ID {$userId} attempting to delete share ID: {$share->id}");
        
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user || $user->id !== $share->user_id) {
            \Log::warning("Unauthorized delete attempt by User ID {$userId} for share ID: {$share->id}");
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        try {
            // Skeleton deletion: mark as deleted, clear caption to '[deleted]'
            // This preserves the comments structure, replies, and song metadata.
            $share->update([
                'is_deleted' => true,
                'caption' => '[deleted]',
            ]);
            
            \Log::info("Share ID: {$share->id} marked as deleted successfully by User ID {$userId}");
            return response()->json(['message' => 'Share deleted successfully.']);
            
        } catch (\Exception $e) {
            \Log::error("Error deleting share ID: {$share->id} (User ID {$userId}): " . $e->getMessage());
            return response()->json(['error' => 'Database error preventing deletion.'], 500);
        }
    }

    /**
     * Toggles the "dislike" status for a given share.
     */
    public function toggleDislike(Share $share)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->id === $share->user_id) {
            return response()->json([
                'message' => 'You cannot dislike your own share.',
                'disliked' => $user->dislikes->contains($share),
                'dislikesCount' => 0,
            ], 403);
        }

        if ($user->likes->contains($share)) {
            $user->likes()->detach($share);
        }

        $user->dislikes()->toggle($share);

        return response()->json([
            'disliked' => $user->dislikes->contains($share),
            'dislikesCount' => 0,
            'liked' => $user->likes->contains($share),
            'likesCount' => $share->likes()->count(),
        ]);
    }

    /**
     * Toggles the "bookmark" status for a given share.
     */
    public function toggleBookmark(Share $share)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->bookmarks()->toggle($share);

        return response()->json([
            'bookmarked' => $user->bookmarks->contains($share),
        ]);
    }
}
