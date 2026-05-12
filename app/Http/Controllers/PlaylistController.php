<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\PlaylistCollaborator;
use App\Models\PlaylistSong;
use App\Models\User;
use App\Models\Song;
use App\Models\SongInteraction;
use App\Models\Share;
use App\Services\SpotifyService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
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

        // Playlists where the user is an owner or an accepted collaborator
        $playlists = Playlist::whereHas('collaborators', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('status', ['accepted']);
        })->with(['collaborators.user', 'songs'])->get();

        // Pending invites
        $invites = Playlist::whereHas('collaborators', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'pending');
        })->get();

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

        return view('playlists.index', compact('playlists', 'invites', 'usersToSuggest', 'recommendedSongs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $playlist = Playlist::create($validated);

        // Make the creator the owner
        PlaylistCollaborator::create([
            'playlist_id' => $playlist->id,
            'user_id' => Auth::id(),
            'role' => 'owner',
            'status' => 'accepted'
        ]);

        return redirect()->route('playlists.show', $playlist->id)->with('success', 'Playlist created!');
    }

    public function show(Playlist $playlist)
    {
        $user = Auth::user();
        
        $collab = $playlist->collaborators()->where('user_id', $user->id)->first();

        if (!$collab || $collab->status === 'declined') {
            return abort(403, 'You are not a collaborator on this playlist.');
        }

        $playlist->load(['songs.addedBy', 'collaborators.user']);

        // Fetch users the current user follows, so they can invite them
        $following = $user->following()->get();

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

        return view('playlists.show', compact('playlist', 'following', 'collab', 'usersToSuggest', 'recommendedSongs'));
    }

    public function invite(Request $request, Playlist $playlist)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        if ($playlist->collaborators()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'User is already a collaborator or has a pending invite.');
        }

        PlaylistCollaborator::create([
            'playlist_id' => $playlist->id,
            'user_id' => $validated['user_id'],
            'role' => 'collaborator',
            'status' => 'pending'
        ]);

        $invitedUser = User::find($validated['user_id']);
        if ($invitedUser) {
            $invitedUser->notify(new \App\Notifications\PlaylistInviteNotification($playlist, $user));
        }

        return back()->with('success', 'Invitation sent!');
    }

    public function acceptInvite(Playlist $playlist)
    {
        $user = Auth::user();
        $collab = PlaylistCollaborator::where('playlist_id', $playlist->id)
                                      ->where('user_id', $user->id)
                                      ->where('status', 'pending')
                                      ->firstOrFail();

        $collab->update(['status' => 'accepted']);

        // TC-09 Peer-Node Clustering (TC-03 & TC-04): Mandatory Friend Multiplier (Rm = 1.0)
        $owner = PlaylistCollaborator::where('playlist_id', $playlist->id)->where('role', 'owner')->first();
        if ($owner && !$user->isFollowing($owner->user)) {
            $user->following()->attach($owner->user_id);
        }

        return redirect()->route('playlists.show', $playlist->id)->with('success', 'You joined the playlist!');
    }

    public function declineInvite(Playlist $playlist)
    {
        $user = Auth::user();
        PlaylistCollaborator::where('playlist_id', $playlist->id)
                            ->where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->delete();

        return redirect()->route('playlists.index')->with('success', 'Invitation declined.');
    }

    public function addSong(Request $request, Playlist $playlist)
    {
        $validated = $request->validate([
            'spotify_track_id' => 'required|string'
        ]);

        $user = Auth::user();
        
        $collab = $playlist->collaborators()->where('user_id', $user->id)->where('status', 'accepted')->first();
        if (!$collab) {
             return response()->json(['error' => 'Not a collaborator'], 403);
        }

        // Ensure not already added
        if ($playlist->songs()->where('song_id', $validated['spotify_track_id'])->exists()) {
            return response()->json(['error' => 'Song already in playlist'], 400);
        }

        PlaylistSong::create([
            'playlist_id' => $playlist->id,
            'song_id' => $validated['spotify_track_id'],
            'added_by_user_id' => $user->id
        ]);

        // TC-09: High-effort Interaction (c_ui = 3 pts)
        // Adding to a shared playlist is treated as a 'share' for maximum affinity boost
        $trackData = $this->spotifyService->getTrack($validated['spotify_track_id']);
        if (!isset($trackData['error']) && isset($trackData['song'])) {
            $song = $trackData['song'];
            SongInteraction::updateOrCreate(
                ['user_id' => $user->id, 'song_id' => $song->id],
                ['type' => 'share', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return response()->json(['success' => true]);
    }

    public function updateCover(Request $request, Playlist $playlist)
    {
        $request->validate([
            'cover_image' => 'required|image|max:2048', // 2MB Max
        ]);

        $user = Auth::user();
        $collab = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->first();

        if (!$collab) {
            return back()->with('error', 'Only the owner can change the cover image.');
        }

        if ($request->hasFile('cover_image')) {
            // Delete old cover if exists
            if ($playlist->cover_image) {
                \Illuminate\Support\Facades\Storage::delete($playlist->cover_image);
            }

            $path = $request->file('cover_image')->store('playlist_covers', 'public');
            $playlist->update(['cover_image' => $path]);
        }

        return back()->with('success', 'Playlist cover updated!');
    }
    public function edit(Playlist $playlist)
    {
        $user = Auth::user();
        $collab = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->first();

        if (!$collab) {
            return abort(403, 'Only the owner can edit this playlist.');
        }

        return view('playlists.edit', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        $user = Auth::user();
        $collab = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->first();

        if (!$collab) {
            return abort(403, 'Only the owner can update this playlist.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $playlist->update($validated);

        return redirect()->route('playlists.show', $playlist->id)->with('success', 'Playlist updated!');
    }

    public function destroy(Playlist $playlist)
    {
        $user = Auth::user();
        $collab = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->first();

        if (!$collab) {
            return abort(403, 'Only the owner can delete this playlist.');
        }

        // Delete cover image if it exists
        if ($playlist->cover_image) {
            \Illuminate\Support\Facades\Storage::delete($playlist->cover_image);
        }

        $playlist->delete();

        return redirect()->route('playlists.index')->with('success', 'Playlist deleted successfully.');
    }

    public function removeSong(Playlist $playlist, $songId)
    {
        $user = Auth::user();
        
        // Find the specific entry in playlist_songs
        $playlistSong = PlaylistSong::where('playlist_id', $playlist->id)
                                    ->where('song_id', $songId)
                                    ->firstOrFail();

        // Check if user is the one who added the song OR is the owner of the playlist
        $isOwner = $playlist->collaborators()->where('user_id', $user->id)->where('role', 'owner')->exists();
        
        if ($playlistSong->added_by_user_id !== $user->id && !$isOwner) {
            return back()->with('error', 'You are not authorized to remove this song.');
        }

        $playlistSong->delete();

        return back()->with('success', 'Song removed from playlist.');
    }
}
