<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\RetrainRecommender;
use App\Console\Commands\BackfillShareMetadata;
use App\Console\Commands\ClearSharesData;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:retrain-recommender', function () {
    $this->call(RetrainRecommender::class);
})->purpose('Retrain the recommendation model');

Artisan::command('app:backfill-share-metadata', function () {
    $this->call(BackfillShareMetadata::class);
})->purpose('Backfill metadata for existing shares');

Artisan::command('app:clear-shares-data', function () {
    $this->call(ClearSharesData::class);
})->purpose('Clear all shares data');
