<x-app-layout>
    <div class="no-print">
        <livewire:area-insights-filter />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100 mb-6">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg print:shadow-none print:rounded-none print:border">
            <div class="flex justify-between px-4 py-5 sm:px-6">
                <div class="overflow-hidden">
                    <h3 class="text-lg leading-6 font-base text-gray-700">
                        {{ __('Area Insights') }}: <livewire:level-area-name-display />
                    </h3>
                    <p class="mt-1 max-w-2xl truncate text-sm text-gray-500">
                        {{ $dataSource->title }} | {{ $dataSource->start_date->toFormattedDateString() }} - {{ $dataSource->end_date->toFormattedDateString() }}
                    </p>
                </div>
                <div class="flex items-center gap-4 text-nowrap text-xl text-zinc-500 font-bold">
                    <div onclick="window.print()" class="no-print border rounded-lg p-1 px-2 bg-gray-50 cursor-pointer hover:bg-gray-100" title="Print">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 px-6 py-4 border-t border-gray-200">
                    {{-- Case stats --}}
                    <div>
                        @livewire($dataSource->case_stats_component, ['dataSource' => $dataSource, 'placement' => 'area-insights'])
                    </div>

                    {{-- Gauges --}}
                    <div class="flex flex-wrap justify-end gap-6">
                        @foreach($dataSource->gauges ?? [] as $gauge)
                            @livewire('gauge.' . $gauge->slug, ['gauge' => $gauge, 'index' => $loop->index, 'lazy' => true])
                        @endforeach
                    </div>
                </div>

                <!-- Scorecards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-6 py-4">
                    @foreach($dataSource->area_insights_scorecards ?? [] as $scorecard)
                        @livewire('scorecard.' . $scorecard->slug, ['scorecard' => $scorecard, 'index' => $loop->index, 'placement' => 'area-insights', 'lazy' => true])
                    @endforeach
                </div>

                <!-- Indicators -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 px-6 py-4">
                    @foreach($dataSource->area_insights_indicators as $indicator)
                        <x-chimera::featured-chart-card :indicator="$indicator">
                            @livewire($indicator->component, ['indicator' => $indicator, 'placement' => 'area-insights', 'lazy' => true])
                        </x-chimera::featured-chart-card>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
