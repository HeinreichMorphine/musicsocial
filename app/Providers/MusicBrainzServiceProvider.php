<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MusicBrainzService;

class MusicBrainzServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MusicBrainzService::class, function ($app) {
            return new MusicBrainzService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
