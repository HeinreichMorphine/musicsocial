<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SpotifySearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\UserSearchController;

Route::get('/', function () {
    return view('welcome');
});

// Breeze's default dashboard, let's rename it to our Feed
Route::get('/dashboard', [FeedController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Breeze's profile routes (for editing your own profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture.update');
    // Our new routes
    Route::resource('shares', ShareController::class)->only(['store', 'destroy']);
    Route::resource('shares.comments', CommentController::class)->only(['store', 'destroy', 'update']);
    Route::post('/shares/{share}/like', [LikeController::class, 'toggle'])->name('shares.like');
    Route::post('/shares/{share}/dislike', [ShareController::class, 'toggleDislike'])->name('shares.dislike');

    // User search route
    Route::get('/users/search', [UserSearchController::class, 'index'])->name('user.search');

    // Public profiles
    Route::get('/users/{user:name}', [UserProfileController::class, 'show'])->name('profile.show');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Spotify search routes
    Route::get('/search/tracks', [SpotifySearchController::class, 'search'])->name('spotify.search');


    // Settings route
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // User profile route
    Route::get('/profile/{user}', [UserProfileController::class, 'show'])->name('user.profile');

    // Followers and Following routes
    Route::get('/profile/{user}/followers', [FollowerController::class, 'followers'])->name('profile.followers');
    Route::get('/profile/{user}/following', [FollowerController::class, 'following'])->name('profile.following');
});

Route::get('/discovery', [App\Http\Controllers\DiscoveryController::class, 'index'])->middleware(['auth', 'verified'])->name('discovery');

require __DIR__.'/auth.php';
