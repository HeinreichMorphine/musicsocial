<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Share;
use App\Models\User;

class PostLikedNotification extends Notification
{
    use Queueable;

    public $share;
    public $liker;

    /**
     * Create a new notification instance.
     */
    public function __construct(Share $share, User $liker)
    {
        $this->share = $share;
        $this->liker = $liker;
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
            'share_id' => $this->share->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'message' => $this->liker->name . ' liked your post.',
        ];
    }
}
