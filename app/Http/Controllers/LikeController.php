<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggles the "like" status for a given share.
     */
    public function toggle(Share $share)
    {
        // Get the currently authenticated user
        $user = auth()->user();

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
