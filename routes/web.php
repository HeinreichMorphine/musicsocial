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

use App\Http\Controllers\AdminController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Admin Login Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('login/admin', [AdminController::class, 'loginForm'])->name('admin.login');
    Route::post('login/admin', [AdminController::class, 'login']);
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('users', [AdminController::class, 'users'])->name('users');
        Route::patch('users/{id}/ban', [AdminController::class, 'banUser'])->name('users.ban');
        Route::delete('users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('moderation', [AdminController::class, 'moderation'])->name('moderation');
        Route::delete('shares/{id}', [AdminController::class, 'deleteShare'])->name('shares.delete');
        Route::delete('comments/{id}', [AdminController::class, 'deleteComment'])->name('comments.delete');
        Route::get('retrain', [AdminController::class, 'retrainPage'])->name('retrain.page');
        Route::post('retrain', [AdminController::class, 'retrainProcess'])->name('retrain.process');
        Route::post('retrain', [AdminController::class, 'retrainProcess'])->name('retrain.process');
        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::post('profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
        
        // Admin Management
        Route::get('admins', [AdminController::class, 'admins'])->name('admins.index');
        Route::post('admins', [AdminController::class, 'storeAdmin'])->name('admins.store');
        Route::delete('admins/{id}', [AdminController::class, 'deleteAdmin'])->name('admins.destroy');
    });
});

// Breeze's default dashboard, let's rename it to our Feed
Route::get('/dashboard', [FeedController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Breeze's profile routes (for editing your own profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture.update');
    Route::patch('/profile/banner', [ProfileController::class, 'updateBanner'])->name('profile.banner.update');
    // Our new routes
    Route::resource('shares', ShareController::class)->only(['store', 'destroy', 'show', 'update']);
    Route::resource('shares.comments', CommentController::class)->only(['store', 'destroy', 'update']);
    Route::post('/shares/{share}/like', [LikeController::class, 'toggle'])->name('shares.like');
    Route::post('/shares/{share}/dislike', [ShareController::class, 'toggleDislike'])->name('shares.dislike');
    Route::post('/shares/{share}/bookmark', [ShareController::class, 'toggleBookmark'])->name('shares.bookmark');
    
    // Song Interactions (Discovery)
    Route::post('/song-interactions', [App\Http\Controllers\SongInteractionController::class, 'store'])->name('song-interactions.store');

    // User search route
    Route::get('/users/search', [UserSearchController::class, 'index'])->name('user.search');
    
    // Notifications
    Route::post('/notifications/mark-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.markRead');
    
    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');
    
    // Mention autocomplete
    Route::get('/mentions/suggestions', [App\Http\Controllers\MentionController::class, 'suggestions'])->name('mentions.suggestions');

    // Public profiles
    Route::get('/users/{user:name}', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/users/{user:name}/taste', [UserProfileController::class, 'taste'])->name('profile.taste');
    Route::get('/users/{user:name}/saved', [UserProfileController::class, 'saved'])->name('profile.saved');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Spotify search routes
    Route::get('/search/tracks', [SpotifySearchController::class, 'search'])->name('spotify.search');
    Route::get('/spotify/recently-played', [SpotifySearchController::class, 'recentlyPlayed'])->name('spotify.recently-played');

    // Spotify Playlist Actions
    Route::get('/spotify/playlists', [App\Http\Controllers\SpotifyPlaylistController::class, 'index'])->name('spotify.playlists.index');
    Route::post('/spotify/playlists/add', [App\Http\Controllers\SpotifyPlaylistController::class, 'addTrack'])->name('spotify.playlists.add');
    Route::post('/spotify/playlists/create', [App\Http\Controllers\SpotifyPlaylistController::class, 'create'])->name('spotify.playlists.create');


    // Settings route
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // User profile route
    Route::get('/profile/{user}', [UserProfileController::class, 'show'])->name('user.profile');

    // Followers and Following routes
    Route::get('/profile/{user}/followers', [FollowerController::class, 'followers'])->name('profile.followers');
    Route::get('/profile/{user}/following', [FollowerController::class, 'following'])->name('profile.following');
});

// Social Auth Routes
Route::get('auth/{provider}', [App\Http\Controllers\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('social.callback');
Route::post('auth/{provider}/unlink', [App\Http\Controllers\SocialAuthController::class, 'unlink'])->middleware('auth')->name('social.unlink');


Route::get('/discovery', [App\Http\Controllers\DiscoveryController::class, 'index'])->middleware(['auth'])->name('discovery');

// Debug Route (Temporary)
Route::get('/debug-auth', function () {
    return [
        'spotify' => config('services.spotify'),
        'google' => config('services.google'),
        'audiodb' => config('services.audiodb'),
    ];
});

require __DIR__.'/auth.php';
