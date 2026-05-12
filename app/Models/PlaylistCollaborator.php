<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistCollaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'user_id',
        'role',
        'status',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
