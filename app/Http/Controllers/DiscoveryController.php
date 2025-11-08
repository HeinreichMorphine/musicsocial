<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the display of the discovery page, which provides personalized content
 * recommendations to the user, including recommended songs and users to follow.
 */
class DiscoveryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Display the discovery page with recommended songs and users.
     *
     * This method generates a list of suggested users to follow based on shared tastes and popularity.
     * It then passes this data to the `discovery` view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $recommendedSongs = collect();
        $usersToSuggest = collect();

        if ($user) {
            // --- [NEW] Improved "Who to Follow" Logic ---

            // 1. Find users who liked the same songs as the current user (Taste Neighbors)
            $likedSongIds = $user->likes->pluck('song.id');
            $tasteNeighbors = User::where('id', '!=', $user->id)
                ->whereHas('likes', function ($query) use ($likedSongIds) {
                    $query->whereIn('song_id', $likedSongIds);
                })
                ->whereDoesntHave('followers', function ($query) use ($user) {
                    $query->where('follower_id', $user->id);
                })
                ->withCount('followers')
                ->orderByDesc('followers_count') // Prioritize more popular users among taste neighbors
                ->limit(3)
                ->get();

            // 2. Find other random users to suggest
            $otherUsers = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $tasteNeighbors->pluck('id')) // Exclude taste neighbors
                ->whereDoesntHave('followers', function ($query) use ($user) {
                    $query->where('follower_id', $user->id);
                })
                ->inRandomOrder()
                ->limit(5 - $tasteNeighbors->count()) // Fill the rest of the spots
                ->get();

            $usersToSuggest = $tasteNeighbors->merge($otherUsers);
        }

        return view('discovery', compact('recommendedSongs', 'usersToSuggest'));
    }
}
