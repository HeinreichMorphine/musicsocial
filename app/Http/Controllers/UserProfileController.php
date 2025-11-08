<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Share;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Song;

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
        $recommendations = $this->recommendationService->getRecommendations($currentUser->id);
        $recommendedShareIds = collect($recommendations)->pluck('share_id')->all();
        $recommendationData = collect($recommendations)->keyBy('share_id');

        $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

        // Sort the recommended shares by score
        $recommendedShares = $recommendedShares->sortByDesc(function ($share) use ($recommendationData) {
            return $recommendationData[$share->id]['score'] ?? 0;
        });

        // Attach the reason to each share
        $recommendedShares = $recommendedShares->map(function ($share) use ($recommendationData) {
            $share->reason = $recommendationData[$share->id]['reason'] ?? 'Based on your taste';
            return $share;
        });

        $recommendedSongIds = collect($recommendations)->pluck('song_id')->all();
        $recommendationData = collect($recommendations)->keyBy('song_id');

        $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();

        // Sort the recommended songs by score
        $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
            return $recommendationData[$song->id]['score'] ?? 0;
        });

        $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
            $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
            return $song;
        });

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
            'recommendedSongs' => $recommendedSongs,
        ]);
    }
}
