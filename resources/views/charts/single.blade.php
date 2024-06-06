<x-app-layout>
    <livewire:area-filter />

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100">
        @connectible($indicator->data_source)
            <x-chimera-chart-card :indicator="$indicator" mode="Full Page">
                @livewire($indicator->component, ['indicator' => $indicator, 'lazy' => true, 'linkedFromScorecard' => request()->has('linked_from_scorecard')])
            </x-chimera-chart-card>
        @else
            <x-chimera::simple-card>
                This indicator is not available because the database connection of the data source
                called <b>{{ $indicator->data_source }}</b> is not available.
            </x-chimera::simple-card>
        @endconnectible
    </div>

</x-app-layout>
