<?php

namespace Uneca\Chimera\Http\Livewire;

use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public Collection $notifications;
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
        return view('chimera::livewire.notification-dropdown');
    }
}
