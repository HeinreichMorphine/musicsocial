<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if (!$user) {
    echo "No user found.\n";
    exit;
}

$request = Illuminate\Http\Request::create('/onboarding/genres', 'POST', [
    'song_ids' => ['3n3Ppam7vgaBg1sNZFvdI3', '4eLsqA9NnWevQ2iO9qKk9L', '0G8nJ0zW48n52n3FovdY04']
]);
$request->setUserResolver(fn() => $user);

try {
    $response = app(App\Http\Controllers\OnboardingController::class)->store($request);
    echo "SUCCESS: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
