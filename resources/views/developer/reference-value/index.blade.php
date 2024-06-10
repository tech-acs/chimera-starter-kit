<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Reference Values') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage reference values for indicators.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />

        <div class="flex justify-between">
            <div>
                @if(count($hierarchies) > 0)
                    <a
                        title="Download excel template you can use to populate your indicator reference values with and import them"
                        href="{{ route('developer.download-reference-value-import-template') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Download Import Template
                    </a>
                @endif
            </div>
            <div class="flex items-center">
                <div class="bg-sky-400/20 text-sky-600 h-9 px-4 text-sm flex items-center rounded-full font-medium">
                    {{ empty($summary) ? "No reference values imported yet" : $summary }}
                </div>
                @can('developer-mode')
                    <div class="ml-4" x-data="confirmedDeletion">
                        <a href="{{route('developer.reference-value.create')}}"><x-button>{{ __('Import') }}</x-button></a>

                        <x-chimera::delete-confirmation />
                        <a href="{{route('developer.reference-value.destroy')}}" x-on:click.prevent="confirmThenDelete($el)">
                            <x-danger-button class="ml-2">{{ __('Delete All') }}</x-danger-button>
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <x-chimera-smart-table :$smartTableData />

    </div>
</x-app-layout>
