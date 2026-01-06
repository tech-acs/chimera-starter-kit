<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Scorecards') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage scorecards here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />

        <x-chimera::error-display />

        @can('developer-mode')
            <div class="text-right">
                <a href="{{route('developer.scorecard.create')}}"><x-button>{{ __('Create new') }}</x-button></a>
            </div>
        @endcan

        <x-chimera-smart-table :$smartTableData custom-action-sub-view="chimera::scorecard.custom-action" />
    </div>
</x-app-layout>
