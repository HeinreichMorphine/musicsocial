<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                Log::error("DiscoveryController: Raw recommendations count: " . count($rawRecommendations));
                $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                Log::error("DiscoveryController: Raw IDs: " . json_encode($recommendedSongIds));
                
                // --- [NEW] Filter out songs user has interacted with (Listened/Liked/Disliked) ---
                $interactedSongIds = \App\Models\SongInteraction::where('user_id', $user->id)
                                        ->pluck('song_id')
                                        ->toArray();
                
                Log::error("DiscoveryController: Interacted song IDs count: " . count($interactedSongIds));
                // Log::error("DiscoveryController: Interacted IDs: " . json_encode($interactedSongIds));

                // Exclude interacted IDs
                $filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);
                Log::error("DiscoveryController: Filtered song IDs count: " . count($filteredSongIds));
                Log::error("DiscoveryController: Filtered IDs: " . json_encode(array_values($filteredSongIds)));
                
                // Take top 12 from the filtered list (preserving order from recommender)
                
                // Take top 12 from the filtered list (preserving order from recommender)
                // Recommender returns sorted list, so we just take the first 12 valid ones.
                $top12Ids = array_slice($filteredSongIds, 0, 12);
                
                $recommendationData = collect($rawRecommendations)->keyBy('song_id');

                $recommendedSongs = Song::whereIn('id', $top12Ids)->get();
                Log::info("DiscoveryController: Songs retrieved from DB: " . $recommendedSongs->count());
                
                // Check if we are missing any songs
                if ($recommendedSongs->count() < count($top12Ids)) {
                     Log::warning("DiscoveryController: Mismatch! Expected " . count($top12Ids) . " songs, but got " . $recommendedSongs->count() . " from DB. IDs missing potentially.");
                     $retrievedIds = $recommendedSongs->pluck('id')->toArray();
                     $missingIds = array_diff($top12Ids, $retrievedIds);
                     Log::warning("DiscoveryController: Missing IDs: " . implode(',', $missingIds));
                }

                // Sort the recommended songs by score (re-apply sorting since whereIn doesn't guarantee order)
                $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                    return $recommendationData[$song->id]['score'] ?? 0;
                });

                $recommendedSongs = $recommendedSongs->values(); // Reset keys

                $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                    $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                    // Pass debug info if needed, but for now just reason
                    return $song;
                });
            } else {
                Log::info("DiscoveryController: No raw recommendations returned from service.");
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
