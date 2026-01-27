<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Handles the logic for following and unfollowing users.
 */
class FollowController extends Controller
{
    /**
     * Toggles the follow status between the authenticated user and the specified user.
     *
     * This method allows the authenticated user to follow or unfollow another user.
     * It prevents users from following themselves and returns a JSON response with the
     * updated follow status and the target user's new follower count.
     *
     * @param  \App\Models\User  $user The user to follow or unfollow.
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(User $user)
    {
        // A user cannot follow themselves
        if (auth()->id() === $user->id) {
            return response()->json(['error' => 'You cannot follow yourself.'], 422);
        }

        // Use toggle to follow or unfollow
        auth()->user()->following()->toggle($user);

        // Invalidate the suggested users cache for the authenticated user
        \Illuminate\Support\Facades\Cache::forget("user_" . auth()->id() . "_suggested_users");

        // Return a JSON response with the new follower count and followed status
        return response()->json([
            'followed' => auth()->user()->following->contains($user),
            'followersCount' => $user->followers()->count(),
        ]);
    }
}
