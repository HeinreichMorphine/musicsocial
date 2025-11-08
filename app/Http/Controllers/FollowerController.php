<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles the display of a user's followers and the users they are following.
 */
class FollowerController extends Controller
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
     * Display a paginated list of a user's followers.
     *
     * This method fetches a paginated list of the specified user's followers.
     * It also includes recommended shares and user suggestions for the authenticated user.
     *
     * @param  \App\Models\User  $user The user whose followers are to be displayed.
     * @return \Illuminate\View\View
     */
    public function followers(User $user)
    {
        $currentUser = Auth::user();

        $rawRecommendations = $this->recommendationService->getRecommendations($currentUser->id);
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

        $usersToSuggest = User::where('id', '!=', $currentUser->id)
                            ->whereDoesntHave('followers', function ($query) use ($currentUser) {
                                $query->where('follower_id', $currentUser->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        $followers = $user->followers()->paginate(10);
        return view('profile.followers', [
            'user' => $user,
            'followers' => $followers,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
        ]);
    }

    /**
     * Display a paginated list of users that the specified user is following.
     *
     * This method fetches a paginated list of the users that the specified user is currently following.
     * It also includes recommended shares and user suggestions for the authenticated user.
     *
     * @param  \App\Models\User  $user The user whose followed users are to be displayed.
     * @return \Illuminate\View\View
     */
    public function following(User $user)
    {
        $currentUser = Auth::user();

        $rawRecommendations = $this->recommendationService->getRecommendations($currentUser->id);
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

        $usersToSuggest = User::where('id', '!=', $currentUser->id)
                            ->whereDoesntHave('followers', function ($query) use ($currentUser) {
                                $query->where('follower_id', $currentUser->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        $following = $user->following()->paginate(10);
        return view('profile.following', [
            'user' => $user,
            'following' => $following,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
        ]);
    }
}
