<?php

namespace App\Http\Controllers;

use App\Models\SongInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SongInteractionController extends Controller
{
    /**
     * Store a newly created song interaction (listen, like, dislike).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'type' => 'required|in:listen,like,dislike',
        ]);

        $user = Auth::user();

        // Update or Create the interaction
        // If user already "listened", and now "likes", we update the type to "like" (stronger signal?)
        // Actually, user might want to toggle?
        // Let's assume the frontend sends the specific state.
        // If "type" is 'listen', it just marks as seen/listened.
        // If 'like' or 'dislike', it overwrites 'listen'.
        
        $interaction = SongInteraction::updateOrCreate(
            [
                'user_id' => $user->id,
                'song_id' => $validated['song_id'],
            ],
            [
                'type' => $validated['type']
            ]
        );

        // Clear the recommended songs cache for this user so they get fresh data next time
        \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_recommended_songs");

        return response()->json([
            'message' => 'Interaction recorded.',
            'interaction' => $interaction
        ]);
    }
}
