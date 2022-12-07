<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Notifications') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Here are all your notifications') }}
        </p>
    </x-slot>

    <livewire:notification-inbox />
</x-app-layout>
