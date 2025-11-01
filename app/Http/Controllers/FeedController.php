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
        $shares = Share::whereIn('user_id', $followingIds)
                       ->orWhere('user_id', $user->id)
                       ->with(['user', 'likes', 'comments' => function ($query) {
                           $query->whereNotIn('id', function ($query) {
                               $query->select('comment_id')->from('comment_threads');
                           })->orderBy('created_at', 'asc');
                       }, 'comments.user', 'comments.replies' => function ($query) {
                           $query->orderBy('created_at', 'asc');
                       }, 'comments.replies.user'])
                       ->latest()
                       ->paginate(20);

        // Fetch recommended shares
        $recommendedShareIds = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

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
