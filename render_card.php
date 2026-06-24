<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Share;
use Illuminate\Support\Facades\Blade;

$share = Share::first();
if (!$share) {
    echo "No share found.\n";
    exit;
}

try {
    // Render the blade component
    $html = Blade::render('<x-share-card :share="$share" />', ['share' => $share]);
    echo "SUCCESS:\n";
    echo substr($html, 0, 2000) . "\n... TRUNCATED ...\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
