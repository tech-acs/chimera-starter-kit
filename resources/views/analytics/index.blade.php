<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Query Analytics') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Here you will find query performance analytics') }}</p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 pt-2 px-4 sm:px-6 lg:px-8">

        <div class="py-2 align-middle inline-block min-w-full">

            <div class="overflow-hidden bg-white shadow sm:rounded-lg px-4 py-5 sm:px-6">

                {{--<dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">

                    <div class="sm:col-span-2">--}}
                            <div class="border-b border-gray-200 pb-5 sm:flex sm:items-center sm:justify-between">
                                <div class="-ml-2 -mt-2 flex flex-wrap items-baseline">
                                    <h3 class="ml-2 mt-2 text-lg font-medium leading-6 text-gray-900">Queries</h3>
                                    <p class="ml-2 mt-1 truncate text-sm text-gray-500">ordered by query time</p>
                                </div>
                                {{--<div class="mt-3 flex sm:mt-0 sm:ml-4">
                                    <button type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Share</button>
                                    <button type="button" class="ml-3 inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Create</button>
                                </div>--}}
                            </div>

                            <dd class="mt-6 text-sm text-gray-900">
                                <div class="-mx-4 mt-1 ring-1 ring-gray-300 sm:-mx-6 md:mx-0 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="py-3 pl-4 text-left text-sm font-semibold text-gray-900 lg:table-cell">User</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">From</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Level</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Status</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Started At</th>
                                                <th scope="col" class="px-3 py-3 text-center text-sm font-semibold text-gray-900 lg:table-cell">Query Time (seconds)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($records as $record)
                                                <tr>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->user?->name }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">
                                                        <a title="{{ $record->type }}"><x-dynamic-component :component="$record->icon_component" class="inline w-5 h-5 text-blue-500 mr-2" /></a>
                                                        {{ $record->analyzable->title }}
                                                    </td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->level }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->source }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->started_at?->toDayDateTimeString() }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200 text-center">{{ $record->query_time }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200 text-center">There are no records to display</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if ($records->hasPages())
                                            <tfoot>
                                                <tr><td colspan="6" class="px-6 py-2 text-left text-xs text-gray-500 tracking-wider">{{ $records->withQueryString()->links() }}</td></tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </dd>

                        {{--</div>
                    </dl>--}}

            </div>


        </div>

    </div>

</x-app-layout>
