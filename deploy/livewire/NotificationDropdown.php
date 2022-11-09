<?php

namespace App\Http\Livewire;

use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notification;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public DatabaseNotificationCollection $notifications;
    public bool $show;

    public function mount()
    {
        $this->notifications = auth()->user()->notifications;
        $this->show = false;
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
