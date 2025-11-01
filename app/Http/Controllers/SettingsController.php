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

        $recommendedShareIds = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

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
