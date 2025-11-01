<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
   /**
     * Toggles the "follow" status for a given user.
     */
    public function toggle(User $user)
    {
        // A user cannot follow themselves
        if (auth()->id() === $user->id) {
            return response()->json(['error' => 'You cannot follow yourself.'], 422);
        }

        // Use toggle to follow or unfollow
        auth()->user()->following()->toggle($user);

        // Return a JSON response with the new follower count and followed status
        return response()->json([
            'followed' => auth()->user()->following->contains($user),
            'followersCount' => $user->followers()->count(),
        ]);
    }
}
