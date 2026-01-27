<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    protected $recommendationService;

    public function __construct(\App\Services\RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $query = $request->input('query') ?? $request->input('user');
        $users = collect();
        $shares = collect();

        if ($query) {
            // Search Users
            $users = User::where('name', 'like', '%' . $query . '%')
                         ->orWhere('email', 'like', '%' . $query . '%')
                         ->paginate(10, ['*'], 'users_page');

            // Search Shares (Captions) or Songs (Title/Artist)
            $shares = \App\Models\Share::where('caption', 'like', '%' . $query . '%')
                ->orWhereHas('song', function($q) use ($query) {
                    $q->where('track_name', 'like', '%' . $query . '%')
                      ->orWhere('artist_name', 'like', '%' . $query . '%');
                })
                ->with(['user', 'song', 'likes', 'comments'])
                ->latest()
                ->paginate(10, ['*'], 'shares_page');
        }

        // Sidebar Data
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Fetch recommendations
        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = \App\Models\Song::whereIn('id', $recommendedSongIds)->get();
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });
            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
            });
        }

        // Fetch user suggestions
        $usersToSuggest = User::where('id', '!=', $user->id)
                            ->whereDoesntHave('followers', function ($query) use ($user) {
                                $query->where('follower_id', $user->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        return view('user.search-results', [
            'users' => $users,
            'shares' => $shares,
            'searchQuery' => $query,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
        ]);
    }
}
