<?php

namespace Uneca\Chimera\Http\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public bool $unreadCount;

    public function render()
    {
        $this->unreadCount = auth()->user()->unreadNotifications->count();
        return view('chimera::livewire.notification-bell');
    }
}
