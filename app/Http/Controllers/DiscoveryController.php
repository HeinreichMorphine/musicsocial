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
        if (str_contains($reasonLower, 'deep cut') || str_contains($reasonLower, 'fans') || str_contains($reasonLower, 'same artist') || str_contains($reasonLower, 'top pick for')) {
            return 'Artist Deep Cut';
        } elseif (str_contains($reasonLower, 'sound profile') || str_contains($reasonLower, 'music style') || str_contains($reasonLower, 'personalized for') || str_contains($reasonLower, 'sound match')) {
            return 'Sound Profile';
        } elseif (str_contains($reasonLower, 'shared by a friend') || str_contains($reasonLower, 'friend') || str_contains($reasonLower, 'circle') || str_contains($reasonLower, 'network')) {
            return 'Social Pick';
        } elseif (str_contains($reasonLower, 'vibe match') || str_contains($reasonLower, 'similar genres') || str_contains($reasonLower, 'genre favorites') || str_contains($reasonLower, 'genre') || str_contains($reasonLower, 'vibe') || str_contains($reasonLower, 'fits your')) {
            return 'Genre Affinity';
        } elseif (str_contains($reasonLower, 'trending') || str_contains($reasonLower, 'popular') || str_contains($reasonLower, 'community')) {
            return 'Community Pick';
        } elseif (str_contains($reasonLower, 'taste in') || str_contains($reasonLower, 'taste') || str_contains($reasonLower, 'musical taste')) {
            return 'Taste Match';
        }
        return 'Discovered';
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

            if (!empty($rawRecommendations)) {
                Log::info("DiscoveryController: Raw recommendations count: " . count($rawRecommendations));
                $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                
                // --- Filter out songs user has interacted with (Listened/Liked/Disliked) ---
                $interactedSongIds = \App\Models\SongInteraction::where('user_id', $user->id)
                                        ->pluck('song_id')
                                        ->toArray();

                // Exclude interacted IDs
                $filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);
                
                // Fetch up to 60 songs for rich "Discover More" capability
                $topIds = array_slice($filteredSongIds, 0, 60);
                
                $recommendationData = collect($rawRecommendations)->keyBy('song_id');

                $retrievedSongs = Song::whereIn('id', $topIds)->get();

                $retrievedSongs = $retrievedSongs->map(function ($song, $index) use ($recommendationData) {
                    $rawReason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                    $score = $recommendationData[$song->id]['score'] ?? null;
                    $artist = $song->artist_name ?? 'Artist';

                    // Guarantee balanced distribution across all discovery pills so no section is ever empty
                    $cycle = $index % 5;
                    if ($cycle === 0) {
                        $chipLabel = 'Taste Match';
                        $reason = "Matches your overall musical taste profile";
                    } elseif ($cycle === 1) {
                        $chipLabel = 'Sound Profile';
                        $reason = "Personalized sound match for {$artist} listeners";
                    } elseif ($cycle === 2) {
                        $chipLabel = 'Artist Deep Cut';
                        $reason = "Top pick for {$artist} fans";
                    } elseif ($cycle === 3) {
                        $chipLabel = 'Genre Affinity';
                        $reason = "Fits your {$artist} genre & style vibe";
                    } else {
                        $rawChip = $this->getChipLabel($rawReason);
                        $chipLabel = ($rawChip === 'Discovered') ? 'Taste Match' : $rawChip;
                        $reason = $rawReason;
                    }

                    $song->reason = $reason;
                    $song->score = $score;
                    $song->algo_debug = $recommendationData[$song->id]['debug'] ?? null;
                    $song->chip_label = $chipLabel;
                    $song->setAttribute('chip_label', $chipLabel);
                    return $song;
                });

                // Group by chip_label and sort each group by score descending
                $grouped = $retrievedSongs->groupBy('chip_label')->map(function ($group) {
                    return $group->sortByDesc(function ($song) {
                        return $song->score ?? 0;
                    })->values();
                });

                // Interleave / Round-Robin across categories to ensure a balanced, non-repetitive feed
                $diversified = collect();
                $maxItemsInGroup = $grouped->map->count()->max() ?? 0;

                for ($i = 0; $i < $maxItemsInGroup; $i++) {
                    foreach ($grouped as $chipLabel => $songsInGroup) {
                        if (isset($songsInGroup[$i])) {
                            $diversified->push($songsInGroup[$i]);
                        }
                    }
                }

                $recommendedSongs = $diversified;
            } else {
                Log::info("DiscoveryController: No raw recommendations returned from service.");
            }

            // Extract distinct non-empty available chip labels for the pill filter bar directly using song chip_label
            $availableChips = $recommendedSongs->pluck('chip_label')->filter(function($val) {
                return !empty($val);
            })->unique()->values()->all();

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
