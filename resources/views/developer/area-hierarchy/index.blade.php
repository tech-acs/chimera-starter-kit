<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Area Hierarchies') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Manage your area hierarchy here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="text-right">
            @if (app()->isLocal())
                <a href="{{route('developer.area-hierarchy.create')}}"><x-jet-button>{{ __('Add') }}</x-jet-button></a>
            @endif
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
                        <ul class="">
                            @foreach($errors->all() as $error)
                                <li class="">{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg" x-data="confirmedDeletion">

                        <x-chimera::delete-confirmation />

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Zero pad length') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Simplification tolerance') }}
                                </th>
                                {{--<th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Corresponding map zoom levels') }}
                                </th>--}}
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{$record->name}}
                                    @if ($loop->first)
                                        <span class="inline-flex ml-1 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            {{ __('first') }}
                                        </span>
                                    @endif
                                    @if ($loop->last)
                                        <span class="inline-flex ml-1 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            {{ __('last') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{ $record->zero_pad_length }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{ $record->simplification_tolerance }}
                                </td>
                                {{--<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center">
                                    {{ json_encode($record->map_zoom_levels) }}
                                </td>--}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (app()->isLocal())
                                        <a href="{{route('developer.area-hierarchy.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        <span class="text-gray-400 px-1">|</span>
                                        <a href="{{ route('developer.area-hierarchy.destroy', $record->id) }}" x-on:click.prevent="confirmThenDelete($el)" class="text-red-600">{{ __('Delete') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
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
