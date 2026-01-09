<x-app-layout>
    <livewire:area-insights-filter />

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100">

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="flex justify-between px-4 py-5 sm:px-6">
                <div class="overflow-hidden">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Erontalga District
                    </h3>
                    <p class="mt-1 max-w-2xl truncate text-sm text-gray-500">
                        Sub-title... not sure what goes here.
                    </p>
                </div>
                <div class="flex items-center text-nowrap text-2xl text-zinc-500 font-bold">
                    Area Insights
                </div>
            </div>
            <div>
                {{-- Case stats and score gauges --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 px-6 py-4 border-t border-gray-200">
                    <div>
                        @foreach($dataSources as $dataSource)
                            @livewire($dataSource->case_stats_component, ['dataSource' => $dataSource])
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-6">
                        @foreach($gauges ?? [] as $gauge)
                            @livewire('gauge.' . $gauge->slug, ['gauge' => $gauge, 'index' => $loop->index, 'lazy' => true])
                        @endforeach
                    </div>
                </div>
                <!-- Scorecards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-6 py-4">
                    @foreach($scorecards ?? [] as $scorecard)
                        @livewire('scorecard.' . $scorecard->slug, ['scorecard' => $scorecard, 'index' => $loop->index, 'lazy' => true])
                    @endforeach
                </div>
                <!-- Indicators -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 px-6 py-4">
                    @foreach($indicators as $indicator)
                        <x-chimera::featured-chart-card :indicator="$indicator">
                            @livewire($indicator->component, ['indicator' => $indicator, 'isBeingFeatured' => true, 'lazy' => true])
                        </x-chimera::featured-chart-card>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
