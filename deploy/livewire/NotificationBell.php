<?php

namespace App\Http\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public bool $unreadCount;
    public function mount()
    {
        $this->unreadCount = auth()->user()->unreadNotifications->count();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
