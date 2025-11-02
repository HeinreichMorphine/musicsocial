<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Get IDs of users the current user follows
        $followingIds = $user->following()->pluck('id');

        // Get shares from those users, plus the current user's own shares
        $shares = Share::where(function ($query) use ($followingIds, $user) {
                           $query->whereIn('user_id', $followingIds)
                                 ->orWhere('user_id', $user->id);
                       })
                       ->where('disliked', false) // Filter out disliked shares
                       ->with(['user', 'likes'])
                       ->latest()
                       ->paginate(20);

        // Fetch recommended shares
        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = collect();

        if (!empty($rawRecommendations)) {
            $recommendedShareIds = collect($rawRecommendations)->pluck('share_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('share_id');

            $recommendedShares = Share::whereIn('id', $recommendedShareIds)
                                      ->where('disliked', false) // Filter out disliked recommended shares
                                      ->get();

            // Sort the recommended shares by score
            $recommendedShares = $recommendedShares->sortByDesc(function ($share) use ($recommendationData) {
                return $recommendationData[$share->id]['score'] ?? 0;
            });

            $recommendedShares = $recommendedShares->map(function ($share) use ($recommendationData) {
                $share->reason = $recommendationData[$share->id]['reason'] ?? 'Based on your taste';
                return $share;
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

        return view('dashboard', [
            'shares' => $shares,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
        ]);
    }
}