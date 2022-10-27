<x-app-layout>

    <livewire:area-filter />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 sm:p-6 sm:pt-0 pb-6 bg-gray-100 grid-flow-row">
        @forelse($indicators as $indicator)
            @connectible($indicator->questionnaire)

                    <x-chart-card :indicator="$indicator">
                        @livewire($indicator->component, ['indicator' => $indicator])
                    </x-chart-card>

            @else
                <x-simple-card>
                    This indicator is not available because the database connection of the questionnaire
                    called <b>{{$indicator->questionnaire}}</b> is not available.
                </x-simple-card>
            @endconnectible
        @empty
            <x-simple-card class="col-span-3">
                {{ __('There are no indicators to display') }}
            </x-simple-card>
        @endforelse

    </div>

    <div class="pt-0">
        {{$indicators->links('chart_pagination.links', ['preview' => $preview])}}
    </div>

</x-app-layout>
