<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
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
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($records ?? [] as $report)
                    @can($report->permission_name)
                    <li>
                        <div class="flex items-center px-4 py-4 sm:px-6">
                            <div class="min-w-0 flex-1 flex items-center">
                                <div class="min-w-0 flex-1 md:grid md:grid-cols-2 md:gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-indigo-600 truncate">{{ $report->title }}</p>
                                        <p class="mt-2 flex items-center text-sm text-gray-500">{{ $report->name }}</p>
                                        <p title="Questionnaire" class="mt-2 flex items-center italic text-sm text-gray-500">{{ $report->questionnaire }} questionnaire</p>
                                    </div>
                                    <div class="hidden md:block">
                                        <div>
                                            <div class="flex items-center">
                                                {{--<x-yes-no :value="$report->enabled" true-label="Enabled" false-label="Not enabled" />--}}
                                                <p class="ml-2 text-sm text-gray-500">
                                                    {{ $report->schedule ? "Generates daily at {$report->schedule}" : 'Not scheduled' }}
                                                </p>
                                            </div>
                                            <p class="mt-2 ml-2 text-sm text-gray-600">
                                                Last generated at: <span class="font-medium">{{ $report->last_generated_at?->toDayDateTimeString() ?? ' NA ' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('report.generate', $report) }}" title="Generate now" type="button" class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </a>
                                @if(Storage::disk('reports')->exists($report->blueprintInstance->file))
                                <a href="{{ route('report.download', $report) }}" title="Download" type="button" class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                                @else
                                <button disabled title="Download" type="button" class="disabled:opacity-25 inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </button>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endcan
                @empty
                    <div class="p-6">
                        There are no records to display
                    </div>
                @endforelse
            </ul>
        </div>
        @if ($records->hasPages())
        <div>
            <div class="px-6 text-left text-xs text-gray-500 tracking-wider">{{ $records->appends(request()->all())->links() }}</div>
        </div>
        @endif
    </div>

</x-app-layout>
