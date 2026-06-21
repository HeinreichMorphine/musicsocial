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

// Onboarding Routes
Route::middleware(['auth'])->name('onboarding.')->group(function () {
    Route::get('/onboarding/genres', [App\Http\Controllers\OnboardingController::class, 'genres'])->name('genres');
    Route::post('/onboarding/genres', [App\Http\Controllers\OnboardingController::class, 'store'])->name('genres.store');
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
        Route::delete('playlists/{id}', [AdminController::class, 'deletePlaylist'])->name('playlists.delete');
        Route::get('retrain', [AdminController::class, 'retrainPage'])->name('retrain.page');
        Route::post('retrain', [AdminController::class, 'retrainProcess'])->name('retrain.process');
        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::post('profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
        
        // Admin Management
        Route::get('admins', [AdminController::class, 'admins'])->name('admins.index');
        Route::post('admins', [AdminController::class, 'storeAdmin'])->name('admins.store');
        Route::delete('admins/{id}', [AdminController::class, 'deleteAdmin'])->name('admins.destroy');
        Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('notifications.markAllRead');
        Route::get('algo-test-suite', [AdminController::class, 'algoTestSuite'])->name('algo-test-suite');
        Route::get('algo-test-suite/frame', [AdminController::class, 'algoTestSuiteFrame'])->name('algo-test-suite.frame');
        Route::any('algo-test-suite/api/{endpoint?}', [AdminController::class, 'algoTestSuiteApi'])
            ->where('endpoint', '.*')
            ->name('algo-test-suite.api');
    });
});

// Routes accessible during onboarding (Auth only, no Onboarding check)
Route::middleware(['auth'])->group(function () {
    Route::get('/search/tracks', [SpotifySearchController::class, 'search'])->name('spotify.search');
    Route::get('/search/tracks/{id}', [SpotifySearchController::class, 'show'])->name('spotify.show');
});

// Breeze's default dashboard, let's rename it to our Feed
Route::get('/dashboard', [FeedController::class, 'index'])
    ->middleware(['auth', App\Http\Middleware\CheckOnboarding::class])->name('dashboard');

Route::middleware(['auth', App\Http\Middleware\CheckOnboarding::class])->group(function () {
    // Breeze's profile routes (for editing your own profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture.update');
    Route::patch('/profile/banner', [ProfileController::class, 'updateBanner'])->name('profile.banner.update');
    // Our new routes
    Route::resource('shares', ShareController::class)->only(['store', 'destroy', 'show', 'update']);
    Route::resource('shares.comments', CommentController::class)->only(['store', 'destroy', 'update']);
    Route::post('/shares/{share}/comments/{comment}/upvote', [CommentController::class, 'toggleUpvote'])->name('shares.comments.upvote');
    Route::post('/shares/{share}/like', [LikeController::class, 'toggle'])->name('shares.like');
    Route::post('/shares/{share}/dislike', [ShareController::class, 'toggleDislike'])->name('shares.dislike');
    Route::post('/shares/{share}/bookmark', [ShareController::class, 'toggleBookmark'])->name('shares.bookmark');
    
    // Playlists routes
    Route::resource('playlists', App\Http\Controllers\PlaylistController::class)->only(['index', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::delete('/playlists/{playlist}/songs/{songId}', [App\Http\Controllers\PlaylistController::class, 'removeSong'])->name('playlists.remove-song');
    Route::post('/playlists/{playlist}/invite', [App\Http\Controllers\PlaylistController::class, 'invite'])->name('playlists.invite');
    Route::post('/playlists/{playlist}/accept', [App\Http\Controllers\PlaylistController::class, 'acceptInvite'])->name('playlists.accept');
    Route::post('/playlists/{playlist}/decline', [App\Http\Controllers\PlaylistController::class, 'declineInvite'])->name('playlists.decline');
    Route::post('/playlists/{playlist}/songs', [App\Http\Controllers\PlaylistController::class, 'addSong'])->name('playlists.add-song');
    Route::post('/playlists/{playlist}/cover', [App\Http\Controllers\PlaylistController::class, 'updateCover'])->name('playlists.update-cover');

    // JSON endpoint for the "Add to MusicSocial Playlist" modal
    Route::get('/api/playlists/mine', function () {
        $user = auth()->user();
        return \App\Models\Playlist::whereHas('collaborators', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'accepted');
        })->withCount('songs')->get(['id', 'name', 'cover_image_url']);
    })->name('api.playlists.mine');
    
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
    Route::get('/users/{user:name}/shelf', [UserProfileController::class, 'shelf'])->name('profile.shelf');
    Route::get('/users/{user:name}/taste', [UserProfileController::class, 'taste'])->name('profile.taste');
    Route::get('/users/{user:name}/saved', [UserProfileController::class, 'saved'])->name('profile.saved');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Spotify search routes
    Route::get('/spotify/recently-played', [SpotifySearchController::class, 'recentlyPlayed'])->name('spotify.recently-played');

    // Spotify Playlist Actions
    Route::get('/spotify/playlists', [App\Http\Controllers\SpotifyPlaylistController::class, 'index'])->name('spotify.playlists.index');
    Route::post('/spotify/playlists/add', [App\Http\Controllers\SpotifyPlaylistController::class, 'addTrack'])->name('spotify.playlists.add');
    Route::post('/spotify/playlists/create', [App\Http\Controllers\SpotifyPlaylistController::class, 'create'])->name('spotify.playlists.create');
    Route::post('/export-playlist', [App\Http\Controllers\PlaylistExportController::class, 'export'])->name('export-playlist');
    
    // Spotify Selective Import
    Route::get('/playlists/import/spotify', [App\Http\Controllers\SpotifyImportController::class, 'index'])->name('playlists.import.index');
    Route::post('/playlists/import/spotify/preview', [App\Http\Controllers\SpotifyImportController::class, 'preview'])->name('playlists.import.preview');
    Route::post('/playlists/import/spotify/process', [App\Http\Controllers\SpotifyImportController::class, 'process'])->name('playlists.import.process');
    Route::get('/spotify/search-playlists', [App\Http\Controllers\SpotifyImportController::class, 'searchSpotifyPlaylists'])->name('spotify.search-playlists');

    // Settings route
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Song Shelf CRUD
    Route::post('/shelf/add', [App\Http\Controllers\UserShelfController::class, 'add'])->name('shelf.add');
    Route::delete('/shelf/{songId}', [App\Http\Controllers\UserShelfController::class, 'remove'])->name('shelf.remove');
    Route::post('/shelf/reorder', [App\Http\Controllers\UserShelfController::class, 'reorder'])->name('shelf.reorder');

    // Followers and Following routes
    Route::get('/users/{user}/followers', [FollowerController::class, 'followers'])->name('profile.followers');
    Route::get('/users/{user}/following', [FollowerController::class, 'following'])->name('profile.following');
});

// Social Auth Routes
Route::get('auth/{provider}', [App\Http\Controllers\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('social.callback');
Route::post('auth/{provider}/unlink', [App\Http\Controllers\SocialAuthController::class, 'unlink'])->middleware('auth')->name('social.unlink');


Route::get('/discovery', [App\Http\Controllers\DiscoveryController::class, 'index'])->middleware(['auth', App\Http\Middleware\CheckOnboarding::class])->name('discovery');

// Debug Route (Temporary)
Route::get('/debug-auth', function () {
    return [
        'spotify' => config('services.spotify'),
        'google' => config('services.google'),
        'audiodb' => config('services.audiodb'),
    ];
});

require __DIR__.'/auth.php';
