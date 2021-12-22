<x-app-layout>

    @connectible($connection)
        <livewire:namibia-area-filter :connection="$connection" />
    @else
        <x-simple-card class="p-6">
            The area filter component is not available because the database connection called <b>{{$connection}}</b> is not available.
        </x-simple-card>
    @endconnectible

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 sm:p-6 sm:pt-0 pb-6 bg-gray-100 grid-flow-row">

        @forelse($indicators as $chart => $metadata)
            @connectible($metadata['connection'])
                @can($metadata['permission_name'])
                    <x-chart-card
                            page="{{$page}}"
                            chart="{{$chart}}"
                            title="{{$metadata['title']}}"
                            description="{{$metadata['description']}}"
                    >
                        @livewire($chart, ['connection' => $metadata['connection'], 'graphDiv' => $chart])
                    </x-chart-card>
                @endcan
            @else
                <x-simple-card>
                    This indicator is not available because the database connection called <b>{{$connection}}</b> is not functioning.
                </x-simple-card>
            @endconnectible
        @empty
            <x-simple-card class="col-span-3">
                There are no indicators to display
            </x-simple-card>
        @endforelse

    </div>

    <div class="pt-0">
        {{$indicators->links('chart_pagination.links', ['preview' => $preview])}}
    </div>

</x-app-layout>
