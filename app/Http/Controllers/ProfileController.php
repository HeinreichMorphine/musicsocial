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

class ProfileController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $rawRecommendations = $this->recommendationService->getRecommendations($user->id);
        $recommendedShares = collect();

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
        ]);
    }

    /**
     * Update the user's profile information.
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
}
