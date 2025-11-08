<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the display of the user's main feed, which includes shares from followed users,
 * recommended content, and suggestions for new users to follow.
 */
class FeedController extends Controller
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
     * Display the user's main feed.
     *
     * This method assembles the main dashboard feed for the authenticated user. It fetches a paginated
     * list of shares from the user and the people they follow, recommended shares from the
     * `RecommendationService`, and a list of users to suggest following. This data is then
     * passed to the `dashboard` view.
     *
     * @return \Illuminate\View\View
     */
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
                       ->whereDoesntHave('dislikes', function ($query) use ($user) {
                           $query->where('user_id', $user->id);
                       })
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
                                      ->whereDoesntHave('dislikes', function ($query) use ($user) {
                                          $query->where('user_id', $user->id);
                                      })
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