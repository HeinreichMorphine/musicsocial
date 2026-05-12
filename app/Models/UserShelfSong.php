<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShelfSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'song_id',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
