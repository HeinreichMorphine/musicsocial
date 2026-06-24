<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SpotifyService;
use App\Services\RecommendationService;
use App\Models\Playlist;
use App\Models\PlaylistCollaborator;
use App\Models\PlaylistSong;
use App\Models\User;
use App\Models\Song;
use Illuminate\Support\Facades\Auth;

class SpotifyImportController extends Controller
{
    protected $spotifyService;
    protected $recommendationService;

    public function __construct(SpotifyService $spotifyService, RecommendationService $recommendationService)
    {
        $this->spotifyService = $spotifyService;
        $this->recommendationService = $recommendationService;
    }

    public function index()
    {
        $user = Auth::user();
        $spotifyPlaylists = [];

        if ($user->spotify_token) {
            $spotifyPlaylists = $this->spotifyService->getUserPlaylists($user);
        }
        
        // Sidebar Data
        $usersToSuggest = User::where('id', '!=', $user->id)
            ->whereDoesntHave('followers', function ($query) use ($user) {
                $query->where('follower_id', $user->id);
            })
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $recommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedSongIds = collect($recommendations)->pluck('song_id')->all();
        $recommendationData = collect($recommendations)->keyBy('song_id');
        $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();
        $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
            $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
            return $song;
        });

        return view('playlists.import.index', compact('usersToSuggest', 'recommendedSongs', 'spotifyPlaylists'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'spotify_url' => 'required|url'
        ]);

        $url = $request->input('spotify_url');
        
        // Extract playlist ID from URL
        if (preg_match('/playlist\/([a-zA-Z0-9]+)/', $url, $matches)) {
            $playlistId = $matches[1];
        } else {
            return back()->with('error', 'Invalid Spotify Playlist URL.');
        }

        $playlistData = $this->spotifyService->getPlaylistTracks($playlistId);

        if (!$playlistData || !isset($playlistData['tracks']['items'])) {
            return back()->with('error', 'Could not fetch playlist. It may be private or the URL is invalid.');
        }

        $tracks = [];
        $playlistImage = $playlistData['images'][0]['url'] ?? null;
        
        foreach ($playlistData['tracks']['items'] as $item) {
            if (isset($item['track']['id'])) {
                $albumArt = $item['track']['album']['images'][2]['url'] ?? $item['track']['album']['images'][0]['url'] ?? null;
                $tracks[] = [
                    'id' => $item['track']['id'],
                    'name' => $item['track']['name'],
                    'artist' => implode(', ', array_map(fn($a) => $a['name'], $item['track']['artists'] ?? [])),
                    'album_art' => $albumArt
                ];
                
                // Fallback for playlist image
                if (!$playlistImage && ($item['track']['album']['images'][0]['url'] ?? null)) {
                    $playlistImage = $item['track']['album']['images'][0]['url'];
                }
            }
        }

        $user = Auth::user();
        
        // Sidebar Data
        $usersToSuggest = User::where('id', '!=', $user->id)
            ->whereDoesntHave('followers', function ($query) use ($user) {
                $query->where('follower_id', $user->id);
            })
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $recommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedSongIds = collect($recommendations)->pluck('song_id')->all();
        $recommendationData = collect($recommendations)->keyBy('song_id');
        $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();
        $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
            $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
            return $song;
        });

        return view('playlists.import.preview', [
            'playlist_name' => $playlistData['name'],
            'playlist_image' => $playlistImage,
            'tracks' => $tracks,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'playlist_name' => 'required|string|max:255',
            'playlist_image' => 'nullable|url',
            'selected_tracks' => 'required|array|max:15', // HARD CAP: 15 songs
            'selected_tracks.*' => 'string'
        ]);

        $user = Auth::user();

        // 1. Create Playlist
        $playlist = Playlist::create([
            'name' => $request->input('playlist_name'),
            'description' => 'Imported from Spotify',
            'cover_image' => $request->input('playlist_image')
        ]);

        // 2. Add Owner
        PlaylistCollaborator::create([
            'playlist_id' => $playlist->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'accepted'
        ]);

        // 3. Process Tracks
        $addedCount = 0;
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($request->input('selected_tracks') as $trackJson) {
                $trackData = json_decode($trackJson, true);
                if (!$trackData || !isset($trackData['id'])) continue;

                // First fetch/create the song locally via basic metadata
                $song = Song::firstOrCreate([
                    'spotify_track_id' => $trackData['id'],
                ], [
                    'track_name' => $trackData['name'],
                    'artist_name' => $trackData['artist'],
                    'album_art_url' => $trackData['album_art'],
                    'spotify_url' => 'https://open.spotify.com/track/' . $trackData['id']
                ]);
                
                // Add to playlist_songs
                PlaylistSong::firstOrCreate([
                    'playlist_id' => $playlist->id,
                    'song_id' => $song->spotify_track_id,
                ], [
                    'added_by_user_id' => $user->id
                ]);
                $addedCount++;
            }
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()->route('playlists.show', $playlist->id)
            ->with('success', "Successfully imported {$addedCount} tracks to your new playlist '{$playlist->name}'!");
    }

    public function searchSpotifyPlaylists(Request $request)
    {
        $query = $request->query('q');
        if (empty($query)) {
            return response()->json([]);
        }

        $playlists = $this->spotifyService->searchPlaylists($query, 12);
        
        return response()->json($playlists);
    }
}
