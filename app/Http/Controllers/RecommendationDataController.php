<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationDataController extends Controller
{
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

        // Fetch 'not interested' feedback (negative feedback)
        // Assuming a 'user_feedback' table with 'feedback_type' column
        $notInterested = DB::table('user_feedback')
                            ->where('feedback_type', 'not_interested')
                            ->select('user_id', 'share_id')
                            ->get();
        foreach ($notInterested as $feedback) {
            $interactions[] = [
                'user_id' => $feedback->user_id,
                'item_id' => $feedback->share_id,
                'rating' => -1, // Negative feedback
            ];
        }

        return response()->json($interactions);
    }
}
