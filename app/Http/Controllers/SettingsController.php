<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Display the user's settings form.
     */
    public function index(): View
    {
        $user = Auth::user();

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

        $usersToSuggest = User::where('id', '!=', $user->id)
                            ->whereDoesntHave('followers', function ($query) use ($user) {
                                $query->where('follower_id', $user->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        return view('settings.index', compact('recommendedShares', 'usersToSuggest'));
    }
}
