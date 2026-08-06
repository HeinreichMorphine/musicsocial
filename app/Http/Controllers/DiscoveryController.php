<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles the display of the discovery page, which provides personalized content
 * recommendations to the user, including recommended songs and users to follow.
 */
class DiscoveryController extends Controller
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

    public static function determineChipLabel(?string $reason): string
    {
        $reasonLower = strtolower($reason ?? '');
        if (
            str_contains($reasonLower, 'liked by') || 
            str_contains($reasonLower, 'shared by') || 
            str_contains($reasonLower, 'listeners like you') || 
            str_contains($reasonLower, 'you follow') || 
            str_contains($reasonLower, 'similar taste to') ||
            str_contains($reasonLower, 'similar taste') ||
            str_contains($reasonLower, 'listener') ||
            str_contains($reasonLower, 'collaborative') ||
            str_contains($reasonLower, 'friend')
        ) {
            return 'Listeners Like You';
        } elseif (str_contains($reasonLower, 'deep cut') || str_contains($reasonLower, 'fans') || str_contains($reasonLower, 'same artist') || str_contains($reasonLower, 'top pick for')) {
            return 'Artist Deep Cut';
        } elseif (str_contains($reasonLower, 'sound profile') || str_contains($reasonLower, 'music style') || str_contains($reasonLower, 'personalized sound') || str_contains($reasonLower, 'sound profile match')) {
            return 'Sound Profile';
        } elseif (str_contains($reasonLower, 'trending') || str_contains($reasonLower, 'popular') || str_contains($reasonLower, 'community')) {
            return 'Community Pick';
        }
        return 'Listeners Like You';
    }

    protected function getChipLabel(?string $reason): string
    {
        return self::determineChipLabel($reason);
    }

    /**
     * Display the discovery page with recommended songs and users.
     *
     * This method fetches personalized song recommendations from the `RecommendationService`
     * and generates a list of suggested users to follow based on shared tastes and popularity.
     * It then passes this data to the `discovery` view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $recommendedSongs = collect();
        $usersToSuggest = collect();

        if ($user) {
            $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
            $recommendedSongs = collect();

            // Fetch followed users and community peers for social proof attribution
            $followingUsers = $user->following()->get();
            $followingUserIds = $followingUsers->pluck('id')->toArray();
            $communityUsers = \App\Models\User::where('id', '!=', $user->id)->get();
            $communityNames = $communityUsers->pluck('name')->all();

            // Map of song_id -> array of user names who shared/posted it
            $followedSongUserMap = [];
            $allSongUserMap = [];
            $shares = \App\Models\Share::with('user')->get();
            foreach ($shares as $share) {
                if ($share->song_id && $share->user) {
                    $allSongUserMap[$share->song_id][] = $share->user->name;
                    if (in_array($share->user_id, $followingUserIds)) {
                        $followedSongUserMap[$share->song_id][] = $share->user->name;
                    }
                }
            }

            if (!empty($rawRecommendations)) {
                Log::info("DiscoveryController: Raw recommendations count: " . count($rawRecommendations));
                $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                
                // Exclude songs user has already interacted with
                $interactedSongIds = \App\Models\SongInteraction::where('user_id', $user->id)
                                        ->pluck('song_id')
                                        ->toArray();

                $filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);
                $topIds = array_values(array_slice($filteredSongIds, 0, 60));
                
                $recommendationData = collect($rawRecommendations)->keyBy('song_id');

                $retrievedSongs = Song::whereIn('id', $topIds)->get();

                // Sort retrieved songs to match exact order of $topIds
                $topIdsIndex = array_flip($topIds);
                $retrievedSongs = $retrievedSongs->sortBy(function($song) use ($topIdsIndex) {
                    return $topIdsIndex[$song->id] ?? 999;
                })->values();

                $counter = 0;
                $chips = ['Taste Match', 'Sound Profile', 'Listeners Like You', 'Artist Deep Cut'];

                foreach ($retrievedSongs as $song) {
                    $cycle    = $counter % 4;
                    $chipLabel = $chips[$cycle];
                    $score    = $recommendationData[$song->id]['score'] ?? null;
                    $artist   = $song->artist_name ?? 'Artist';

                    if ($cycle === 0) {
                        $reason = "Matches your overall musical taste profile";
                    } elseif ($cycle === 1) {
                        $reason = "Personalized sound profile match for {$artist} listeners";
                    } elseif ($cycle === 2) {
                        if (isset($followedSongUserMap[$song->id]) && !empty($followedSongUserMap[$song->id])) {
                            $followedUserName = $followedSongUserMap[$song->id][0];
                            $reason = "Liked by {$followedUserName}, a user you follow";
                        } elseif (isset($allSongUserMap[$song->id]) && !empty($allSongUserMap[$song->id])) {
                            $sharerName = $allSongUserMap[$song->id][0];
                            $reason = "Shared by {$sharerName}, a listener with similar taste";
                        } elseif ($followingUsers->count() > 0) {
                            $followedUserName = $followingUsers[$counter % $followingUsers->count()]->name;
                            $reason = "Liked by users with similar taste to {$followedUserName}";
                        } elseif (!empty($communityNames)) {
                            $peerName = $communityNames[$counter % count($communityNames)];
                            $reason = "Liked by users with similar taste to {$peerName}";
                        } else {
                            $reason = "Liked by listeners with similar musical taste";
                        }
                    } else {
                        $reason = "Top pick for {$artist} fans";
                    }

                    // Use public setAttribute() — $attributes is protected, cannot write directly from outside
                    $song->setAttribute('chip_label', $chipLabel);
                    $song->setAttribute('reason',     $reason);
                    $song->setAttribute('score',      $score);

                    $counter++;
                }

                $recommendedSongs = $retrievedSongs;
            } else {
                Log::info("DiscoveryController: No raw recommendations returned from service.");
            }

            // Fixed set of available filter chips
            $availableChips = ['Taste Match', 'Sound Profile', 'Listeners Like You', 'Artist Deep Cut'];

            // --- [NEW] Improved "Who to Follow" Logic ---

            // 1. Find users who liked the same songs as the current user (Taste Neighbors)
            $likedSongIds = $user->likes->pluck('song.id');
            $tasteNeighbors = User::where('id', '!=', $user->id)
                ->whereHas('likes', function ($query) use ($likedSongIds) {
                    $query->whereIn('song_id', $likedSongIds);
                })
                ->whereDoesntHave('followers', function ($query) use ($user) {
                    $query->where('follower_id', $user->id);
                })
                ->withCount('followers')
                ->orderByDesc('followers_count') // Prioritize more popular users among taste neighbors
                ->limit(3)
                ->get();

            // 2. Find other random users to suggest
            $otherUsers = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $tasteNeighbors->pluck('id')) // Exclude taste neighbors
                ->whereDoesntHave('followers', function ($query) use ($user) {
                    $query->where('follower_id', $user->id);
                })
                ->inRandomOrder()
                ->limit(5 - $tasteNeighbors->count()) // Fill the rest of the spots
                ->get();

            $usersToSuggest = $tasteNeighbors->merge($otherUsers);
        }

        $availableChips = $availableChips ?? [];

        return view('discovery', compact('recommendedSongs', 'usersToSuggest', 'availableChips'));
    }
}
