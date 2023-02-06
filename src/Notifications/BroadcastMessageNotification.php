<?php

namespace Uneca\Chimera\Notifications;

use Uneca\Chimera\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BroadcastMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private Announcement $from;

    public function __construct(private Announcement $announcement)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->announcement->title)
                    /*->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))*/
                    ->line($this->announcement->body);
    }

    public function toArray($notifiable)
    {
        return [
            'icon' => 'announcement',
            'from' => $this->announcement->user->name,
            'title' => $this->announcement->title,
            'body' => $this->announcement->body,
            'sent_at' => $this->announcement->created_at,
        ];
    }
}
