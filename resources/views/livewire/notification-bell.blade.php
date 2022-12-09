<x-chimera::round-button title="{{ __('Notifications') }}"  class="inline-flex relative items-center">
    <div wire:poll.3000ms.visible>
        @if ($unreadCount > 0)
            <x-chimera::icon.bell-unread />
        @else
            <x-chimera::icon.bell />
        @endif
    </div>
</x-chimera::round-button>
