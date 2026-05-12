<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'song_id',
        'added_by_user_id',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function song()
    {
        return $this->belongsTo(Song::class, 'song_id', 'spotify_track_id');
    }
}
