<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Playlist;
use App\Models\User;

class PlaylistInviteNotification extends Notification
{
    use Queueable;

    public $playlist;
    public $inviter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Playlist $playlist, User $inviter)
    {
        $this->playlist = $playlist;
        $this->inviter = $inviter;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'playlist_id' => $this->playlist->id,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'message' => $this->inviter->name . ' invited you to collaborate on "' . $this->playlist->name . '".',
        ];
    }
}
