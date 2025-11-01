<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'share_id', 'body'];

    /**
     * A comment belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A comment belongs to one share.
     */
    public function share()
    {
        return $this->belongsTo(Share::class);
    }

    /**
     * A comment can have a parent.
     */
    public function parent()
    {
        return $this->belongsToMany(Comment::class, 'comment_threads', 'comment_id', 'parent_id');
    }

    /**
     * A comment can have many replies.
     */
    public function replies()
    {
        return $this->belongsToMany(Comment::class, 'comment_threads', 'parent_id', 'comment_id');
    }
}
