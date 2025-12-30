<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        <x-chimera::message-display />

        <div class="mt-2 flex flex-col">
            <div class="inline-block min-w-full py-2 align-middle">
                {{--<div class="p-2 text-sm text-gray-500">
                    Filter
                    @if (is_null($filter))
                        : click on the rounded badges to filter to that data source
                    @else
                        <span class="border border-purple-200 inline-flex rounded-full bg-purple-100 px-2 leading-5 text-purple-800 items-center">
                            {{ $filter }}
                            <a href="{{route('report')}}" type="button" class="cursor-pointer flex-shrink-0 ml-1 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-gray-300 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
                                <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                  <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                                </svg>
                            </a>
                        </span>
                    @endif
                </div>--}}
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Report</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Generation Schedule</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Current Version</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 items-center">
                                Notify Me
                                <a title="You will be notified via email and in-app message">
                                    <svg class="inline w-5 h-5 text-blue-700 cursor-pointer" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path>
                                    </svg>
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($records ?? [] as $report)
                            @can($report->permission_name)
                        <tr>
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6 w-5/12">
                                <div class="flex items-center">
                                    {{--<div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </div>--}}
                                    <div class="w-full">
                                        <div class="font-medium text-gray-900">
                                            <span class="text-base mr-2">{{ $report->title }}</span>
                                            <div class="border border-purple-200 inline-flex rounded-full bg-purple-100 px-2 leading-5 text-purple-800 items-center">
                                                {{ $report->data_source_title }}
                                                {{--<svg class="w-4 ml-2" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.991 19.67l-3.991 1.33v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v3" /><path d="M19 16l-2 3h4l-2 3" /></svg>--}}
                                            </div>
                                        </div>
                                        <div class="text-gray-500 mt-2 text-xs">{{ $report->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-800 w-1/5">
                                {{ $report->schedule_for_humans }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $report->last_generated_at?->toDayDateTimeString() ?? ' N/A ' }}<br />
                                {{ $report->last_generated_at?->diffForHumans() ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                <livewire:subscribe-to-report-notification :report="$report"/>
                                {{--<x-chimera::toggle-button name="subscribed-to-{{ $report->id }}" :value="$report->subscribed" />--}}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                @if($report->fileExists)
                                    <a href="{{ route('report.download', $report) }}" title="Download" type="button" class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                @else
                                    <button disabled title="Download" type="button" class="disabled:opacity-25 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 pl-3 pr-4 text-center text-gray-500">{{ __('There are no records to display') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if ($records->hasPages())
                            <tfoot>
                                <tr>
                                    <td>
                                        <div class="px-6 text-left text-xs text-gray-500 tracking-wider">{{ $records->appends(request()->all())->links() }}</div>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
