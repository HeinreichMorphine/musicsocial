<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\MusicBrainzService::class, function ($app) {
            return new \App\Services\MusicBrainzService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // 1. Users to Suggest (Existing Logic)
                $usersToSuggest = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_suggested_users", 300, function () use ($user) {
                    return User::where('id', '!=', $user->id)
                                    ->whereDoesntHave('followers', function ($query) use ($user) {
                                        $query->where('follower_id', $user->id);
                                    })
                                    ->inRandomOrder()
                                    ->limit(5)
                                    ->get();
                });
                $view->with('usersToSuggest', $usersToSuggest);

                // 2. Recommended Songs (New Logic for Sidebar)
                $recommendedSongs = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_recommended_songs", 600, function () use ($user) {
                    $recommendationService = app(\App\Services\RecommendationService::class);
                    $rawRecommendations = $recommendationService->getRecommendations($user->id);
                    
                    if (empty($rawRecommendations)) {
                        return collect();
                    }

                    $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                    $recommendationData = collect($rawRecommendations)->keyBy('song_id');

                    $songs = \App\Models\Song::whereIn('id', $recommendedSongIds)->get();

                    // Sort and add metadata
                    return $songs->map(function ($song) use ($recommendationData) {
                        $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                        $song->score = $recommendationData[$song->id]['score'] ?? 0;
                        return $song;
                    })->sortByDesc('score');
                });
                
                $view->with('recommendedSongs', $recommendedSongs);

            } else {
                $view->with('usersToSuggest', collect());
                $view->with('recommendedSongs', collect());
            }
        });
    }
}
