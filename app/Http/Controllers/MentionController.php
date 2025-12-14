<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class MentionController extends Controller
{
    /**
     * Get mention suggestions for autocomplete
     * Returns users that the authenticated user follows + parent comment author if applicable
     */
    public function suggestions(Request $request)
    {
        $query = $request->input('query', '');
        $parentCommentId = $request->input('parent_comment_id');
        
        // Get users the authenticated user follows
        $followedUsers = auth()->user()->following()
            ->where('name', 'LIKE', $query . '%')
            ->select('id', 'name', 'profile_picture')
            ->limit(5)
            ->get();
        
        $suggestions = $followedUsers->toArray();
        
        // If replying to a comment, add the parent comment author
        if ($parentCommentId) {
            $parentComment = \App\Models\Comment::find($parentCommentId);
            if ($parentComment && $parentComment->user_id !== auth()->id()) {
                $parentAuthor = $parentComment->user;
                if (stripos($parentAuthor->name, $query) === 0) {
                    // Add parent author at the top if matching
                    array_unshift($suggestions, [
                        'id' => $parentAuthor->id,
                        'name' => $parentAuthor->name,
                        'profile_picture' => $parentAuthor->profile_picture,
                        'is_parent_author' => true
                    ]);
                }
            }
        }
        
        return response()->json($suggestions);
    }
}
