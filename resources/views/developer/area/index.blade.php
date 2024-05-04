<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Areas') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage areas. Names, codes and map.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between">
            <div></div>
            <div class="flex items-center">
                <div class="bg-sky-400/20 text-sky-600 h-9 px-4 text-sm flex items-center rounded-full font-medium mr-4">
                    {{ empty($summary) ? "No areas imported yet" : $summary }}
                </div>
                @can('developer-mode')
                    <div x-data="confirmedDeletion">
                        <a href="{{route('developer.area.create')}}"><x-button>{{ __('Import') }}</x-button></a>

                        <x-chimera::delete-confirmation />
                        <a href="{{route('developer.area.destroy')}}" x-on:click.prevent="confirmThenDelete($el)">
                            <x-danger-button class="ml-2">{{ __('Delete All') }}</x-danger-button>
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <x-chimera-smart-table :$smartTableData />
    </div>

</x-app-layout>
