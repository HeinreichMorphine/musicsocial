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
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = \App\Models\Song::whereIn('id', $recommendedSongIds)->get();

            // Sort the recommended songs by score
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });

            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
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
            'recommendedSongs' => $recommendedSongs,
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
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = \App\Models\Song::whereIn('id', $recommendedSongIds)->get();

            // Sort the recommended songs by score
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });

            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
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
            'recommendedSongs' => $recommendedSongs,
            'usersToSuggest' => $usersToSuggest,
        ]);
    }
}
