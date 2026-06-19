<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Services\SpotifyService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistExportController extends Controller
{
    protected $spotifyService;
    protected $recommendationService;

    public function __construct(SpotifyService $spotifyService, RecommendationService $recommendationService)
    {
        $this->spotifyService = $spotifyService;
        $this->recommendationService = $recommendationService;
    }

    public function export(Request $request)
    {
        $request->validate([
            'source' => 'required|in:saved,discovery',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->spotify_token) {
            return back()->with('error', 'You must connect your Spotify account to export playlists.');
        }

        $source = $request->input('source');
        $songs = [];
        $playlistName = '';

        if ($source === 'saved') {
            // Get bookmarked shares
            $shares = $user->bookmarks()->with('song')->get();
            $songs = $shares->pluck('song')->filter();
            $playlistName = 'Reso Bookmarks';
        } elseif ($source === 'discovery') {
            // Get recommendations
            $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
            if (!empty($rawRecommendations)) {
                $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                $songs = collect(Song::whereIn('id', $recommendedSongIds)->get());
            }
            $playlistName = 'Reso Discoveries';
        }

        if (count($songs) === 0) {
            return back()->with('error', 'No songs found to export.');
        }

        $trackUris = [];
        foreach ($songs as $song) {
            if ($song->spotify_track_id) {
                $trackUris[] = 'spotify:track:' . $song->spotify_track_id;
            }
        }

        $trackUris = array_unique($trackUris); // Prevent duplicates

        if (empty($trackUris)) {
             return back()->with('error', 'No Spotify tracks found to export.');
        }

        // Create Playlist
        $playlist = $this->spotifyService->createPlaylist($user, $playlistName);

        if (!$playlist || !isset($playlist['id'])) {
            return back()->with('error', 'Failed to create Spotify playlist. Your session might have expired.');
        }

        // Add Tracks
        $success = $this->spotifyService->addTracksToPlaylist($user, $playlist['id'], $trackUris);

        if ($success) {
            return back()->with('success', "Success! Playlist '{$playlistName}' was created on your Spotify account.");
        } else {
            return back()->with('error', 'Playlist created, but failed to add some tracks.');
        }
    }
}
