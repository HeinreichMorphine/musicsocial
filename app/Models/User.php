<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
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
     * Get the user's username (using name as username for now).
     */
    public function getUsernameAttribute(): string
    {
        return $this->name; // Assuming 'name' column can serve as a username
    }

    /**
     * A user can post many shares.
     */
    public function shares()
    {
        return $this->hasMany(Share::class)->latest();
    }

    /**
     * A user can post many comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The shares a user has liked (Many-to-Many).
     */
    public function likes()
    {
        return $this->belongsToMany(Share::class, 'likes', 'user_id', 'share_id');
    }

    /**
     * The users that this user follows (Many-to-Many).
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * The users that follow this user (Many-to-Many).
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }
}
