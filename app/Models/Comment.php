<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a comment made on a music share.
 *
 * @property int $id
 * @property int $user_id
 * @property int $share_id
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Share $share
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $replies
 */
class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'share_id', 'body'];

    /**
     * Get the user that owns the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the share that the comment belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function share()
    {
        return $this->belongsTo(Share::class);
    }

    /**
     * Get the embedded song data from the body.
     * Format: [SONG:spotify_id]
     */
    public function getEmbeddedSongId()
    {
        if (preg_match('/[\[\(]SONG:([a-zA-Z0-9]+)[\]\)]/i', $this->body, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get the plain text body (without metadata tags).
     */
    public function getCleanBody()
    {
        // Strip raw Spotify track URLs (including optional query parameters)
        $body = preg_replace('/(?:https?:\/\/)?open\.spotify\.com\/track\/[a-zA-Z0-9]+(?:\?[^\s\]\)]*)?/i', '', $this->body);
        $body = preg_replace('/[\[\(]SONG:[a-zA-Z0-9]+[\]\)]/i', '', $body);
        $body = preg_replace('/\[UPVOTES:.*?\]/i', '', $body);
        return trim($body);
    }

    /**
     * Get the upvote count from the body.
     * Format: [UPVOTES:user_id,user_id]
     */
    public function getUpvoteCount()
    {
        if (preg_match('/\[UPVOTES:([^\]]*)\]/', $this->body, $matches)) {
            $ids = array_filter(explode(',', $matches[1]));
            return count($ids);
        }
        return 0;
    }

    /**
     * Check if a user has upvoted.
     */
    public function hasUpvoted($userId)
    {
        if (preg_match('/\[UPVOTES:([^\]]*)\]/', $this->body, $matches)) {
            $ids = explode(',', $matches[1]);
            return in_array((string)$userId, $ids);
        }
        return false;
    }

    /**
     * Get the parent comment of the current comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parent()
    {
        return $this->belongsToMany(Comment::class, 'comment_threads', 'comment_id', 'parent_id');
    }

    /**
     * Get the replies to the current comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function replies()
    {
        return $this->belongsToMany(Comment::class, 'comment_threads', 'parent_id', 'comment_id');
    }
}
