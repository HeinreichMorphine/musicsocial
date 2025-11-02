<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoveryController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index()
    {
        $user = Auth::user();
        $recommendedShares = collect();
        $usersToSuggest = collect();

        if ($user) {
            $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
            $recommendedShares = collect();

            if (!empty($rawRecommendations)) {
                $recommendedShareIds = collect($rawRecommendations)->pluck('share_id')->all();
                $recommendationData = collect($rawRecommendations)->keyBy('share_id');

                $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

                // Sort the recommended shares by score
                $recommendedShares = $recommendedShares->sortByDesc(function ($share) use ($recommendationData) {
                    return $recommendationData[$share->id]['score'] ?? 0;
                });

                $recommendedShares = $recommendedShares->map(function ($share) use ($recommendationData) {
                    $share->reason = $recommendationData[$share->id]['reason'] ?? 'Based on your taste';
                    return $share;
                });
            }

            // --- [NEW] Improved "Who to Follow" Logic ---

            // 1. Find users who liked the same shares as the current user (Taste Neighbors)
            $likedShareIds = $user->likes->pluck('id');
            $tasteNeighbors = User::where('id', '!=', $user->id)
                ->whereHas('likes', function ($query) use ($likedShareIds) {
                    $query->whereIn('share_id', $likedShareIds);
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

        return view('discovery', compact('recommendedShares', 'usersToSuggest'));
    }
}
