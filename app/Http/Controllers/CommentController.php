<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentThread;
use App\Models\Share;
use Illuminate\Http\Request;

/**
 * Handles the creation, updating, and deletion of comments on music shares.
 */
class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Share  $share
     * @return \Illuminate\Contracts\View\View The rendered comment component.
     */
    public function store(Request $request, Share $share)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // 2. Create the comment linked to the share and the user
        $comment = $share->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        // 3. If it's a reply, create a CommentThread record
        if (isset($validated['parent_id'])) {
            CommentThread::create([
                'comment_id' => $comment->id,
                'parent_id' => $validated['parent_id'],
            ]);
        }

        // 4. Return the rendered comment component
        return view('components.comment', ['comment' => $comment])->render();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Share  $share
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Share $share, Comment $comment)
    {
        // 1. Authorize the user
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // 2. Validate the incoming data
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        // 3. Update the comment
        $comment->update($validated);

        // 4. Return a JSON response with the updated comment body
        return response()->json([
            'body' => $comment->body,
            'message' => 'Comment updated!',
        ]);
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param  \App\Models\Share  $share
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Share $share, Comment $comment)
    {
        // 1. Authorize the user
        // We only allow the user who created the comment to delete it.
        if (auth()->id() !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // 2. Delete the comment
        $comment->delete();

        // 3. Return a success response
        return response()->json(['message' => 'Comment deleted.']);
    }
}
