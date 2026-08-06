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
        'preview_url',
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

    /**
     * Get the album art URL, with a fallback to the default Reso icon.
     *
     * @param string|null $value
     * @return string
     */
    public function getAlbumArtUrlAttribute($value)
    {
        if (empty($value) || !filter_var($value, FILTER_VALIDATE_URL)) {
            return asset('icons/reso.png');
        }

        return $value;
    }

    public function getReasonAttribute()
    {
        return $this->attributes['reason'] ?? 'Based on your taste';
    }

    public function setReasonAttribute($value)
    {
        $this->attributes['reason'] = $value;
    }

    public function getChipLabelAttribute()
    {
        return $this->attributes['chip_label'] ?? null;
    }

    public function setChipLabelAttribute($value)
    {
        $this->attributes['chip_label'] = $value;
    }

    /**
     * Normalized 11-character YouTube video ID for embeds.
     */
    public function embedYoutubeVideoId(): ?string
    {
        $raw = $this->youtube_video_id ?: $this->youtube_url;
        if (!$raw) {
            return null;
        }

        if (preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $raw, $matches)) {
            return $matches[1];
        }

        $raw = trim($raw);

        return preg_match('/^[a-zA-Z0-9_-]{11}$/', $raw) ? $raw : null;
    }
}