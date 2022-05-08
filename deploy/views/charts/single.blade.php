<x-app-layout>

    <livewire:area-filter />

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100">
        @connectible($indicator->questionnaire)
            {{--<x-chart-card :indicator="$indicator">
                @livewire($chart, ['connection' => $metadata['connection'], 'graphDiv' => $chart, 'mode' => 'Full Page'])
            </x-chart-card>--}}

            @can($indicator->permission_name)
                <x-chart-card :indicator="$indicator" mode="Full Page">
                    @livewire($indicator->component, ['indicator' => $indicator])
                </x-chart-card>
            @endcan
        @else
            <x-simple-card>
                This indicator is not available because the database connection of the questionnaire
                called <b>{{$questionnaire}}</b> is not functioning properly.
            </x-simple-card>
        @endconnectible
    </div>

</x-app-layout>
