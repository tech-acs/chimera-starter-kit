<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Reports') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage reports here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />

        <x-chimera::error-display />

        @can('developer-mode')
            <div class="text-right">
                <a href="{{route('developer.report.create')}}"><x-button>{{ __('Create new') }}</x-button></a>
            </div>
        @endcan

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">

                        <x-chimera::delete-confirmation prompt="[ This action will delete the component file and also the database entry for the report ]" />

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Title') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Data source') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Published') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Enabled') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Schedule') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium py-2">{{$record->title}}</div>
                                    <div class="text-gray-500">{{$record->name}}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{$record->data_source}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <x-chimera::yes-no value="{{$record->published}}" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <x-chimera::yes-no value="{{$record->enabled}}" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    At {{ $record->run_at }} <br />
                                    @if($record->run_every) then every {{ $record->run_every }} hours @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{route('manage.report.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a> |
                                    <a href="{{route('manage.report.run_now', $record->id)}}" class="text-red-600 hover:text-red-900">{{ __('Run now') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                    {{ __('There are no records to display') }}
                                </td>
                            </tr>
                        @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
