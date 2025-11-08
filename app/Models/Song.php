<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a song in the application.
 *
 * @property int $id
 * @property string $track_name
 * @property string $artist_name
 * @property string|null $album_art_url
 * @property string|null $spotify_track_id
 * @property string|null $youtube_video_id
 * @property string|null $spotify_url
 * @property string|null $youtube_url
 * @property string|null $genres
 * @property string|null $release_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_name',
        'artist_name',
        'album_art_url',
        'spotify_track_id',
        'youtube_video_id',
        'spotify_url',
        'youtube_url',
        'genres',
        'release_date',
    ];

    /**
     * Get the shares associated with the song.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shares()
    {
        return $this->hasMany(Share::class);
    }
}