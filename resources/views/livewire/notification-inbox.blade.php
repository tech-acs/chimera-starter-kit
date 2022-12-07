<div class="flex h-[calc(100vh_-_290px)] max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="w-2/5 overflow-y-auto scrollbar bg-white">
        <ul role="list" class="grid grid-cols-1 gap-1 text-gray-800">
            <li class="bg-white text-base font-medium p-2 border-b-2">
                {{ $notifications->count() }} notifications <span class="font-semibold">({{ $unreadCount }} unread)</span>
            </li>
            @forelse($notifications as $notification)
                <li
                    wire:click="show('{{ $notification->id }}')"
                    class="relative cursor-pointer bg-white py-5 px-4 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 hover:bg-gray-50
                           @if ($notification->id === $selectedNotification->id) bg-gray-50 border border-gray-200 @endif
                           @if($notification->read_at) text-gray-500 @else border-l-4 border-blue-500 text-blue-700 @endif"
                >
                    <div class="flex justify-between space-x-3">
                        <div class="min-w-0 flex-1">
                            <a class="block focus:outline-none">
                                <p class="text-sm font-medium">{{ $notification->data['from'] }}</p>
                                <p class="text-sm text-gray-500">{{ $notification->data['title'] }}</p>
                            </a>
                        </div>
                        <time class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</time>
                    </div>
                    <div class="mt-1">
                        <p class="text-sm text-gray-600 line-clamp-2">{{ $notification->data['body'] }}</p>
                    </div>
                </li>
            @empty
                <li class="relative cursor-pointer bg-white py-5 px-4 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 hover:bg-gray-50 text-center">
                    You have no notifications
                </li>
            @endforelse
        </ul>
    </div>
    <div class="w-full p-6 bg-white border-l-2">
        @if (! empty($selectedNotification))
        <div>
            <div class="border-b border-gray-200 font-medium text-gray-900 py-4 flex justify-between">
                <p>From: {{ $selectedNotification->data['from'] }}</p>
                <time datetime="2021-01-27T16:35" class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500">{{ $selectedNotification->created_at->diffForHumans() }}</time>
            </div>
            <p class="text-lg text-gray-800 py-4">{{ $selectedNotification->data['title'] }}</p>
            <p class="text-sm text-gray-600">{{ $selectedNotification->data['body'] }}</p>
            <div class="flex justify-end pt-2 text-xs text-gray-500">
                {{ is_null($selectedNotification->read_at) ? '' : 'Seen ' . $selectedNotification->read_at->diffForHumans() }}
            </div>
        </div>
        @endif
    </div>
</div>
