<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Represents a music share in the application.
 *
 * @property int $id
 * @property int $user_id
 * @property int $song_id
 * @property string|null $caption
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Song $song
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $likes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $dislikes
 */
class Share extends Model
{
    /** @use HasFactory<\Database\Factories\ShareFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'song_id',
        'caption',
        'type',
        'is_deleted',
    ];

    /**
     * The "booting" method of the model.
     *
     * This method is called when the model is booted. It sets up a `deleting` event listener
     * to manage the associated `Song` record. If a `Share` is deleted and it's the last
     * share referencing a particular `Song`, that `Song` record will also be deleted.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($share) {
            Log::info('Share deleting event fired for Share ID: ' . $share->id);
            if ($share->song) {
                Log::info('Song relationship loaded for Share ID: ' . $share->id . ', Song ID: ' . $share->song->id);
                // Check if any other shares of this song exist
                $otherShares = Share::where('song_id', $share->song_id)
                                    ->where('id', '!=', $share->id)
                                    ->exists();

                Log::info('Other shares exist for Song ID: ' . $share->song->id . ': ' . ($otherShares ? 'Yes' : 'No'));

                if (!$otherShares) {
                    // If no other shares exist, delete the song
                    $share->song->delete();
                    Log::info('Song ID: ' . $share->song->id . ' deleted as no other shares exist.');
                }
            } else {
                Log::info('Song relationship NOT loaded for Share ID: ' . $share->id);
            }
        });
    }

    /**
     * Get the user that owns the share.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the song that the share belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Get the comments for the share.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the users that liked this share.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes', 'share_id', 'user_id');
    }

    /**
     * Get the users that disliked this share.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dislikes()
    {
        return $this->belongsToMany(User::class, 'dislikes', 'share_id', 'user_id');
    }
}