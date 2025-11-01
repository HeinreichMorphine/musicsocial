<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Share;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function show($user)
    {
        if (is_string($user)) {
            $user = User::where('name', 'like', "%{$user}%")->first();

            if (!$user) {
                return redirect()->back()->withErrors(['error' => 'User not found.']);
            }
        }

        $user->load('shares.user'); // Eager load shares and their associated users

        $currentUser = Auth::user();
        $recommendedShareIds = $this->recommendationService->getRecommendations($currentUser->id);
        $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

        $usersToSuggest = User::where('id', '!=', $currentUser->id)
                            ->whereDoesntHave('followers', function ($query) use ($currentUser) {
                                $query->where('follower_id', $currentUser->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();

        return view('profile.show', [
            'user' => $user,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
        ]);
    }
}
