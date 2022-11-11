<?php

namespace App\Http\Livewire;

use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notification;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public DatabaseNotificationCollection $notifications;
    public bool $show;
    public int $totalCount;

    public function mount()
    {
        $this->show = false;
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = auth()->user();
        $this->notifications = $user->notifications()->take(5)->get();
        $this->totalCount = $user->notifications->count();
        return view('livewire.notification-dropdown');
    }
}
