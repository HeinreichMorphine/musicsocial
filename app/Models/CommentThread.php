<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a thread relationship between comments, indicating a reply structure.
 *
 * @property int $id
 * @property int $comment_id
 * @property int $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Comment $comment
 * @property-read \App\Models\Comment $parent
 */
class CommentThread extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'parent_id'];

    /**
     * Get the comment that owns the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the parent comment of the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}
