<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationDataController extends Controller
{
    /**
     * Get user interactions for the recommendation service.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInteractions()
    {
        $interactions = [];

        // Fetch likes (positive feedback)
        $likes = DB::table('likes')->select('user_id', 'share_id')->get();
        foreach ($likes as $like) {
            $interactions[] = [
                'user_id' => $like->user_id,
                'item_id' => $like->share_id,
                'rating' => 1, // Positive feedback
            ];
        }

        // Fetch dislikes (negative feedback)
        $dislikes = DB::table('dislikes')->select('user_id', 'share_id')->get();
        foreach ($dislikes as $dislike) {
            $interactions[] = [
                'user_id' => $dislike->user_id,
                'item_id' => $dislike->share_id,
                'rating' => 0, // Negative feedback
            ];
        }

        return response()->json($interactions);
    }
}
