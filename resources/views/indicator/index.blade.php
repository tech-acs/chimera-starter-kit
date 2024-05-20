<x-app-layout>
    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Indicators') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage indicators and their grouping into pages, here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />

        <x-chimera-smart-table :$smartTableData custom-action-sub-view="chimera::indicator.custom-action" />
    </div>
</x-app-layout>
