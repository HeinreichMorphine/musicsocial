<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Share;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Song;

class ProfileController extends Controller
{
    protected $recommendationService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\RecommendationService  $recommendationService
     * @return void
     */
    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Display the user's profile form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = collect();
        $recommendedSongs = collect();

        if (!empty($rawRecommendations)) {
            $recommendedShareIds = collect($rawRecommendations)->pluck('share_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('share_id');

            $recommendedShares = Share::whereIn('id', $recommendedShareIds)->get();

            // Sort the recommended shares by score
            $recommendedShares = $recommendedShares->sortByDesc(function ($share) use ($recommendationData) {
                return $recommendationData[$share->id]['score'] ?? 0;
            });

            $recommendedShares = $recommendedShares->map(function ($share) use ($recommendationData) {
                $share->reason = $recommendationData[$share->id]['reason'] ?? 'Based on your taste';
                return $share;
            });

            $recommendedSongIds = collect($rawRecommendations)->pluck('song_id')->all();
            $recommendationData = collect($rawRecommendations)->keyBy('song_id');

            $recommendedSongs = Song::whereIn('id', $recommendedSongIds)->get();

            // Sort the recommended songs by score
            $recommendedSongs = $recommendedSongs->sortByDesc(function ($song) use ($recommendationData) {
                return $recommendationData[$song->id]['score'] ?? 0;
            });

            $recommendedSongs = $recommendedSongs->map(function ($song) use ($recommendationData) {
                $song->reason = $recommendationData[$song->id]['reason'] ?? 'Based on your taste';
                return $song;
            });
        }

        $usersToSuggest = User::where('id', '!=', $user->id)
                            ->whereDoesntHave('followers', function ($query) use ($user) {
                                $query->where('follower_id', $user->id);
                            })
                            ->inRandomOrder()
                            ->limit(5)
                            ->get();



        return view('profile.edit', [
            'user' => $user,
            'recommendedShares' => $recommendedShares,
            'usersToSuggest' => $usersToSuggest,
            'recommendedSongs' => $recommendedSongs,

        ]);
    }

    /**
     * Update the user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's profile picture.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePicture(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('profile_picture')->store('profile-pictures', 'public');

        $request->user()->profile_picture = $path;
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-picture-updated');
    }

    /**
     * Update the user's cover photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBanner(Request $request): RedirectResponse
    {
        $request->validate([
            'cover_photo' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('cover_photo')->store('cover-photos', 'public');

        $request->user()->cover_photo_path = $path;
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'cover-photo-updated');
    }
}
