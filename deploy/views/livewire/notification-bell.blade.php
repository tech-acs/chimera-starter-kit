<x-round-button title="{{ __('Notifications') }}"  class="inline-flex relative items-center">
    <div wire:poll.3000ms.visible>
        @if ($unreadCount > 0)
            <x-icon.bell-unread />
        @else
            <x-icon.bell />
        @endif
    </div>
</x-round-button>
