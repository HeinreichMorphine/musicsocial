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

    public function show(User $user)
    {

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

    public function taste(User $user)
    {

        // 1. GENRE DNA & ARTIST COLLECTION
        // Collect songs from Shares, Likes, and Interaction History (Listened/Liked)
        $sharedSongs = $user->shares()->with('song')->get()->pluck('song');
        $likedSongs = $user->likes()->with('song')->get()->pluck('song');
        
        // Fetch song interactions where type is NOT dislike
        $historySongs = $user->songInteractions()
            ->where('type', '!=', 'dislike')
            ->with('song')
            ->get()
            ->pluck('song');

        // Merge all sources
        $allSongs = $sharedSongs->merge($likedSongs)->merge($historySongs)->unique('id');

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
                    $genre = strtolower(trim($genre));
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
        
        $totalTopGenreCount = array_sum($topGenres);
        
        $userArtistKeys = array_keys($artistCounts);
        $userSongIds = $allSongs->pluck('id')->toArray();
        $userGenreKeys = array_keys($genreCounts);
        
        // Normalize to percentages (Total of Top 5 should be 100%)
        $genreDna = [];
        foreach ($topGenres as $genre => $count) {
            $genreDna[$genre] = [
                'percent' => $totalTopGenreCount > 0 ? round(($count / $totalTopGenreCount) * 100) : 0,
            ];
        }

        // 2. TASTE TWINS (Artist Based)
        // Enhanced: Now includes Shares, Likes, and Listening History (Song Interactions)
        $tasteTwins = collect();

        if (!empty($userArtistKeys)) {
            // Find users who have shared/liked/listened to songs by these artists
            $potentialTwins = User::where('id', '!=', $user->id)
                ->where(function($query) use ($userArtistKeys) {
                    $query->whereHas('shares.song', function($q) use ($userArtistKeys) {
                        $q->whereIn('artist_name', $userArtistKeys);
                    })
                    ->orWhereHas('songInteractions', function($q) use ($userArtistKeys) {
                        $q->where('type', '!=', 'dislike')
                          ->whereHas('song', function($sq) use ($userArtistKeys) {
                              $sq->whereIn('artist_name', $userArtistKeys);
                          });
                    });
                })
                ->with(['shares.song' => function($q) {
                    $q->select('id', 'artist_name', 'genres', 'track_name');
                }, 'likes.song' => function($q) {
                    $q->select('id', 'artist_name', 'genres', 'track_name');
                }, 'songInteractions.song' => function($q) {
                    $q->select('id', 'artist_name', 'genres', 'track_name');
                }])
                ->limit(20)
                ->get();

            $scoredTwins = [];
            foreach ($potentialTwins as $twin) {
                // Collect twin's data
                $twinSongs = $twin->shares->pluck('song')->merge($twin->likes->pluck('song'))->merge($twin->songInteractions->where('type', '!=', 'dislike')->pluck('song'))->unique('id');
                
                $twinSongIds = $twinSongs->pluck('id')->toArray();
                $twinArtists = $twinSongs->pluck('artist_name')->unique()->filter()->toArray();
                
                $twinGenres = [];
                foreach ($twinSongs as $ts) {
                    if ($ts->genres) {
                        $clean = str_replace(['[', ']', '"', "'"], '', $ts->genres);
                        $gs = array_map('strtolower', array_map('trim', explode(',', $clean)));
                        $twinGenres = array_merge($twinGenres, $gs);
                    }
                }
                $twinGenres = array_unique(array_filter($twinGenres));

                // --- MULTI-DIMENSIONAL INTERSECTION ---
                $commonSongs = array_intersect($userSongIds, $twinSongIds);
                $commonArtists = array_intersect($userArtistKeys, $twinArtists);
                $commonGenres = array_intersect($userGenreKeys, $twinGenres);

                $songMatchCount = count($commonSongs);
                $artistMatchCount = count($commonArtists);
                $genreMatchCount = count($commonGenres);

                if ($songMatchCount > 0 || $artistMatchCount > 0) {
                    // WEIGHTED SCORING
                    // Song Match: 4 pts | Artist Match: 2 pts | Genre Match: 1 pt
                    $rawScore = ($songMatchCount * 4) + ($artistMatchCount * 2) + ($genreMatchCount * 1);
                    
                    // Normalization Factor (Based on user's unique footprint)
                    $userFootprint = (count($userSongIds) * 4) + (count($userArtistKeys) * 2) + (count($userGenreKeys) * 1);
                    
                    $matchScore = $userFootprint > 0 ? round(($rawScore / $userFootprint) * 100) : 0;
                    if ($matchScore > 100) $matchScore = 100;
                    if ($matchScore < 1) $matchScore = 1; // Minimum floor for visibility

                    // DYNAMIC COMMON GROUND TEXT
                    if ($songMatchCount > 0) {
                        $randomSong = Song::find(collect($commonSongs)->random());
                        $commonGround = "You both enjoy " . ($randomSong->track_name ?? 'the same songs');
                    } elseif ($artistMatchCount > 0) {
                        $commonGround = "You both enjoy " . collect($commonArtists)->random();
                    } else {
                        $commonGround = "You both enjoy " . collect($commonGenres)->random();
                    }

                    $twin->match_score = $matchScore;
                    $twin->common_ground = $commonGround;
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
                  $g = strtolower(trim($g));
                  if ($g) {
                      if (!isset($genreCounts[$g])) $genreCounts[$g] = 0;
                      $genreCounts[$g]++;
                  }
              }
         }
         arsort($genreCounts);
         return array_key_first($genreCounts);
    }

    public function saved(User $user)
    {

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

    public function shelf(User $user)
    {

        $user->load(['shelfSongs' => function($q) {
            $q->orderBy('position');
        }]);

        // Map shelf songs to Spotify-like structure for the frontend
        $shelfTracks = $user->shelfSongs->map(function($ss) {
            $song = Song::where('spotify_track_id', $ss->song_id)->first();
            if (!$song) return null;
            
            return [
                'id' => $song->spotify_track_id,
                'name' => $song->track_name,
                'artists' => [['name' => $song->artist_name]],
                'album' => [
                    'images' => [['url' => $song->album_art_url]]
                ],
                'external_urls' => ['spotify' => $song->spotify_url]
            ];
        })->filter()->values();

        // Badge Calculation
        $topGenre = $this->getTopGenre($user);
        $badge = $topGenre ? "🏆 " . ucfirst($topGenre) . " Lover" : "🎧 Music Explorer";

        // Sidebar Data
        $currentUser = Auth::user();
        $usersToSuggest = User::where('id', '!=', $currentUser->id)->limit(5)->get();
        $recommendedSongs = collect(); 

        return view('profile.shelf', [
            'user' => $user,
            'shelfTracks' => $shelfTracks,
            'badge' => $badge,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,
        ]);
    }
}
