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
        <div class="flex justify-between">
            <form method="get" action="{{ route('developer.reference-value.index') }}">
                <x-jet-input type="search" name="search" placeholder="Search indicator" value="{{ request('search') }}" />
            </form>
            <div class="flex items-center">
                <div class="bg-sky-400/20 text-sky-600 h-9 px-4 text-sm flex items-center rounded-full font-medium mr-4">
                    {{ empty($summary) ? "No reference values imported yet" : $summary }}
                </div>
                @if(app()->environment('local'))
                    <div x-data="confirmedDeletion">
                        <a href="{{route('developer.reference-value.create')}}"><x-jet-button>{{ __('Import') }}</x-jet-button></a>

                        <x-chimera::delete-confirmation />
                        <a href="{{route('developer.reference-value.destroy')}}" x-on:click.prevent="confirmThenDelete($el)">
                            <x-jet-danger-button class="ml-2">{{ __('Delete All') }}</x-jet-danger-button>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @if (session('message'))
            <div class="rounded-md p-4 py-3 mt-4 mb-4 border bg-blue-50 border-blue-300">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: solid/information-circle -->
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-blue-700">
                            {{session('message')}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-md p-4 py-3 mt-4 mb-4 border bg-red-100 border-red-400">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: solid/information-circle -->
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between text-sm text-red-700">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li class="list-disc">{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Indicator') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Area Path') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Level') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Value') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{$record->indicator}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{$record->path}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{ ucfirst($hierarchies[$record->level] ?? $record->level) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{$record->value}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{route('developer.reference-value.edit', $record)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                    {{--<span class="text-gray-400 px-1">|</span>
                                    <a href="{{route('developer.area.destroy', $record->id)}}" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</a>--}}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                    {{ __('There are no records to display') }}
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                            @if ($records->hasPages())
                                <tfoot>
                                    <tr><td colspan="6" class="px-6 text-left text-xs text-gray-500 tracking-wider">{{ $records->withQueryString()->links() }}</td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
