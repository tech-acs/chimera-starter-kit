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
                @if(app()->environment('local'))
                    <div x-data="confirmedDeletion">
                        <a href="{{route('developer.area.create')}}"><x-button>{{ __('Import') }}</x-button></a>

                        <x-chimera::delete-confirmation />
                        <a href="{{route('developer.area.destroy')}}" x-on:click.prevent="confirmThenDelete($el)">
                            <x-danger-button class="ml-2">{{ __('Delete All') }}</x-danger-button>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <x-chimera-smart-table :$smartTableData hierarchies="$hierarchies"/>
    </div>

{{--<tr>
    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
        {{ __('Name') }}
    </th>
    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
        {{ __('Code') }}
    </th>
    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
        {{ __('Level') }}
    </th>
    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
        {{ __('Path') }}
    </th>
    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
        {{ __('Has map') }}
    </th>
    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
</tr>

<tr>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
        {{$record->name}}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
        {{$record->code}}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
        {{ ucfirst($hierarchies[$record->level] ?? $record->level) }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
        {{$record->path}}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
        <x-chimera::yes-no value="{{$record->geom}}" />
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <a href="{{route('developer.area.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
        <span class="text-gray-400 px-1">|</span>
        <a href="{{route('developer.area.destroy', $record->id)}}" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</a>
    </td>
</tr>--}}

</x-app-layout>
