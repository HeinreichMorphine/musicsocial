<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    /** @use HasFactory<\Database\Factories\ShareFactory> */
    use HasFactory;
    protected $fillable = [
    'user_id',
    'type',
    'spotify_track_id',
    'caption',
    'track_name',
    'artist_name',
    'album_art_url',
    'spotify_url',
    'youtube_video_id',
    'youtube_url',
    'disliked', // Add 'disliked' to fillable
    ];

    /**
     * A share belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A share can have many comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The users that liked this share (Many-to-Many).
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes', 'share_id', 'user_id');
    }

    /**
     * The users that disliked this share (Many-to-Many).
     */
    public function dislikes()
    {
        return $this->belongsToMany(User::class, 'dislikes', 'share_id', 'user_id');
    }
}
