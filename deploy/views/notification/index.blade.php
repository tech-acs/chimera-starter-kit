<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Notifications') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Here are all your notifications') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($records as $record)
                    <li class="relative bg-white py-5 px-4 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 hover:bg-gray-50">
                        <div class="flex justify-between space-x-3">
                            <div class="min-w-0 flex-1">
                                <a href="#" class="block focus:outline-none">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $record->data['from'] }}</p>
                                    <p class="truncate text-sm text-gray-500">{{ $record->data['title'] }}</p>
                                </a>
                            </div>
                            <time datetime="2021-01-27T16:35" class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500">{{ $record->created_at->diffForHumans() }}</time>
                        </div>
                        <div class="mt-1">
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $record->data['body'] }}</p>
                        </div>
                    </li>
                @empty
                    Nada
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
