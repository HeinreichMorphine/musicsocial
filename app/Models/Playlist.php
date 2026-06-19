<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cover_image',
    ];

    protected $appends = ['cover_image_url'];

    public function collaborators()
    {
        return $this->hasMany(PlaylistCollaborator::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'playlist_collaborators')
                    ->withPivot('role', 'status')
                    ->withTimestamps();
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            if (str_starts_with($this->cover_image, 'http')) {
                return $this->cover_image;
            }
            return \Illuminate\Support\Facades\Storage::url($this->cover_image);
        }

        // Fallback to first song's album art
        $firstSong = $this->songs()->with('song')->first();
        if ($firstSong && $firstSong->song && $firstSong->song->album_art_url) {
            return $firstSong->song->album_art_url;
        }

        return asset('icons/reso.png');
    }

    public function creator()
    {
        return $this->hasOne(PlaylistCollaborator::class)->where('role', 'owner');
    }

    public function songs()
    {
        return $this->hasMany(PlaylistSong::class);
    }
}
