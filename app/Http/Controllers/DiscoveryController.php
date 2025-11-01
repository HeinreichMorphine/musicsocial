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
            // Fetch recommendations from the Python service
            $recommendations = $this->recommendationService->getRecommendations($user->id);

            if ($recommendations) {
                $recommendedShareIds = array_column($recommendations, 'item_id');
                $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();
            }

            // Fetch users to suggest (e.g., users not followed by the current user)
            $usersToSuggest = User::where('id', '!=', $user->id)
                                ->whereDoesntHave('followers', function ($query) use ($user) {
                                    $query->where('follower_id', $user->id);
                                })
                                ->inRandomOrder()
                                ->limit(5)
                                ->get();
        }

        return view('discovery', compact('recommendedShares', 'usersToSuggest'));
    }
}
