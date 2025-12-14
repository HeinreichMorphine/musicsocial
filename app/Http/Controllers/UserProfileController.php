<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Share;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Song;
use Illuminate\Support\Facades\DB;

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

        // Calculate Badge
        $topGenre = $this->getTopGenre($user);
        $badge = $topGenre ? "🏆 " . ucfirst($topGenre) . " Lover" : "🎧 Music Explorer";

        return view('profile.show', [
            'user' => $user,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
            'badge' => $badge
        ]);
    }

    public function taste($user)
    {
        if (is_string($user)) {
             $userModel = User::where('name', 'like', "%{$user}%")->first();
             if (!$userModel) {
                 return redirect()->back()->withErrors(['error' => 'User not found.']);
             }
             $user = $userModel;
        }

        // 1. GENRE DNA & ARTIST COLLECTION
        // Collect songs from Shares and Like
        $sharedSongs = $user->shares()->with('song')->get()->pluck('song');
        $likedSongs = $user->likes()->with('song')->get()->pluck('song');
        $allSongs = $sharedSongs->merge($likedSongs);

        $genreCounts = [];
        $artistCounts = [];
        $songsWithGenresCount = 0;

        foreach ($allSongs as $song) {
            if (!$song) continue;

            // Artists
            if ($song->artist_name) {
                $artist = $song->artist_name;
                if (!isset($artistCounts[$artist])) {
                    $artistCounts[$artist] = 0;
                }
                $artistCounts[$artist]++;
            }

            // Genres
            if ($song->genres) {
                // Genres are stored as "['pop', 'rock']" string or just "pop, rock" depending on seeder.
                $cleanGenres = str_replace(['[', ']', '"', "'"], '', $song->genres);
                $genres = explode(',', $cleanGenres);
                
                $songHasGenre = false;
                foreach ($genres as $genre) {
                    $genre = trim($genre);
                    if (empty($genre)) continue;
                    
                    if (!isset($genreCounts[$genre])) {
                        $genreCounts[$genre] = 0;
                    }
                    $genreCounts[$genre]++;
                    $songHasGenre = true;
                }
                if ($songHasGenre) {
                     $songsWithGenresCount++;
                }
            }
        }

        arsort($genreCounts);
        $topGenres = array_slice($genreCounts, 0, 5, true);
        $userArtistKeys = array_keys($artistCounts);
        
        // Normalize to percentages (of Songs)
        $genreDna = [];
        foreach ($topGenres as $genre => $count) {
            $genreDna[$genre] = [
                'percent' => $songsWithGenresCount > 0 ? round(($count / $songsWithGenresCount) * 100) : 0,
            ];
        }

        // 2. TASTE TWINS (Artist Based)
        $tasteTwins = collect();

        if (!empty($userArtistKeys)) {
            // Find users who have shared/liked songs by these artists
            // We'll inspect Shares only for efficiency in this simplified version
            $potentialTwins = User::where('id', '!=', $user->id)
                ->whereHas('shares.song', function($q) use ($userArtistKeys) {
                    $q->whereIn('artist_name', $userArtistKeys);
                })
                ->with(['shares.song' => function($q) {
                    $q->select('id', 'artist_name');
                }])
                ->get();

            $scoredTwins = [];
            foreach ($potentialTwins as $twin) {
                // Get twin's artists
                $twinArtists = $twin->shares->pluck('song.artist_name')->unique()->toArray();
                
                // Calculate Intersection
                $commonArtists = array_intersect($userArtistKeys, $twinArtists);
                $commonCount = count($commonArtists);
                
                if ($commonCount > 0) {
                     // Simple Match Score: (Common / My Total) * 100
                     // This represents "How much of my taste do they cover?"
                     $myTotal = count($userArtistKeys);
                     $matchScore = $myTotal > 0 ? round(($commonCount / $myTotal) * 100) : 0;
                     
                     // Cap at 100 just in case
                     if ($matchScore > 100) $matchScore = 100;

                     // Pick a random common artist
                     // Pick a random common artist
                     $values = array_values($commonArtists);
                     $commonArtistName = $values[array_rand($values)];

                     $twin->match_score = $matchScore;
                     $twin->common_ground = "You both love " . $commonArtistName;
                     $scoredTwins[] = $twin;
                }
            }

            // Sort by match score
            usort($scoredTwins, function($a, $b) {
                return $b->match_score <=> $a->match_score;
            });

            $tasteTwins = collect(array_slice($scoredTwins, 0, 4));
        }

        // Top Genre Badge 
        $primaryGenre = array_key_first($topGenres);
        $badge = $primaryGenre ? "🏆 " . ucfirst($primaryGenre) . " Lover" : "🎧 Music Explorer";

        // Calculate Radar Values (Relative Intensity)
        // Normalize against the top genre count so the chart is always full
        $maxCount = !empty($topGenres) ? reset($topGenres) : 0;
        $radarValues = [];
        foreach ($topGenres as $genre => $count) {
             $radarValues[] = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0;
        }

        // Sidebar Data
        $currentUser = Auth::user();
        $usersToSuggest = User::where('id', '!=', $currentUser->id)
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();
        $recommendedSongs = collect(); 

        return view('profile.taste', [
            'user' => $user,
            'genreDna' => $genreDna,
            'tasteTwins' => $tasteTwins,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
            'badge' => $badge,
            'genreLabels' => array_keys($genreDna),
            'genreValues' => $radarValues, // Use relative intensity for radar
        ]);
    }
    private function getTopGenre($user)
    {
         $sharedGenres = DB::table('shares')
            ->join('songs', 'shares.song_id', '=', 'songs.id')
            ->where('shares.user_id', $user->id)
            ->pluck('songs.genres')
            ->toArray();
            
         $genreCounts = [];
         foreach ($sharedGenres as $genres) {
              if (!$genres) continue;
              $cleanGenres = str_replace(['[', ']', '"', "'"], '', $genres);
              $list = explode(',', $cleanGenres);
              foreach ($list as $g) {
                  $g = trim($g);
                  if ($g) {
                      if (!isset($genreCounts[$g])) $genreCounts[$g] = 0;
                      $genreCounts[$g]++;
                  }
              }
         }
         arsort($genreCounts);
         return array_key_first($genreCounts);
    }

    public function saved($user)
    {
        if (is_string($user)) {
             $userModel = User::where('name', 'like', "%{$user}%")->first();
             if (!$userModel) {
                 return redirect()->back()->withErrors(['error' => 'User not found.']);
             }
             $user = $userModel;
        }

        // Security Check: Only allow viewing own saved posts
        if (Auth::id() !== $user->id) {
            abort(403);
        }

        $shares = $user->bookmarks()->with(['song', 'user', 'likes', 'dislikes', 'comments.user'])->paginate(10);

        // Badge Calculation (reused)
        $topGenre = $this->getTopGenre($user);
        $badge = $topGenre ? "🏆 " . ucfirst($topGenre) . " Lover" : "🎧 Music Explorer";

        // Sidebar Data
        $currentUser = Auth::user();
        $usersToSuggest = User::where('id', '!=', $currentUser->id)
                            ->whereDoesntHave('followers', function ($query) use ($currentUser) {
                                $query->where('follower_id', $currentUser->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();
        
        $recommendations = $this->recommendationService->getRecommendations($currentUser->id);
        $recommendedSongIds = collect($recommendations)->pluck('song_id')->all();
        $recommendationData = collect($recommendations)->keyBy('song_id');
        $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();
        $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
            return $recommendationData[$song->id]['score'] ?? 0;
        });
        $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
            $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
            return $song;
        });

        return view('profile.saved', [
            'user' => $user,
            'shares' => $shares,
            'badge' => $badge,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
        ]);
    }
}
