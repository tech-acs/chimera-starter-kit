<div>
    <div class="block py-2 px-4 font-medium text-center text-gray-700 bg-gray-50">
        {{ __('Notifications') }} ({{ $totalCount }})
    </div>
    <div class="grid grid-cols-1 bg-gray-200" wire:poll.1000ms.visible>
        @forelse($notifications as $notification)
            <a
                href="{{ route('notification.index') }}"
                class="flex py-3 px-4 hover:bg-gray-100 bg-white mt-[1px] first:mt-0 @if($notification->read_at) text-gray-400 @else border-l-4 border-blue-500 text-blue-700 @endif"
            >
                <div class="flex-shrink-0">
                    <x-dynamic-component component="chimera::icon.{{ $notification->data['icon'] }}" class="mt-4" />
                </div>
                <div class="pl-3 w-full">
                    <div class="text-sm mb-1.5">
                        <div class="font-semibold mb-2">{{ $notification->data['title'] }}</div>
                        <div class="line-clamp-3 mb-2 text-gray-500 ">{{ $notification->data['body'] }}</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-xs text-blue-600">{{ $notification->created_at->locale(app()->getLocale())->diffForHumans() }}</div>
                        <div class="text-xs text-gray-500">{{ __('Sent by') }} {{ $notification->data['from'] }}</div>
                    </div>
                </div>
            </a>
        @empty
            <div class="py-3 px-6 text-center text-sm sm:px-14">
                <!-- Heroicon name: outline/exclamation-circle -->
                <svg class="mx-auto h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="mt-2 text-gray-500">{{ __('You have no notification yet.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm flex justify-between">
            <a href="{{ route('notification.index') }}" class="whitespace-nowrap text-indigo-600 hover:text-indigo-500">View all</a>
            <a href="" wire:click.prevent.stop="markAllAsRead()" class="whitespace-nowrap text-indigo-600 hover:text-indigo-500">Mark all as read</a>
        </div>
    </div>
</div>
