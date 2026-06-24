<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all_count = DB::select("select count(*) as cnt from songs")[0]->cnt;
$preview_count = DB::select("select count(*) as cnt from songs where preview_url is not null and preview_url != ''")[0]->cnt;

echo "All songs: $all_count\n";
echo "Songs with preview_url: $preview_count\n";
