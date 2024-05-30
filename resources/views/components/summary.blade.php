<div class="rounded-md bg-white shadow">
    <div class="flex justify-between p-4 border-b border-gray-200 sm:px-6">
        <div>
            <dt>
                <p class="text-2xl font-semibold text-gray-700">{{ $title }} @if (! is_null($area)) ({{ $area->name }}) @endif</p>
            </dt>
            <dd class="flex items-baseline">
                <p class="flex items-baseline text-sm font-semibold">
                    {{$dates['start']->locale(app()->getLocale())->isoFormat('ll')}} - {{$dates['end']->locale(app()->getLocale())->isoFormat('ll')}}
                </p>
            </dd>
        </div>
        <div class="flex flex-col">
            <p class="text-2xl font-semibold text-gray-700 text-right">
                {{ $dates['progress'] }}
            </p>
            <p class="flex items-baseline text-sm font-semibold text-gray-400">
                {{ __('Updated:') }} {{ $lastUpdated }}
            </p>
        </div>
    </div>

    <div class="px-4 sm:px-6 py-4">

        @livewire($dataSource->case_stats_component, ['dataSource' => $dataSource])

        <div>
            <div class="bg-white">
                @if ($dataSource->scorecards->isNotEmpty())
                    <div class="max-w-7xl mx-auto py-4">
                        <p class="text-center text-sm font-semibold uppercase text-gray-600 tracking-wider pb-2">
                            {{ __('A few selected scorecards') }}
                        </p>
                        <div class="rounded-lg bg-white grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($dataSource->scorecards as $scorecard)
                                @livewire('scorecard.' . $scorecard->slug, ['scorecard' => $scorecard, 'index' => $loop->index])
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($dataSource->featured_indicators->isNotEmpty())
                    <div class="max-w-7xl mx-auto py-4">
                        <p class="text-center text-sm font-semibold uppercase text-gray-600 tracking-wider pb-2">
                            {{ __('Featured indicators') }}
                        </p>
                        <div class="flex flex-col gap-y-4">
                            @connectible($dataSource->name)
                                @foreach($dataSource->featured_indicators as $indicator)
                                    @can($indicator->permission_name)
                                        @if (($loop->iteration % 2) === 1)
                                            <div class="flex gap-x-6">
                                        @endif

                                        <x-chimera::featured-chart-card :indicator="$indicator">
                                            @livewire($indicator->component, ['indicator' => $indicator, 'isBeingFeatured' => true, 'lazy' => true])
                                        </x-chimera::featured-chart-card>

                                        @if ((($loop->iteration % 2) === 0) || $loop->last)
                                            </div>
                                        @endif
                                    @endcan
                                @endforeach
                            @else
                                <p>Featured indicators can not be displayed because the data source named {{ $dataSource->name }} is not connectible.</p>
                            @endconnectible
                        </div>
                    </div>

                @endif
            </div>
        </div>
    </div>
</div>

