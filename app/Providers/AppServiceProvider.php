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
                $usersToSuggest = User::where('id', '!=', $user->id)
                                    ->whereDoesntHave('followers', function ($query) use ($user) {
                                        $query->where('follower_id', $user->id);
                                    })
                                    ->inRandomOrder()
                                    ->limit(5)
                                    ->get();
                $view->with('usersToSuggest', $usersToSuggest);
            } else {
                $view->with('usersToSuggest', collect());
            }
        });
    }
}
