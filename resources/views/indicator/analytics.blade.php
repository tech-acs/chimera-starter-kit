<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Indicator Analytics') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Here you will find indicator performance analytics') }}</p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 pt-2 px-4 sm:px-6 lg:px-8">

        <div class="py-2 align-middle inline-block min-w-full">

            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $indicator->title }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $indicator->description }}</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Pages</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $indicator->pages->pluck('title')->join(', ') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Tag</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $indicator->tag }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Total queries</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $queryTimes->count() }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Average query time</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $queryTimes->avg() }} seconds</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Top 5 longest running queries</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="-mx-4 mt-1 ring-1 ring-gray-300 sm:-mx-6 md:mx-0 md:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-300">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="py-3 pl-4 text-left text-sm font-semibold text-gray-900 lg:table-cell">User</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Started At</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Level</th>
                                                <th scope="col" class="px-3 py-3 text-left text-sm font-semibold text-gray-900 lg:table-cell">Source</th>
                                                <th scope="col" class="px-3 py-3 text-center text-sm font-semibold text-gray-900 lg:table-cell">Query Time (seconds)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($longestRunningQueries as $record)
                                                <tr>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->user?->name }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->started_at?->toDayDateTimeString() }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->level }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $record->source }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200 text-center">{{ $record->query_time }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-3 py-3 text-sm text-gray-500 lg:table-cell border-t border-gray-200 text-center">There are no records to display</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </dd>
                            <dt class="text-sm font-medium text-gray-500 mt-8">All query times (in seconds). Oldest to most recent.</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="divide-y divide-gray-200 rounded-md border border-gray-200 py-3 pl-3 pr-4 text-sm">
                                    {{ $queryTimes->join(', ') }}
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>


        </div>

    </div>

</x-app-layout>
