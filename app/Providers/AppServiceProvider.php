<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;

use Illuminate\Support\Facades\URL;

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
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(
            SocialiteWasCalled::class,
            SpotifyExtendSocialite::class
        );

        View::composer('*', function ($view) {
            // User Data
            if (Auth::check()) {
                $user = Auth::user();
                if ($user instanceof \App\Models\User) {
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

                    $recommendedSongs = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_recommended_songs", 600, function () use ($user) {
                        $recommendationService = app(\App\Services\RecommendationService::class);
                        $rawRecommendations = $recommendationService->getRecommendations($user->id);
                        if (empty($rawRecommendations)) return collect();
                        $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
                        $interactedSongIds = $user->songInteractions()->pluck('song_id')->toArray();
                        $filteredSongIds = array_diff($recommendedSongIds, $interactedSongIds);
                        if (empty($filteredSongIds)) return collect();
                        $recommendationData = collect($rawRecommendations)->keyBy('song_id');
                        $songs = \App\Models\Song::whereIn('id', $filteredSongIds)->get();
                        return $songs->map(function ($song) use ($recommendationData) {
                            $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                            $song->score = $recommendationData[$song->id]['score'] ?? 0;
                            return $song;
                        })->sortByDesc('score');
                    });
                    $view->with('recommendedSongs', $recommendedSongs);
                }
            }

            // Admin Data
            if (Auth::guard('admin')->check()) {
                $admin = Auth::guard('admin')->user();
                $view->with('adminNotifications', $admin->notifications()->take(5)->get());
                $view->with('adminUnreadCount', $admin->unreadNotifications()->count());
            }
        });
    }
}
