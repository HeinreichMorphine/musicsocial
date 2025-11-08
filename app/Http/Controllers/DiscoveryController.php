<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the display of the discovery page, which provides personalized content
 * recommendations to the user, including recommended songs and users to follow.
 */
class DiscoveryController extends Controller
{
    protected $recommendationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\RecommendationService  $recommendationService The recommendation service, injected by the service container.
     * @return void
     */
    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Display the discovery page with recommended songs and users.
     *
     * This method fetches personalized song recommendations from the `RecommendationService`
     * and generates a list of suggested users to follow based on shared tastes and popularity.
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
