<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\Request;

/**
 * Handles the logic for liking and unliking music shares.
 */
class LikeController extends Controller
{
    /**
     * Toggles the like status for a given share.
     *
     * This method allows the authenticated user to like or unlike a share. It prevents users
     * from liking their own shares and removes any existing dislikes before liking. The method
     * returns a JSON response with the updated like status and the share's new like count.
     *
     * @param  \App\Models\Share  $share The share to like or unlike.
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Share $share)
    {
        // Get the currently authenticated user
        $user = auth()->user();

        // Prevent user from liking their own share
        if ($user->id === $share->user_id) {
            return response()->json([
                'message' => 'You cannot like your own share.',
                'liked' => $user->likes->contains($share), // Should always be false
                'likesCount' => $share->likes()->count(),
            ], 403); // Forbidden
        }

        // If the user has disliked this share, remove the dislike first
        if ($user->dislikes->contains($share)) {
            $user->dislikes()->detach($share);
        }

        // Use the toggle method to attach if not attached,
        // or detach if already attached.
        $user->likes()->toggle($share);

        // Return a JSON response with the new like count and liked status
        return response()->json([
            'liked' => $user->likes->contains($share),
            'likesCount' => $share->likes()->count(),
        ]);
    }
}
