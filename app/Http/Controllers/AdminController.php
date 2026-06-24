<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Share;
use App\Models\Comment;
use App\Models\Admin;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AdminController extends Controller
{
    public function loginForm()
    {
        // Already-logged-in admin → go to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        // Already-logged-in regular user → go to their dashboard
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $userCount    = User::count();
        $shareCount   = Share::count();
        $commentCount = Comment::count();
        $songCount    = \App\Models\Song::count();
        $playlistCount = \App\Models\Playlist::count();

        $latestUsers  = User::latest()->take(5)->get();
        $latestShares = Share::with('user', 'song')->latest()->take(5)->get();

        // Daily share activity for the last 7 days (Chart.js)
        $activityLabels = [];
        $activityData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $activityLabels[] = now()->subDays($i)->format('M j');
            $activityData[]   = Share::whereDate('created_at', $date)->count();
        }

        // Top 5 genres from songs table
        $topGenres = \Illuminate\Support\Facades\DB::table('songs')
            ->select(\Illuminate\Support\Facades\DB::raw('genres, COUNT(*) as count'))
            ->whereNotNull('genres')
            ->groupBy('genres')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'userCount', 'shareCount', 'commentCount',
            'songCount', 'playlistCount',
            'latestUsers', 'latestShares',
            'activityLabels', 'activityData',
            'topGenres'
        ));
    }

    public function users()
    {
        $search = request('search');
        $users = User::withCount('shares')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', compact('users', 'search'));
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_banned = !$user->is_banned;
        $user->save();

        // Force-logout: delete all active sessions for this user
        if ($user->is_banned) {
            \DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $status = $user->is_banned ? 'banned' : 'unbanned';
        return redirect()->back()->with('success', "User has been {$status}.");
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function moderation()
    {
        $search   = request('search');
        $shares   = Share::with('user', 'song')
            ->withCount('likes')
            ->when($search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")))
            ->latest()->paginate(12, ['*'], 'shares_page')->withQueryString();
        $comments = Comment::with('user')
            ->when($search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                                        ->orWhere('body', 'like', "%{$search}%"))
            ->latest()->paginate(12, ['*'], 'comments_page')->withQueryString();
        $playlists = \App\Models\Playlist::with('creator.user')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                                        ->orWhereHas('creator.user', fn($u) => $u->where('name', 'like', "%{$search}%")))
            ->withCount('songs')
            ->latest()->paginate(12, ['*'], 'playlists_page')->withQueryString();

        return view('admin.moderation', compact('shares', 'comments', 'playlists', 'search'));
    }

    public function deletePlaylist($id)
    {
        $playlist = \App\Models\Playlist::findOrFail($id);
        
        // Manual cleanup if needed, though usually cascade covers this
        \App\Models\PlaylistCollaborator::where('playlist_id', $playlist->id)->delete();
        \App\Models\PlaylistSong::where('playlist_id', $playlist->id)->delete();
        
        $playlist->delete();
        return redirect()->back()->with('success', 'Playlist deleted successfully.');
    }

    public function deleteShare($id)
    {
        $share = Share::findOrFail($id);
        
        // Manual cleanup to ensure no FK constraint failures
        \App\Models\Like::where('share_id', $share->id)->delete();
        \App\Models\Bookmark::where('share_id', $share->id)->delete();
        \App\Models\Notification::where('data->share_id', $share->id)->delete();
        \App\Models\SongInteraction::where('share_id', $share->id)->delete();
        
        // Delete comments manually if they aren't cascading
        foreach ($share->comments as $comment) {
            \App\Models\Upvote::where('comment_id', $comment->id)->delete();
            $comment->delete();
        }

        $share->delete();
        return redirect()->back()->with('success', 'Share deleted successfully.');
    }

    public function deleteComment($id)
    {
        Comment::destroy($id);
        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }



    public function retrainPage(Request $request)
    {
        $recommendations = null;
        $users = User::select('id', 'name')->get(); // Fetch all users for the dropdown

        if ($request->has('user_id')) {
            $userId = $request->input('user_id');
            // Call Flask API to get recommendations
            try {
                $client = new \GuzzleHttp\Client();
                $recommenderUrl = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000');
                $response = $client->request('GET', rtrim($recommenderUrl, '/') . "/recommendations/{$userId}");
                $data = json_decode($response->getBody(), true);
                if (isset($data['recommendations'])) {
                    $rawRecommendations = $data['recommendations'];
                    $songIds = collect($rawRecommendations)->pluck('song_id')->all();
                    $songs = \App\Models\Song::whereIn('id', $songIds)->get()->keyBy('id');

                    $recommendations = [];
                    foreach ($rawRecommendations as $rec) {
                        $song = $songs->get($rec['song_id']);
                        $rec['song_name'] = $song ? $song->track_name : 'Unknown Song';
                        // Ensure debug key exists
                        $rec['debug'] = $rec['debug'] ?? [
                            'svd' => $rec['score'] * 0.7, // Fallback approximation
                            'context' => 0,
                            'weighted_base' => $rec['score'] * 0.7,
                            'weighted_social' => ($rec['social_boost'] ?? 0) * 0.3
                        ];
                        $recommendations[] = $rec;
                    }
                }
            } catch (\Exception $e) {
                // Fallback or error handling
                // For now, just return null or empty array
            }
        }
        return view('admin.retrain', compact('recommendations', 'users'));
    }


    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->save();

        return redirect()->back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin->password = bcrypt($request->password);
        $admin->save();

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function admins()
    {
        $admins = Admin::all();
        return view('admin.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->back()->with('success', 'New admin created successfully.');
    }

    public function deleteAdmin($id)
    {
        if (Auth::guard('admin')->id() == $id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        Admin::destroy($id);
        return redirect()->back()->with('success', 'Admin deleted successfully.');
    }

    public function retrainProcess()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $recommenderUrl = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000');
            $response = $client->request('POST', rtrim($recommenderUrl, '/') . "/retrain");
            
            if ($response->getStatusCode() == 200) {
                return redirect()->back()->with('success', 'Model retraining initiated successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to initiate retraining. API returned status: ' . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error calling Recommender Service: ' . $e->getMessage());
        }
    }

    public function markAllNotificationsRead()
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->unreadNotifications->markAsRead();
        }
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function algoTestSuite()
    {
        return view('admin.algo_test_suite');
    }

    public function algoTestSuiteFrame()
    {
        $filePath = base_path('algo_test_suite.html');
        if (!file_exists($filePath)) {
            abort(404, 'Test suite file not found.');
        }
        return response(file_get_contents($filePath), 200)
            ->header('Content-Type', 'text/html');
    }

    public function algoTestSuiteApi(Request $request, $endpoint = '')
    {
        $recommenderUrl = env('PYTHON_RECOMMENDER_URL', 'http://127.0.0.1:5000');
        $url = rtrim($recommenderUrl, '/') . '/' . $endpoint;
        $query = $request->getQueryString();
        if ($query) {
            $url .= '?' . $query;
        }

        try {
            $method = strtolower($request->method());
            
            if ($method === 'post') {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->post($url, $request->json()->all());
            } else {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
            }

            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Recommender Connection failed: ' . $e->getMessage()], 500);
        }
    }

    // --- Song Management ---

    public function songs()
    {
        $sort = request('sort', 'latest');
        $search = request('search');
        
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $likeExpression = $driver === 'sqlite'
            ? "'%[SONG:' || songs.spotify_track_id || ']%'"
            : "concat('%[SONG:', songs.spotify_track_id, ']%')";

        $query = \App\Models\Song::select('songs.*')
            ->addSelect([
                'comments_count' => \App\Models\Comment::selectRaw('count(*)')
                    ->whereRaw("comments.body LIKE {$likeExpression}")
            ])
            ->withCount('shares');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('track_name', 'like', "%{$search}%")
                  ->orWhere('artist_name', 'like', "%{$search}%")
                  ->orWhere('genres', 'like', "%{$search}%");
            });
        }
        
        if ($sort === 'untagged') {
            $query->where(function ($q) {
                $q->whereNull('genres')
                  ->orWhere('genres', '')
                  ->orWhere('genres', '[]')
                  ->orWhere('genres', '""')
                  ->orWhere('genres', 'null')
                  ->orWhere('genres', '{}');
            })->latest();
        } elseif ($sort === 'shares') {
            $query->orderByRaw('(shares_count + comments_count) DESC')->latest();
        } elseif ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $songs = $query->paginate(15)->withQueryString();

        return view('admin.songs.index', compact('songs', 'sort', 'search'));
    }

    public function createSong()
    {
        return view('admin.songs.create');
    }

    public function storeSong(Request $request)
    {
        $data = $request->validate([
            'track_name' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'release_date' => 'nullable|date',
            'genres' => 'nullable|string',
            'album_art_url' => 'nullable|url',
            'spotify_track_id' => 'nullable|string',
            'youtube_url' => 'nullable|url',
        ]);

        if (!empty($data['genres'])) {
            $genresArray = array_map('trim', explode(',', $data['genres']));
            $data['genres'] = json_encode(array_values(array_filter($genresArray)));
        }

        if (!empty($data['spotify_track_id'])) {
            if (preg_match('/track\/([a-zA-Z0-9]+)/', $data['spotify_track_id'], $matches)) {
                $data['spotify_track_id'] = $matches[1];
            }
            $data['spotify_url'] = 'https://open.spotify.com/track/' . $data['spotify_track_id'];
        }

        \App\Models\Song::create($data);

        return redirect()->route('admin.songs')->with('success', 'Song added successfully.');
    }

    public function editSong(\App\Models\Song $song)
    {
        return view('admin.songs.edit', compact('song'));
    }

    public function updateSong(Request $request, \App\Models\Song $song)
    {
        $data = $request->validate([
            'track_name' => 'required|string|max:255',
            'artist_name' => 'required|string|max:255',
            'release_date' => 'nullable|date',
            'genres' => 'nullable|string',
            'album_art_url' => 'nullable|url',
            'spotify_track_id' => 'nullable|string',
            'youtube_url' => 'nullable|url',
        ]);

        if (!empty($data['genres'])) {
            $genresArray = array_map('trim', explode(',', $data['genres']));
            $data['genres'] = json_encode(array_values(array_filter($genresArray)));
        } else {
            $data['genres'] = null;
        }

        if (!empty($data['spotify_track_id'])) {
            if (preg_match('/track\/([a-zA-Z0-9]+)/', $data['spotify_track_id'], $matches)) {
                $data['spotify_track_id'] = $matches[1];
            }
            $data['spotify_url'] = 'https://open.spotify.com/track/' . $data['spotify_track_id'];
        } else {
            $data['spotify_track_id'] = null;
            $data['spotify_url'] = null;
        }

        $song->update($data);

        return redirect()->route('admin.songs')->with('success', 'Song updated successfully.');
    }

    public function deleteSong(\App\Models\Song $song)
    {
        if ($song->shares()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete a song that has shares. Delete the shares first.');
        }

        $song->delete();
        return redirect()->back()->with('success', 'Song deleted successfully.');
    }

    public function refetchGenres(\App\Models\Song $song, \App\Services\SpotifyService $spotifyService)
    {
        if (!$song->spotify_track_id) {
            return response()->json([
                'success' => false,
                'message' => 'Song missing Spotify Track ID.'
            ], 422);
        }

        // 1. Force flush the 7-day cache block for this specific track
        \Illuminate\Support\Facades\Cache::forget("genres_track_v2_{$song->spotify_track_id}");

        // 2. Re-run the engine (which now includes your new YouTube backstop)
        $genreData = $spotifyService->getGenresWithSources($song->spotify_track_id);

        if (!empty($genreData['genres'])) {
            $song->update([
                'genres' => json_encode($genreData['genres'])
            ]);

            return response()->json([
                'success' => true, 
                'genres' => $genreData['genres']
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'All automated metadata systems drew a blank.'
        ], 422);
    }
}
