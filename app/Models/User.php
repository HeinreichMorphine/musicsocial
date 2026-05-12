<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Represents a user in the application.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $profile_picture
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $unreadNotifications
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'cover_photo_path',
        'is_banned',
        'spotify_id',
        'spotify_token',
        'spotify_refresh_token',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the URL for the user's profile picture.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        return $this->profile_picture
            ? Storage::url($this->profile_picture)
            : asset('images/default-profile.png'); // Ensure you have a default image at public/images/default-profile.png
    }

    /**
     * Get the URL for the user's cover photo.
     */
    public function getCoverPhotoUrlAttribute(): string
    {
        return $this->cover_photo_path
            ? Storage::url($this->cover_photo_path)
            : ''; // Return empty string to fallback to gradient in view, or serve a default image
    }

    /**
     * Get the user's username (using name as username for now).
     */
    public function getUsernameAttribute(): string
    {
        return $this->name; // Assuming 'name' column can serve as a username
    }

    /**
     * Get the shares posted by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shares()
    {
        return $this->hasMany(Share::class)->latest();
    }

    /**
     * Get the comments posted by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the shares that the user has liked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany(Share::class, 'likes', 'user_id', 'share_id');
    }

    /**
     * Get the shares that the user has disliked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dislikes()
    {
        return $this->belongsToMany(Share::class, 'dislikes', 'user_id', 'share_id');
    }

    /**
     * Get the shares that the user has bookmarked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bookmarks()
    {
        return $this->belongsToMany(Share::class, 'bookmarks', 'user_id', 'share_id')->withTimestamps();
    }

    /**
     * Get the users that this user follows.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * Get the users that follow this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    // public function shelfItems()
    // {
    //     return $this->hasMany(ShelfItem::class);
    // }

    public function isFollowing(User $user)
    {
        return $this->following()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the song interactions for the user.
     */
    public function songInteractions()
    {
        return $this->hasMany(SongInteraction::class);
    }

    /**
     * Get the songs on the user's shelf.
     */
    public function shelfSongs()
    {
        return $this->hasMany(UserShelfSong::class)->orderBy('position');
    }

    /**
     * Get the playlists owned by the user.
     */
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }
}
